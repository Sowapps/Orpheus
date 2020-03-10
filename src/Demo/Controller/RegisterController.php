<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use User;

class RegisterController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		/* @var $user User */
		try {
			if( $request->hasParameter('ac') && is_id($userID = $request->getParameter('u')) ) {
				$user = User::load($userID);
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
			}
			
		} catch (UserException $e) {
			reportError($e);
		}
		
		return $this->renderHTML('app/user_login');
	}
	
	
}
