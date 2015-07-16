<?php

class AdminDemoController extends AdminHTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		return HTMLHTTPResponse::render('app/admin_demo');
	}

}
