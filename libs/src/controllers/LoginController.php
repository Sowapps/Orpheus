<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Form\FormToken;

class LoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		/* @var User $user */
		$FORM_TOKEN	= new FormToken();
// 		debug('Request data', $request->getAllData());
		try {
			$request->hasData() && $FORM_TOKEN->validateForm();
// 			debug('POST', $request->getAllData());
// 			die();
// 			$FORM_TOKEN->validateForm();
			if( $request->hasParameter('ac') && is_id($userID=$request->getParameter('u')) ) {
				$user	= User::load($userID);
				if( !$user || $user->activation_code != $request->getParameter('ac') ) {
					User::throwException('invalidActivationCode');
				}
				$user->activate();
				$user->login();
				redirectTo(u(DEFAULTMEMBERROUTE));
			} else
			if( $request->hasData('submitLogin') && $data = $request->getData('login') ) {
				User::userLogin($data, 'email');
				redirectTo(u(DEFAULTMEMBERROUTE));
			} else
			if( $request->hasData('submitRegister') && ($data = $request->getData('user')) ) {
				startReportStream('register');
// 			if( $data = $request->getData('user') ) {
				$data['published']			= 0;
				$data['activation_code']	= generatePassword(30);
				$user	= User::createAndGet($data, array('fullname', 'email', 'password', 'published', 'activation_code'));
// 				debug('Send email => '.b(sendUserRegistrationEmail($user)));
				sendUserRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
		
			}
		} catch (UserException $e) {
			reportError($e);
			endReportStream();
		}
		
		return $this->renderHTML('app/user_login', array(
			'FORM_TOKEN'	=> $FORM_TOKEN
		));
	}

	
}
