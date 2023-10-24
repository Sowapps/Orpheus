<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Authentication;

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use Orpheus\Service\SecurityService;

class ImpersonatingTerminateController extends HttpController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$security = SecurityService::get();
		$security->setActiveUser($security->getAuthenticatedUser());
		
		return new RedirectHttpResponse(DEFAULT_MEMBER_ROUTE);
	}
	
}
