<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use Exception;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use Orpheus\Service\SecurityService;

class RedirectController extends HttpController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		$authenticated = SecurityService::get()->isAuthenticated();
		return new RedirectHttpResponse(u($authenticated ? DEFAULT_MEMBER_ROUTE : DEFAULT_ROUTE));
	}
	
	
}
