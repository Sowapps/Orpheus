<?php

abstract class AdminController extends HTTPController {

	public function preRun(HTTPRequest $request) {
		HTMLRendering::setDefaultTheme('admin');
		
		/* @var $USER User */
// 		if( CHECK_MODULE_ACCESS ) {
// 			global $USER;
// 			if( !$USER || !$USER->canAccess($request->getRouteName()) ) {
// 				throw new ForbiddenException('forbiddenAccessToRoute');
// 			}
// 		}
	}
	
	public function render($response, $layout, $values=array()) {
		if( isset($GLOBALS['USER']) ) {
			$values['USER']	= $GLOBALS['USER'];
		}
		return parent::render($response, $layout, $values);
	}

}
