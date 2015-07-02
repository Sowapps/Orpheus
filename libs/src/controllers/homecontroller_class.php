<?php


class HomeController extends HTTPController {
	
	/**
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		return new HTMLHTTPResponse('Everything is ok.');
	}

	
}
