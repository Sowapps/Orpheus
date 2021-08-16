<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Orpheus\Config\AppConfig;
use Orpheus\InputController\HttpController\HtmlHttpResponse;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;

class DownloadController extends HttpController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HtmlHttpResponse|RedirectHttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$downloadURL = AppConfig::instance()->get($request->hasParameter('releases') ? 'releases_url' : 'download_url');
		if( $downloadURL ) {
			return new RedirectHttpResponse($downloadURL);
		}
		
		return HtmlHttpResponse::render('app/home');
	}
}
