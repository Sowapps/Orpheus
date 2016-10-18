<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

class DevHomeController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		return $this->renderHTML('devtools/dev_home');
	}

}
