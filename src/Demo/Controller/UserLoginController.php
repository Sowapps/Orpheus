<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Demo\User;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class UserLoginController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		$formToken = new FormToken();
		
		try {
			$request->hasData() && $formToken->validateForm($request);
			
			if( $request->hasData('submitLogin') ) {
				User::userLogin($request->getData('login'));
				reportSuccess(User::text('successLogin'));
				
			} elseif( $request->hasData('submitRegister') ) {
				startReportStream('register');
				$user = User::createAndGet($request->getData('user'), ['name', 'fullname', 'email', 'email_public', 'password']);
				sendAdminRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
			}
			endReportStream();
		} catch( UserException $e ) {
			reportError($e);
			endReportStream();
		}
		
		return HTMLHTTPResponse::render('app/user_login', ['formToken' => $formToken]);
	}
	
}
