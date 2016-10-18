<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

class DevSystemController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		$this->addThisToBreadcrumb();
		return $this->renderHTML('devtools/dev_system');
	}

}
