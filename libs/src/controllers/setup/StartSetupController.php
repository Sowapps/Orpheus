<?php

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class StartSetupController extends SetupController {
	
	protected static $route = 'setup_start';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		try {
			if( static::isThisStepValidated('EndSetupController') ) {
				throw new UserException('alreadyInstalled');
			} else {
				$this->validateStep();
			}
		} catch( UserException $e ) {
			reportError($e, DOMAIN_SETUP);
		}
	
		return $this->renderHTML('setup/setup_start', array(
		));
	}

}
