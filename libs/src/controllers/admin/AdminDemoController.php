<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

class AdminDemoController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
// 		HTMLRendering::setDefaultTheme('admin');
		
		return $this->renderHTML('app/admin_demo');
	}

}
