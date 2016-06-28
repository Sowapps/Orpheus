<?php

use Orpheus\Publisher\Form\FormToken;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\EntityDescriptor\SQLGenerator\SQLGeneratorMySQL;
use Orpheus\Exception\UserException;

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class InstallDatabaseSetupController extends SetupController {
	
	protected static $route = 'setup_installdb';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		using('entitydescriptor.EntityDescriptor');
		using('entitydescriptor.SQLGenerator_MySQL');
		using('entitydescriptor.LangGenerator');
		
		$FORM_TOKEN	= new FormToken();
		$env		= array(
			'FORM_TOKEN'	=> $FORM_TOKEN,
			'allowContinue'	=> false,
		);
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$output		= $output==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$FORM_TOKEN->validateForm();
					}
					$generator	= new SQLGeneratorMySQL();
					$result		= '';
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
// 						$query	= $generator->matchEntity(EntityDescriptor::load($entityName));
						$query	= $generator->matchEntity($entityClass::getValidator());
						if( $query ) {
							$result[$entityClass]	= $query;
						}
					}
					
					if( empty($result) ) {
						$env['allowContinue']	= true;
						throw new UserException('errorNoChanges', DOMAIN_SETUP);
					}
					$env['resultingSQL']	= implode('', $result);
					if( $output==OUTPUT_DISPLAY ) {
						$env['requireEntityValidation']	= 1;
// 						$env['resultingSQL']	= str_replace("\t", "<div class=\"tabulation\">\t</div>", $env['resultingSQL']);
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
						$env['allowContinue']	= true;
						reportSuccess('successSQLApply', DOMAIN_SETUP);
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
// 				} else
// 				if( $request->hasData('submitGenerateVE') ) {
// 					$output		= $request->getData('ve_output')==OUTPUT_DLRAW ? OUTPUT_DLRAW : OUTPUT_DISPLAY;
// 					$generator	= new LangGenerator();
// 					$result		= '';
// 					foreach( POST('entities') as $entityName => $on ) {
// 						$result	.= "\n\n\t$entityName.ini\n";
// 						foreach( $generator->getRows(EntityDescriptor::load($entityName)) as $k => $exc ) {
// 							/* @var $exc InvalidFieldException */
// 							$exc->setDomain('entity_model');
// 							$exc->removeArgs();//Does not replace arguments
// 							// Tab size is 4 (as my editor's config)
// 							$result .= $k.str_repeat("\t", 11-floor(strlen($k)/4)).'= "'.$exc->getText()."\"\n";
// 						}
// 						//paymentsbyexchangeaccepted_aboveMaxValue\t\t\t
// 					}
// 					if( $output==OUTPUT_APPLY ) {
// 			// 			reportSuccess('Output not implemented !');
// 						reportError('Output not implemented !');
						
// 					} else {
// 						echo '<pre style="tab-size: 4; -moz-tab-size: 4;">'.$result.'</pre>';
// 					}
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
		
		if( $env['allowContinue'] ) {
			$this->validateStep();
		}
	
		return $this->renderHTML('setup/setup_installdb', $env);
	}

}

define('OUTPUT_APPLY',		1);
define('OUTPUT_DISPLAY',	2);
define('OUTPUT_DLRAW',		3);
define('OUTPUT_DLZIP',		4);
