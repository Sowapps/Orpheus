<?php

namespace App\Controller\Developer;

use Orpheus\Config\AppConfig;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class DevConfigController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$this->addThisToBreadcrumb();
		$config = AppConfig::instance();
		
		if( $data = $request->getArrayData('row') ) {
			try {
				$config->set($data['key'], $data['value']);
				$config->save();
				$this->redirectToSelf();
				
			} catch( UserException $e ) {
				reportError($e);
			}
		}
		
		return $this->renderHtml('developer/dev_config', [
			'config' => $config,
		]);
	}
	
}
