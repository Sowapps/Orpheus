<?php


abstract class ControllerRoute {
	
	protected $name;
	protected $path;
	protected $controller;
	protected $options;
	protected $restrictTo;
	
	protected static $routes = array();
	
	protected static $routesRestrictions	= array();
	
	protected function __construct($name, $path, $controller, $restrictTo, $options) {
		$this->name			= $name;
		$this->path			= $path;
		$this->controller	= $controller;
		$this->restrictTo	= $restrictTo;
		$this->options		= $options;
	}
	
	public abstract function isMatchingRequest(InputRequest $request, &$values=array(), $alternative=false);
	
	public static function getRoutes() {
		static::initialize();
		return static::$routes;
// 		throw new Exception('The class "'.get_called_class().'" should override the `getRoutes()` static method from "'.get_class().'"');
	}
	
	public static function registerConfig($name, array $config) {
		throw new Exception('The class "'.get_called_class().'" should override the `registerConfig()` static method from "'.get_class().'"');
	}
	
	public function run(InputRequest $request) {
		if( !$this->controller || !class_exists($this->controller, true) ) {
			throw new NotFoundException('The controller "'.$this->controller.'" was not found');
		}
		$request->setRoute($this);
		if( !$this->isAccessible() ) {
			throw new ForbiddenException('This route is not accessible in this context');
		}
		$class	= $this->controller;
		$controller = new $class();
		if( !($controller instanceof Controller) ) {
			throw new NotFoundException('The controller "'.$this->controller.'" is not a valid controller, the class must inherit from "'.get_class().'"');
		}
		$result	= $controller->process($request);
		return $result;
	}
	
	protected static $initialized = false;
	public static function initialize() {
// 		debug('ControllerRoute::initialize()');
		if( static::isInitialized() ) { return; }
		static::$initialized = true;
		
		$conf	= YAML::build('routes', true, true);
		$routes	= $conf->asArray();
// 		debug('Routes', $routes);
		if( DEV_VERSION ) {
// 			debug('Loading dev routes');
			// If there is not file routes_dev, we get an empty array
			$conf	= YAML::build('routes_dev', true, true);
// 			debug('Routes dev', $conf->asArray());
			foreach( $conf->asArray() as $type => $typeRoutes ) {
// 				debug('Routes dev type : '.$type);
				if( isset($routes[$type]) ) {
					$routes[$type]	= array_merge($typeRoutes, $routes[$type]);
				} else {
					$routes[$type]	= $typeRoutes;
				}
			}
		}
// 		debug('Routes', $routes);
// 		die();
		foreach( $routes as $type => $typeRoutes ) {
			$routeClass	= $type.'Route';
// 			debug('$type => '.$type);
			if( !class_exists($routeClass, true) || !in_array(get_class(), class_parents($routeClass)) ) {
// 				debug('Invalid class');
				continue;
			}
			foreach( $typeRoutes as $routeName => $routeConfig ) {
				$routeClass::registerConfig($routeName, $routeConfig);
			}
		}
	}
	public static function isInitialized() {
		return static::$initialized;
	}
	public function getName() {
		return $this->name;
	}
	public function getPath() {
		return $this->path;
	}
	public function getController() {
		return $this->controller;
	}
	public function getOptions() {
		return $this->options;
	}
	
	public function isAccessible() {
		if( !CHECK_MODULE_ACCESS ) {
			return true;
		}
		if( $this->restrictTo ) {
			foreach( $this->restrictTo as $type => $options ) {
				if( empty(static::$routesRestrictions[$type]) ) {
					throw new Exception('Unknown route access type "'.$type.'" in config file');
				}
				if( !call_user_func(static::$routesRestrictions[$type], $this, $options)) {
					return false;
				}
			}
		}
		return true;
	}
	
	public static function registerAccessRestriction($type, $callable) {
		static::$routesRestrictions[$type]	= $callable;
	}
	
	
}
