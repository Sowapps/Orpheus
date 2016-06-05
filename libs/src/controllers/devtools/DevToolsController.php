<?php
/*
 * Vérifier écriture sur le FS
 * Vérifier BDD
 * Install bdd
 * Install user
 * 
 */

abstract class DevToolsController extends HTTPController {
	

	public function preRun(HTTPRequest $request) {
		parent::preRun($request);
		HTMLRendering::setDefaultTheme('admin');
		
		$this->setOption('mainmenu', 'devmenu');
	
	}

}
