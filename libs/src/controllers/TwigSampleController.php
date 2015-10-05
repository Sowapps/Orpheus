<?php

class TwigSampleController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		return new HTMLHTTPResponse((new TwigRendering())->render('sample_twig'));
	}

}
