<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Developer;

use Orpheus\EntityDescriptor\EntityDescriptor;
use Orpheus\EntityDescriptor\LangGenerator;
use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\EntityDescriptor\SqlGenerator\SqlGeneratorMySql;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Publisher\Exception\InvalidFieldException;
use Orpheus\SqlAdapter\Exception\SqlException;
use Orpheus\SqlAdapter\SqlAdapter;
use PDO;
use PDOStatement;

class DevEntitiesController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$this->addThisToBreadcrumb();
		
		$formToken = new FormToken();
		$env = [
			'formToken' => $formToken,
		];
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$defaultAdapter = SqlAdapter::getInstance();
					$output = $output == OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$formToken->validateForm($request);
					}
					$generator = new SqlGeneratorMySql();
					$result = [];
					/** @var PermanentEntity $entityClass */
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$query = $generator->matchEntity($entityClass::getValidator(), $entityClass::getSqlAdapter());
						if( $query ) {
							$result[$entityClass] = $query;
						}
					}
					// List all unknown tables
					$env['unknownTables'] = [];
					/* @var PDOStatement $statement */
					$statement = $defaultAdapter->query('SHOW TABLES', PDOSTMT);
					$knownTables = [];
					foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
						$knownTables[$entityClass::getTable()] = 1;
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
						// Apply for selected entities
						foreach( $result as $query ) {
							$defaultAdapter->query(strip_tags($query), PDOEXEC);
						}
						// Remove non-managed tables
						$tablesToRemove = $request->getData('removeTable');
						foreach( $env['unknownTables'] as $table => $on ) {
							if( empty($tablesToRemove[$table]) ) {
								// Not selected
								continue;
							}
							try {
								$defaultAdapter->query(sprintf('DROP TABLE `%s`', $defaultAdapter->escapeIdentifier($table)), PDOEXEC);
							} catch( SqlException $e ) {
								reportError(sprintf('Unable to drop table %s, cause: %s', $table, $e->getMessage()));
							}
						}
						reportSuccess('successSqlApply');
					}
				} elseif( $request->hasData('submitGenerateVE') ) {
					$output = $request->getData('ve_output') == OUTPUT_DLRAW ? OUTPUT_DLRAW : OUTPUT_DISPLAY;
					$generator = new LangGenerator();
					$result = '';
					/** @var PermanentEntity $entityClass */
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$entityName = $entityClass::getTable();
						$result .= "\n\n\t$entityName.ini\n";
						$entityDescriptor = EntityDescriptor::load($entityName, $entityClass);
						foreach( $generator->getRows($entityDescriptor) as $k => $exc ) {
							/* @var $exc InvalidFieldException */
							$exc->setDomain('entity_model');
							$exc->removeArgs();//Does not replace arguments
							// Tab size is 4 (as my editor's config)
							$result .= $k . str_repeat("\t", 11 - floor(strlen($k) / 4)) . '= "' . $exc->getText() . "\"\n";
						}
					}
					if( $output == OUTPUT_APPLY ) {
						reportError('Output not implemented !');
					} else {
						echo '<pre style="tab-size: 4; -moz-tab-size: 4;">' . $result . '</pre>';
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
		
		return $this->renderHtml('app/dev_entities', $env);
	}
	
}

define('OUTPUT_APPLY', 1);
define('OUTPUT_DISPLAY', 2);
define('OUTPUT_DLRAW', 3);
define('OUTPUT_DLZIP', 4);
