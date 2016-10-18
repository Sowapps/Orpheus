<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class CheckDatabaseSetupController extends SetupController {
	
	protected static $routeName = 'setup_checkdb';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		$env = array(
			'folders'	=> array(),
			'allowContinue'	=> false
		);
		
		pdo_loadConfig();
		$DB_SETTINGS = (object) pdo_getSettings(pdo_getDefaultInstance());
		$env['DB_SETTINGS'] = &$DB_SETTINGS;
		
// 		startReportStream('checkdb');
// 		$allowContinue = false;
		try {
			ensure_pdoinstance();
			$env['allowContinue'] = true;
			reportSuccess('successDBAccess', DOMAIN_SETUP);
		} catch( SQLException $e ) {
			reportError($e->getMessage(), DOMAIN_SETUP);
		}
// 		endReportStream();

		if( $env['allowContinue'] ) {
			$this->validateStep();
		}
	
		return $this->renderHTML('setup/setup_checkdb', $env);
	}

}
