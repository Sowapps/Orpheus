<?php


use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\RedirectHTTPResponse;

class RedirectController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		return new RedirectHTTPResponse(u(User::isLogged() ? DEFAULTMEMBERROUTE : DEFAULTROUTE));
	}

	
}
