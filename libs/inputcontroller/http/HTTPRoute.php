<?php


class HTTPRoute extends ControllerRoute {
	
	protected $method;
	protected $defaults;
	protected $pathRegex;
	protected $pathVariables;
	
	const METHOD_GET	= 'GET';
	const METHOD_POST	= 'POST';
	const METHOD_PUT	= 'PUT';
	const METHOD_DELETE	= 'DELETE';
	
	protected static $typesRegex	= array();
	protected static $routes		= array();
	protected static $knownMethods	= array('GET', 'POST', 'PUT', 'DELETE');
	
	protected function __construct($name, $path, $controller, $method, $restrictTo, $options) {
		parent::__construct($name, $path, $controller, $restrictTo, $options);
		$this->method	= $method;
		$this->generatePathRegex();
	}
	
	/**
	 * Format the current route to get an URL from path
	 * @param string[] $values
	 * @return string
	 * @throws Exception
	 */
	public function formatURL($values=array()) {
		$path = preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function($matches) use($values) {
				$var = $regex = null;
				static::extractVariable($matches[1], $var, $regex);
				if( !isset($values[$var]) ) {
					throw new Exception('The variable `'.$var.'` is missing to generate URL for route '.$this->name);
				}
				$value	= $values[$var].'';
				if( !preg_match('#^'.$regex.'$#', $value) ) {
					throw new Exception('The given value "'.$value.'" of variable `'.$var.'` is not matching the regex requirements to generate URL for route '.$this->name);
				}
				return $value;
			},
			$this->path
		);
		return SITEROOT.(isset($path[0]) && $path[0]==='/' ? substr($path, 1) : $path);
	}
	
	public function __toString() {
		return $this->method.'("'.$this->path.'")';
	}
	
	protected static function extractVariable($str, &$var=null, &$regex=null) {
		list($p1, $p2) 	= explodeList(':', $str, 2);
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
	}
	
	protected function generatePathRegex() {
		if( $this->pathRegex ) { return; }
// 		$variables	= &$this->pathVariables;
		$variables	= array();
		$this->pathRegex	= preg_replace_callback(
			'#\{([^\}]+)\}#sm',
			function($matches) use(&$variables) {
// 				debug('$matches', $matches);
				static::extractVariable(str_replace('\.', '.', $matches[1]), $var, $regex);
				$variables[]	= $var;
				return '('.$regex.')';
			},
			str_replace('.', '\.', $this->path)
		);
		$this->pathVariables	= $variables;
	}
	
	/**
	 * @param HTTPRequest $request
	 * @param array $values
	 * @see ControllerRoute::isMatchingRequest()
	 */
	public function isMatchingRequest(InputRequest $request, &$values=array()) {
		// Method match && Path match (variables included)
// 		debug('Route '.$this.' is matching request '.$request);
		if( $this->method !== $request->getMethod() ) {
			return false;
		}
// 		debug('Method ok');
// 		debug('Path regex '.'#^'.$this->pathRegex.'$#i');
		if( preg_match('#^'.$this->pathRegex.'$#i', $request->getPath(), $matches) ) {
			unset($matches[0]);
			$values	= array_combine($this->pathVariables, $matches);
// 			debug('Path ok');
			return true;
		}
// 		debug('Path does not match');
		return false;
	}
	
	public static function registerConfig($name, array $config) {
// 		debug('registerConfig('.$name.')', $config);
		if( empty($config['path']) ) {
			throw new Exception('Missing a valid `path` in configuration of route "'.$name.'"');
		}
		if( empty($config['controller']) ) {
			if( !empty($config['render']) ) {
				$config['controller']	= 'StaticPageController';
			} else {
				throw new Exception('Missing a valid `controller` in configuration of route "'.$name.'"');
			}
		}
		if( !isset($config['restrictTo']) ) {
			$config['restrictTo']	= null;
		}
		$options	= $config;
		unset($options['path'], $options['controller'], $options['method'], $options['restrictTo']);
		static::register($name, $config['path'], $config['controller'], isset($config['method']) ? $config['method'] : null, $config['restrictTo'], $options);
	}
	
	public static function register($name, $path, $controller, $methods=null, $restrictTo=null, $options=array()) {
		if( $methods && !is_array($methods) ) {
			$methods	= array($methods);
		}
		foreach( static::$knownMethods as $method ) {
			if( (!$methods && !empty(static::$routes[$name][$method])) || ($methods && !in_array($method, $methods)) ) {
				continue;
			}
			static::$routes[$name][$method]	= new static($name, $path, $controller, $method, $restrictTo, $options);
		}
	}
	
	public static function setTypeRegex($type, $regex) {
		static::$typesRegex[$type]	= $regex;
	}
	
	public static function getRoutes() {
// 		$routes	= parent::getRoutes();
		return static::$routes;
	}
	
	public static function getRoute($route, $method=self::METHOD_GET) {
// 		$routes	= static::getRoutes();
		return isset(static::$routes[$route][$method]) ? static::$routes[$route][$method] : null;
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

