<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Authentication;

use App\Entity\User;
use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use Orpheus\Service\SecurityService;

class LoginController extends HttpController {
	
	public function preRun($request): ?HttpResponse {
		$security = SecurityService::get();
		if( $security->isAuthenticated() ) {
			$navigationKey = $request->getParameter('rnk');
			$navigationTarget = $navigationKey ? $security->consumeNavigationKey($navigationKey) : u(DEFAULT_MEMBER_ROUTE);
			return new RedirectHttpResponse($navigationTarget);
		}
		return null;
	}
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$formToken = new FormToken();
		
		try {
			//			$request->hasData() && $formToken->validateForm($request);
			
			if( $request->hasData('submitLogin') ) {
				$input = $request->getData('login');
				$user = User::getUserByLogin($input['email'] ?? null, $input['password'] ?? null);
				SecurityService::get()->setPersistentAuthentication($user);
				reportSuccess(User::text('successLogin'));
				$navigationKey = $request->getParameter('rnk');
				$navigationTarget = $navigationKey ? SecurityService::get()->consumeNavigationKey($navigationKey) : null;
				if( $navigationTarget ) {
					return new RedirectHttpResponse($navigationTarget);
				}
				
			} else if( $request->hasData('submitRegister') ) {
				startReportStream('register');
				$input = $request->getData('user');
				$user = User::createAndGet($input, ['name', 'fullname', 'email', 'email_public', 'password']);
				sendAdminRegistrationEmail($user);
				unset($user);
				reportSuccess(User::text('successRegister'));
			}
			endReportStream();
		} catch( UserException $e ) {
			reportError($e);
			endReportStream();
		}
		
		return $this->renderHtml('app/user_login', ['formToken' => $formToken]);
	}
	
}
