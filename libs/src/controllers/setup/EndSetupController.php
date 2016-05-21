<?php

/*
 * Check writing on FS
 * Check DB
 * Install db
 * Install user
 * 
 */

class EndSetupController extends SetupController {
	
	protected static $route = 'setup_end';

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
// 		if( $data = $request->getArrayData('row') ) {
	
// 			try {
// 				$GlobalConfig	= GlobalConfig::instance();
// 				$GlobalConfig->set($data['key'], $data['value']);
// 				$GlobalConfig->save();
	
// 			} catch(UserException $e) {
// 				reportError($e);
// 			}
// 		}

		$this->validateStep();
	
		return $this->renderHTML('setup/setup_end', array());
	}

}
