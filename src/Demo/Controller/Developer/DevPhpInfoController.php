<?php

namespace Demo\Controller\Developer;

use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class DevPhpInfoController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		phpinfo();
		die();
	}
	
}
