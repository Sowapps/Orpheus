<?php


abstract class ControllerRoute {
	
	protected $name;
	protected $path;
	protected $controller;
	
	protected function __construct($name, $path, $controller) {
		$this->name			= $name;
		$this->path			= $path;
		$this->controller	= $controller;
	}
	public abstract function isMatchingRequest(InputRequest $request, &$values=array());
	
	
	public static function registerConfig($name, array $config) {
		throw new Exception('The class "'.get_called_class().'" should override the `registerConfig()` method from "'.get_class().'"');
	}
	
	public function run(InputRequest $request) {
		if( !$this->controller || !class_exists($this->controller, true) ) {
			throw NotFoundException('controllerNotFound');
		}
		$class	= $this->controller;
		$controller = new $class();
		if( !($controller instanceof Controller) ) {
			throw Exception('controllerNotFound');
		}
		$controller->run($request);
	}
	
	protected static $initialized = false;
	public static function initialize() {
		if( static::$initialized ) { return; }
		static::$initialized = true;
		
		$conf	= YAML::build('routes', true, true);
		$routes	= $conf->asArray();
		foreach( $routes as $type => $typeRoutes ) {
			$routeClass	= $type.'Route';
			if( !class_exists($routeClass, true) || !in_array(get_class(), class_parents($routeClass)) ) { continue; }
			foreach( $typeRoutes as $routeName => $routeConfig ) {
				$routeClass::registerConfig($routeName, $routeConfig);
			}
		}
	}
	
}
