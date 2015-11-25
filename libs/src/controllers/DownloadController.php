<?php


class DownloadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$downloadURL	= GlobalConfig::instance()->get($request->hasParameter('releases') ? 'releases_url' : 'download_url');
// 		debug('$downloadURL => '.$downloadURL);

// 		return HTMLHTTPResponse::render('app/home');
		return new RedirectHTTPResponse($downloadURL ? $downloadURL : 'home');
	}

	
}
