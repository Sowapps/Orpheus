<?php

abstract class AdminController extends HTTPController {

	public function preRun(HTTPRequest $request) {
		HTMLRendering::setDefaultTheme('admin');
		
		/* @var $USER User */
		global $USER;
		if( !$USER || !$USER->canAccess($request->getRouteName()) ) {
			throw new ForbiddenException('forbiddenAccessToRoute');
		}
	}

}
