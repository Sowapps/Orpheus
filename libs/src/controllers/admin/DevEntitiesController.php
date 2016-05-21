<?php

class DevEntitiesController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		using('entitydescriptor.EntityDescriptor');
		using('entitydescriptor.SQLGenerator_MySQL');
		using('entitydescriptor.LangGenerator');
		
		$this->addThisToBreadcrumb();
		
		$FORM_TOKEN	= new FormToken();
		$env		= array(
			'FORM_TOKEN'	=> $FORM_TOKEN
		);
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$output		= $output==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$FORM_TOKEN->validateForm();
					}
					$generator	= new SQLGenerator_MySQL();
					$result		= '';
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
// 						$query	= $generator->matchEntity(EntityDescriptor::load($entityName));
						$query	= $generator->matchEntity($entityClass::getValidator());
						if( $query ) {
							$result[$entityClass]	= $query;
						}
					}
					
					$env['unknownTables'] = array();
					/* @var PDOStatement $statement */
					$statement	= pdo_query('SHOW TABLES', PDOSTMT);
					$knownTables	= array();
					foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
						$knownTables[$entityClass::getTable()]	= 1;
					}
					while( $tableFetch = $statement->fetch(PDO::FETCH_NUM) ) {
						$table	= $tableFetch[0];
						if( isset($knownTables[$table]) ) {
							continue;
						}
						$env['unknownTables'][$table] = 1;
					}
					
					if( empty($result) ) {
						throw new UserException('No changes');
					}
					$env['resultingSQL']	= implode('', $result);
					if( $output==OUTPUT_DISPLAY ) {
						$env['requireEntityValidation']	= 1;
// 						echo '
// 			<form method="POST">'.$FORM_TOKEN;
// 						foreach( POST('entities') as $entityName => $on ) {
// 							echo htmlHidden('entities/'.$entityName);
// 						}
// 						echo '
// 			<button type="submit" class="btn btn-primary" name="submitGenerateSQL['.OUTPUT_APPLY.']">Apply</button></form>';
					} else
					if( $output==OUTPUT_APPLY ) {
						foreach( $result as $entity => $query ) {
							pdo_query(strip_tags($query), PDOEXEC);
						}
						$tablesToRemove = $request->getData('removeTable');
						foreach( $env['unknownTables'] as $table => $on ) {
							if( empty($tablesToRemove[$table]) ) {
								// Not selected
								continue;
							}
							try {
// 								debug('DROP TABLE '.SQLAdapter::doEscapeIdentifier($table));
								pdo_query('DROP TABLE '.SQLAdapter::doEscapeIdentifier($table), PDOEXEC);
							} catch( SQLException $e ) {
								reportError('Unable to drop table '.$table.', cause: '.$e->getMessage());
							}
						}
						reportSuccess('successSQLApply');
					}
// 					echo '<div>'.$result.'</div>';
// 					if( $output==OUTPUT_DISPLAY ) {
// 						echo '
// 			<form method="POST">'.$FORM_TOKEN;
// 						foreach( POST('entities') as $entityName => $on ) {
// 							echo htmlHidden('entities/'.$entityName);
// 						}
// 						echo '
// 			<button type="submit" class="btn btn-primary" name="submitGenerateSQL['.OUTPUT_APPLY.']">Apply</button></form>';
// 					} else
// 					if( $output==OUTPUT_APPLY ) {
// 						pdo_query(strip_tags($result), PDOEXEC);
// 						reportSuccess('successSQLApply');
// 					}
				} else
				if( $request->hasData('submitGenerateVE') ) {
					$output		= $request->getData('ve_output')==OUTPUT_DLRAW ? OUTPUT_DLRAW : OUTPUT_DISPLAY;
					$generator	= new LangGenerator();
					$result		= '';
					foreach( POST('entities') as $entityName => $on ) {
						$result	.= "\n\n\t$entityName.ini\n";
						foreach( $generator->getRows(EntityDescriptor::load($entityName)) as $k => $exc ) {
							/* @var $exc InvalidFieldException */
							$exc->setDomain('entity_model');
							$exc->removeArgs();//Does not replace arguments
							// Tab size is 4 (as my editor's config)
							$result .= $k.str_repeat("\t", 11-floor(strlen($k)/4)).'= "'.$exc->getText()."\"\n";
						}
						//paymentsbyexchangeaccepted_aboveMaxValue\t\t\t
					}
					if( $output==OUTPUT_APPLY ) {
			// 			reportSuccess('Output not implemented !');
						reportError('Output not implemented !');
						
					} else {
						echo '<pre style="tab-size: 4; -moz-tab-size: 4;">'.$result.'</pre>';
					}
				}
			}
// 		} catch( SQLException $e ) {
// 			HTMLRendering::doDisplay('error', array('date' => date('c'), 'report' => $e->getMessage(), 'action' => $e->getAction()));
		
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
//define('OUTPUT_SQLDOWNLOAD');
