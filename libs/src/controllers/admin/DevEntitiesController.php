<?php

class AdminUserListController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		using('entitydescriptor.entitydescriptor');
		using('entitydescriptor.sqlgenerator_mysql');
		using('entitydescriptor.langgenerator');
		
		$FORM_TOKEN	= new FormToken();
		$Values		= array(
			'FORM_TOKEN'	=> $FORM_TOKEN
		);
		try {
			if( is_array($request->getData('entities')) ) {
// 			if( $request->getArrayData('entities')Data('entities') && is_array(POST('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$output		= $output==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$FORM_TOKEN->validateForm();
					}
					$generator	= new SQLGenerator_MySQL();
					$result		= '';
					foreach( $request->getArrayData('entities') as $entityName => $on ) {
						$result	.= $generator->matchEntity(EntityDescriptor::load($entityName));
					}
					if( empty($result) ) {
						throw new UserException('No changes');
					}
					$Values['resultingSQL']	= $result;
					if( $output==OUTPUT_DISPLAY ) {
						$Values['requireEntityValidation']	= 1;
// 						echo '
// 			<form method="POST">'.$FORM_TOKEN;
// 						foreach( POST('entities') as $entityName => $on ) {
// 							echo htmlHidden('entities/'.$entityName);
// 						}
// 						echo '
// 			<button type="submit" class="btn btn-primary" name="submitGenerateSQL['.OUTPUT_APPLY.']">Apply</button></form>';
					} else
					if( $output==OUTPUT_APPLY ) {
						pdo_query(strip_tags($result), PDOEXEC);
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
			reportError($e);
		}
		return HTMLHTTPResponse::render('app/dev_entities', $Values);
	}

}
		
define('OUTPUT_APPLY',		1);
define('OUTPUT_DISPLAY',	2);
define('OUTPUT_DLRAW',		3);
define('OUTPUT_DLZIP',		4);
//define('OUTPUT_SQLDOWNLOAD');
