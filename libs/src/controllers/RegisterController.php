<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;

class LoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		/* @var $user User */
		try {
			if( $request->hasParameter('ac') && is_id($userID=$request->getParameter('u')) ) {
				$user	= User::load($userID);
				if( !$user || $user->activation_code != $request->getParameter('ac') ) {
					User::throwException('invalidActivationCode');
				}
				$user->login();
				redirectTo(u(ROUTE_USERPROJECTS));
			}
			if( $data = $request->getData('user') ) {
				$data['published']			= 0;
				$data['activation_code']	= generatePassword(30);
				$user	= User::createAndGet($data, array('fullname', 'email', 'password', 'published', 'activation_code'));
				sendUserRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
				
// 				$data	= array_filterbykeys($data, array('email', 'password', 'fullname'));
// 				$data	= User::checkUserInput($data, array('email', 'password', 'fullname'), null, $errors);
// 				if( !$errors ) {
// 					$data['create_date']	= sqlDatetime();
// 					sendUserRegistrationEmail(json_encode($data));
// 				}
		
			}
				
		} catch (UserException $e) {
			reportError($e);
		}
		
		return $this->renderHTML('app/user_login');
	}

	
}
