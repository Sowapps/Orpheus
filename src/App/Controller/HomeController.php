<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class HomeController extends HttpController {
	
	/**
	 * Controller declaration
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$this->setOption(self::OPTION_PAGE_TITLE, t('home.title', DOMAIN_APP));
		$this->setOption(self::OPTION_PAGE_DESCRIPTION, t('home.description', DOMAIN_APP));
		
		return $this->renderHtml('app/home');
	}
	
}
