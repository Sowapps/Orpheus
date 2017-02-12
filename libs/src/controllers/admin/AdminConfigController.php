<?php

use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Config\AppConfig;

class AdminConfigController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		
		$this->addThisToBreadcrumb();
		
		if( $data = $request->getArrayData('row') ) {
		
			try {
				$AppConfig = AppConfig::instance();
				$AppConfig->set($data['key'], $data['value']);
				$AppConfig->save();
				
			} catch(UserException $e) {
				reportError($e);
			}
		}
		
		return $this->renderHTML('app/admin_config', array(
		));
	}

}
