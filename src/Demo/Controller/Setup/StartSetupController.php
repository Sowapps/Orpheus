<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Setup;

use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class StartSetupController extends SetupController {
	
	protected static $routeName = 'setup_start';
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		try {
			if( static::isThisStepValidated('EndSetupController') ) {
				throw new UserException('alreadyInstalled');
			} else {
				$this->validateStep();
			}
		} catch( UserException $e ) {
			reportError($e, DOMAIN_SETUP);
		}
		
		return $this->renderHTML('setup/setup_start');
	}
	
}
