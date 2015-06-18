<?php


class ControllerRoute {
	
	protected $name;
	protected $path;
	protected $controller;
	
	protected static $routes	= array();
	
	protected function __construct($name, $path, $controller) {
		$this->name			= $name;
		$this->path			= $path;
		$this->controller	= $controller;
	}
	
	public function isMatchingRequest(InputRequest $request);
	
	public function run(InputRequest $request) {
		if( !$this->controller || !class_exists($this->controller, true) ) {
			throw NotFoundException('controllerNotFound');
		}
		$controller = new {$this->controller}();
		if( !($controller instanceof Controller) ) {
			throw Exception('controllerNotFound');
		}
		$controller->run($request);
	}
	
}
