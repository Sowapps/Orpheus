<?php

namespace Demo\Controller\Developer;

use Orpheus\Config\AppConfig;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use User;

class DevConfigController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		/* @var User $USER */
		
		$this->addThisToBreadcrumb();
		
		if( $data = $request->getArrayData('row') ) {
			
			try {
				$AppConfig = AppConfig::instance();
				$AppConfig->set($data['key'], $data['value']);
				$AppConfig->save();
				
			} catch( UserException $e ) {
				reportError($e);
			}
		}
		
		return $this->renderHTML('developer/dev_config', [
		]);
	}
	
}
