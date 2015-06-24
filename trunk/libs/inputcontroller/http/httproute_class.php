<?php


class HTTPRoute extends ControllerRoute {
	
	protected $method;
	protected $defaults;
	protected $pathRegex;
	protected $pathVariables;
	
	protected static $typesRegex	= array();
	protected static $routes		= array();
	protected static $knownMethods	= array('GET', 'POST', 'PUT', 'DELETE');
	
	protected function __construct($name, $path, $controller, $method) {
		parent::__construct($name, $path, $controller);
		$this->method	= $method;
	}
	
	protected function generatePathRegex() {
		if( $this->pathRegex ) { return; }
		$variables	= &$this->pathVariables;
		$this->pathRegex	= preg_replace_callback(
			'#\{[^\}]+\}#sm',
			function($matches) use(&$variables) {
				list($p1, $p2) 	= explodeList(':', $matches[0], 2);
				// Optionnal only if there is a default value
				if( $p2 ) {
					// {regex|type:variable}
					$var	= $p2;
					$regex	= $p1;
					if( ctype_alpha($regex) && isset(static::$typesRegex[$regex]) ) {
						$regex	= static::$typesRegex[$regex];
					}
				} else {
					// {variable}, regex=[^\/]+
					$var	= $p1;
					$regex	= '[^\/]+';
				}
				$variables[]	= $var;
				return $regex;
			},
			$this->path
		);
	}
	
	public function isMatchingRequest(HTTPRequest $request) {
		return $request->get
	}
	
	public static function register($name, $path, $controller, $methods=null) {
		if( $methods && !is_array($methods) ) {
			$methods	= array($methods);
		}
		foreach( static::$knownMethods as $method ) {
			if( (!$methods && !empty(static::$routes[$name][$method])) || ($methods && !in_array($method, $methods)) ) {
				continue;
			}
			static::$routes[$name][$method]	= new static($name, $path, $controller, $method);
		}
	}
	
	public static function setTypeRegex($type, $regex) {
		static::$typesRegex[$type]	= $regex;
	}
	
	public static function getRoutes() {
		return static::$routes;
	}
	
	public static function getKnownMethods() {
		return static::$knownMethods;
	}
	
}

//http://fr.php.net/manual/fr/regexp.reference.escape.php
//http://fr.php.net/manual/fr/regexp.reference.character-classes.php
HTTPRoute::setTypeRegex('int',	'\d+');
HTTPRoute::setTypeRegex('id',	'[1-9]\d*');
HTTPRoute::setTypeRegex('slug',	'[a-z0-9\-_]+');

