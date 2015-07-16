<?php

abstract class AdminHTTPController extends HTTPController {

	public function preRun(HTTPRequest $request) {
		HTMLRendering::setDefaultTheme('admin');
	}

}
