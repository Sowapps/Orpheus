<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Orpheus\Config\AppConfig;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\RedirectHTTPResponse;

class DownloadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTMLHTTPResponse|RedirectHTTPResponse The output HTTP response
	 */
	public function run($request) {
		$downloadURL = AppConfig::instance()->get($request->hasParameter('releases') ? 'releases_url' : 'download_url');
		if( $downloadURL ) {
			return new RedirectHTTPResponse($downloadURL);
		}
		
		return HTMLHTTPResponse::render('app/home');
	}
}
