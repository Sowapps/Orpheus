<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Demo\User;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class UserLoginController extends HttpController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
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
		
		return HtmlHttpResponse::render('app/user_login', ['formToken' => $formToken]);
	}
	
}
