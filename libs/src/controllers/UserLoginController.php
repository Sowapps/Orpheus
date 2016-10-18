<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;

class UserLoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		$FORM_TOKEN	= new FormToken();
		
		try {
// 			if( $request->hasData() ) {
// 				debug('$FORM_TOKEN', $FORM_TOKEN);
// 				debug('$_SESSION', $_SESSION);
// 				die(__LINE__);
// 			}
			$request->hasData() && $FORM_TOKEN->validateForm($request);
// 			debug('Data', $request->getAllData());
// 			die();
			
			if( $request->hasData('submitLogin') ) {
// 				die('LINE : '.__LINE__);
// 				startReportStream('login');
				User::userLogin($request->getData('login'));
				reportSuccess(User::text('successLogin'));
				
			} else
			if( $request->hasData('submitRegister') ) {
				startReportStream('register');
				$user = User::createAndGet($request->getData('user'), array('name', 'fullname', 'email', 'email_public', 'password'));
				sendAdminRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
			}
			endReportStream();
		} catch( UserException $e ) {
// 			debug('UserException', $e);
// 			die('Exception');
			reportError($e);
// 		} catch(Exception $e) {
// 			debug('Exception', $e);
// 			die('Exception');
		}
		return HTMLHTTPResponse::render('app/user_login', array('FORM_TOKEN'=>$FORM_TOKEN));
	}

}
