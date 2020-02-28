<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use Orpheus\InputController\HTTPController\RedirectHTTPResponse;

class LogoutController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		$user = User::getLoggedUser();
		if( isset($user) ) {
			$user->logout();
		}
		return new RedirectHTTPResponse(DEFAULT_ROUTE);
	}
	
	
}
