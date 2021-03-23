<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Demo\User;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class LoginController extends HTTPController {
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		/* @var User $user */
		$formToken = new FormToken();
		try {
			$request->hasData() && $formToken->validateForm($request);
			if( $request->hasParameter('ac') && is_id($userID = $request->getParameter('u')) ) {
				$user = User::load($userID);
				if( !$user || $user->activation_code != $request->getParameter('ac') ) {
					User::throwException('invalidActivationCode');
				}
				$user->activate();
				$user->login();
				redirectTo(u(DEFAULTMEMBERROUTE));
				
			} elseif( $request->hasData('submitLogin') && $data = $request->getData('login') ) {
				User::userLogin($data, 'email');
				redirectTo(u(DEFAULTMEMBERROUTE));
				
			} elseif( $request->hasData('submitRegister') && ($data = $request->getData('user')) ) {
				startReportStream('register');
				$data['published'] = 0;
				$data['activation_code'] = generatePassword(30);
				$user = User::createAndGet($data, ['fullname', 'email', 'password', 'published', 'activation_code']);
				sendUserRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
				
			}
		} catch (UserException $e) {
			reportError($e);
			endReportStream();
		}
		
		return $this->renderHTML('app/user_login', [
			'formToken' => $formToken
		]);
	}
	
	
}
