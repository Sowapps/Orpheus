<?php

class RedirectController extends HTTPController {

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		$options	= $request->getRoute()->getOptions();
		if( empty($options['redirect']) ) {
			throw new Exception('The RedirectController requires a redirect option, add it to your route configuration.');
		}
		return new RedirectHTTPResponse(u($options['redirect']));
	}

}

