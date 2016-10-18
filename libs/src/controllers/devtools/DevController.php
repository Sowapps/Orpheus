<?php

use Orpheus\InputController\HTTPController\HTTPRequest;

abstract class DevController extends AdminController {

	public function preRun(HTTPRequest $request) {
		parent::preRun($request);
// 		HTMLRendering::setDefaultTheme('admin');

		$this->addRouteToBreadcrumb(ROUTE_DEV_HOME);
	
		$this->setOption('mainmenu', 'devmenu');
		$this->setOption('main_title', 'devconsole_title');
		$this->setOption('invertedStyle', 0);
	
	}
	
}
