<?php

class AdminDemoController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		HTMLRendering::setDefaultTheme('admin');
		
		return HTMLHTTPResponse::render('app/admin_demo');
	}

}
