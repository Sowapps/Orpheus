<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Admin;

use Orpheus\EntityDescriptor\EntityDescriptor;
use Orpheus\EntityDescriptor\LangGenerator;
use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\EntityDescriptor\SQLGenerator\SQLGeneratorMySQL;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use Orpheus\Publisher\Exception\InvalidFieldException;
use Orpheus\SQLAdapter\Exception\SQLException;
use Orpheus\SQLAdapter\SQLAdapter;
use PDO;
use PDOStatement;

class DevEntitiesController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		$this->addThisToBreadcrumb();
		
		$formToken = new FormToken();
		$env = [
			'formToken' => $formToken,
		];
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$output = $output == OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$formToken->validateForm($request);
					}
					$generator	= new SQLGeneratorMySQL();
					$result		= '';
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$query	= $generator->matchEntity($entityClass::getValidator());
						if( $query ) {
							$result[$entityClass] = $query;
						}
					}
					
					$env['unknownTables'] = array();
					/* @var PDOStatement $statement */
					$statement = pdo_query('SHOW TABLES', PDOSTMT);
					$knownTables = array();
					foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
						$knownTables[$entityClass::getTable()]	= 1;
					}
					while( $tableFetch = $statement->fetch(PDO::FETCH_NUM) ) {
						$table = $tableFetch[0];
						if( isset($knownTables[$table]) ) {
							continue;
						}
						$env['unknownTables'][$table] = 1;
					}
					
					if( empty($result) ) {
						throw new UserException('No changes');
					}
					$env['resultingSQL'] = implode('', $result);
					if( $output == OUTPUT_DISPLAY ) {
						$env['requireEntityValidation'] = 1;
					} elseif( $output == OUTPUT_APPLY ) {
						foreach( $result as $query ) {
							pdo_query(strip_tags($query), PDOEXEC);
						}
						$tablesToRemove = $request->getData('removeTable');
						foreach( $env['unknownTables'] as $table => $on ) {
							if( empty($tablesToRemove[$table]) ) {
								// Not selected
								continue;
							}
							try {
								pdo_query('DROP TABLE ' . SQLAdapter::doEscapeIdentifier($table), PDOEXEC);
							} catch( SQLException $e ) {
								reportError('Unable to drop table ' . $table . ', cause: ' . $e->getMessage());
							}
						}
						reportSuccess('successSQLApply');
					}
				} elseif( $request->hasData('submitGenerateVE') ) {
					$output = $request->getData('ve_output') == OUTPUT_DLRAW ? OUTPUT_DLRAW : OUTPUT_DISPLAY;
					$generator = new LangGenerator();
					$result = '';
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$entityName = $entityClass::getTable();
						$result .= "\n\n\t$entityName.ini\n";
						foreach( $generator->getRows(EntityDescriptor::load($entityName)) as $k => $exc ) {
							/* @var $exc InvalidFieldException */
							$exc->setDomain('entity_model');
							$exc->removeArgs();//Does not replace arguments
							// Tab size is 4 (as my editor's config)
							$result .= $k.str_repeat("\t", 11-floor(strlen($k)/4)).'= "'.$exc->getText()."\"\n";
						}
					}
					if( $output==OUTPUT_APPLY ) {
						reportError('Output not implemented !');
						
					} else {
						echo '<pre style="tab-size: 4; -moz-tab-size: 4;">'.$result.'</pre>';
					}
				}
			}
		
		} catch( UserException $e ) {
			if( $e->getMessage() === 'errorNoChanges' ) {
				reportWarning($e);
			} else {
				reportError($e);
			}
		}
		return $this->renderHTML('app/dev_entities', $env);
	}

}

define('OUTPUT_APPLY',		1);
define('OUTPUT_DISPLAY',	2);
define('OUTPUT_DLRAW',		3);
define('OUTPUT_DLZIP',		4);
