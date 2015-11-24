<?php


class DownloadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$downloadURL	= GlobalConfig::instance()->get('download_url');
		
		return new RedirectHTTPResponse($downloadURL ? $downloadURL : 'home');
	}

	
}
