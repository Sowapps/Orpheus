<?php

namespace App\Controller\Developer;

use App\Controller\Admin\AbstractAdminController;
use Orpheus\InputController\HttpController\HttpResponse;

abstract class DevController extends AbstractAdminController {
	
	public function preRun($request): ?HttpResponse {
		parent::preRun($request);
		
		$this->addRouteToBreadcrumb(ROUTE_DEV_HOME);
		
		//		$this->setOption('mainmenu', 'devmenu');
		//		$this->setOption('main_title', 'devconsole_title');
		//		$this->setOption('invertedStyle', 0);
		
		return null;
	}
	
}
