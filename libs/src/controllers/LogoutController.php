<?php


class LogoutController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		global $USER;
		if( isset($USER) ) {
			$USER->logout();
		}
		
		return new RedirectHTTPResponse('home');
	}

	
}
