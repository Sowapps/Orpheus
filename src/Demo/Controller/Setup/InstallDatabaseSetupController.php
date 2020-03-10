<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Orpheus\EntityDescriptor\SQLGenerator\SQLGeneratorMySQL;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class InstallDatabaseSetupController extends SetupController {
	
	protected static $routeName = 'setup_installdb';
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		$FORM_TOKEN = new FormToken();
		$env = [
			'FORM_TOKEN'    => $FORM_TOKEN,
			'allowContinue' => false,
		];
		// TODO: Check and suggest to delete unknown tables in DB
		try {
			if( is_array($request->getData('entities')) ) {
				if( $request->hasDataKey('submitGenerateSQL', $output) ) {
					$output		= $output==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
					if( $output == OUTPUT_APPLY ) {
						$FORM_TOKEN->validateForm($request);
					}
					$generator	= new SQLGeneratorMySQL();
					$result		= '';
					foreach( $request->getArrayData('entities') as $entityClass => $on ) {
						$query	= $generator->matchEntity($entityClass::getValidator());
						if( $query ) {
							$result[$entityClass]	= $query;
						}
					}
					
					if( empty($result) ) {
						$env['allowContinue'] = true;
						throw new UserException('errorNoChanges', DOMAIN_SETUP);
					}
					$env['resultingSQL'] = implode('', $result);
					if( $output == OUTPUT_DISPLAY ) {
						$env['requireEntityValidation'] = 1;
					} elseif( $output == OUTPUT_APPLY ) {
						foreach( $result as $query ) {
							pdo_query(strip_tags($query), PDOEXEC);
						}
						$env['allowContinue'] = true;
						reportSuccess('successSQLApply', DOMAIN_SETUP);
					}
				}
			}
		
		} catch( UserException $e ) {
			if( $e->getMessage() === 'errorNoChanges' ) {
				reportSuccess($e, $e->getDomain());
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
