<?php

namespace Demo\Controller\Developer;

use Demo\Controller\Admin\AdminController;

abstract class DevController extends AdminController {
	
	public function preRun($request) {
		parent::preRun($request);
		
		$this->addRouteToBreadcrumb(ROUTE_DEV_HOME);
		
		$this->setOption('mainmenu', 'devmenu');
		$this->setOption('main_title', 'devconsole_title');
		$this->setOption('invertedStyle', 0);
		
	}
	
}
