<?php

class StaticPageController extends HTTPController {

	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		$options	= $request->getRoute()->getOptions();
		if( empty($options['render']) ) {
			throw new Exception('The StaticPageController requires a render option, add it to your routes configuration.');
		}
		return HTMLHTTPResponse::render($options['render']);
	}

}

