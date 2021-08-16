<?php

namespace Demo\Controller\Developer;

use Orpheus\Config\AppConfig;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use User;

class DevConfigController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
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
		
		return $this->renderHtml('developer/dev_config', [
		]);
	}
	
}
