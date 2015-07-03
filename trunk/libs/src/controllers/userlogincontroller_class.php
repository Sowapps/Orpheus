<?php

class UserLoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		$FORM_TOKEN	= new FormToken();
		
		try {
			isPOST() && $FORM_TOKEN->validateForm();
			if( $request->hasData('submitLogin') ) {
				SiteUser::userLogin($request->getData('login'));
				reportSuccess('You\'re successfully logged in.');
			} else if( $request->hasData('submitRegister') ) {
		// 		$formregister = POST('register');
				$user	= SiteUser::createAndGet($request->getData('register'), array('name', 'fullname', 'email', 'email_public', 'password'));
				sendAdminRegistrationEmail($user);
				unset($user);
				reportSuccess('You\'re successfully registered.');
			}
		} catch(UserException $e) {
			reportError($e);
		}
		return HTMLHTTPResponse::renderWithPHP('app/user_login', array('FORM_TOKEN'=>$FORM_TOKEN));
	}

}
