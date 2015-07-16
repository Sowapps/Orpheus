<?php

abstract class AdminHTTPController extends HTTPController {

	public function preRun(HTTPRequest $request) {
		HTMLRendering::setDefaultTheme('admin');
		
		/* @var $USER SiteUser */
		global $USER;
		if( !$USER || !$USER->canAccess($request->getRouteName()) ) {
			throw new ForbiddenException('forbiddenAccessToRoute');
		}
	}

}
