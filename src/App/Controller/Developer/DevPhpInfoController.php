<?php

namespace App\Controller\Developer;

use Orpheus\InputController\HttpController\HttpRequest;

class DevPhpInfoController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return never The output HTTP response
	 */
	public function run($request): never {
		phpinfo();
		die();
	}
	
}
