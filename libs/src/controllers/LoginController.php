<?php


class LoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		if( $data = $request->getData('login') ) {
			try {
				User::userLogin($data);
				redirectTo(u('adm_items'));
				
			} catch (UserException $e) {
				reportError($e);
			}
		}
		
		return $this->renderHTML('app/login');
	}

	
}
