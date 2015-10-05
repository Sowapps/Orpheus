<?php


abstract class InputRequest {
	
	protected $path;
	protected $parameters;
	protected $input;
	protected $route;
	
	public function __construct($path, $parameters, $input) {
		$this->path			= $path;
		$this->parameters	= $parameters;
		$this->input		= $input;
	}
	
	/**
	 * Find a matching route according to the request
	 * 
	 * @return Route
	 */
	public function findFirstMatchingRoute() {
		foreach( $this->getRoutes() as $route ) {
			/* @var $route HTTPRoute */
			if( $route->isMatchingRequest($this) ) {
				return $route;
			}
		}
		return null;
	}
	
	public function process() {
		return $this->processRoute($this->findFirstMatchingRoute());
	}
	public function processRoute($route) {
// 		$route	= static::$mainRequest->findFirstMatchingRoute();
		if( !$route ) {
			throw new NotFoundException('No route matches the current request '.$this);
		}
		$this->setRoute($route);
		return $this->route->run($this);
	}
	
	public abstract function getRoutes();

	/**
	 * Resolve the current request by calling the matching contoller
	 * 
	 * @return Controller
	 */
// 	public function resolve() {
// 		$route	= $this->findFirstMatchingRoute();
// 		if( !$route ) {
// 			throw new NotFoundException('noRoute');
// 		}
// 		$route->run();
// 	}
	
	public function getPath() {
		return $this->path;
	}
	protected function setPath($path) {
		$this->path = $path;
		return $this;
	}
	
	public function hasParameter($key) {
		return $this->getParameter($key, null) !== null;
	}
	
	public function getParameter($key, $default=null) {
// 		debug('$this->parameters', $this->parameters);
		return apath_get($this->parameters, $key, $default);
	}
	
	public function getParameters() {
		return $this->parameters;
	}
	protected function setParameters($parameters) {
		$this->parameters = $parameters;
		return $this;
	}
	
	public function getInput() {
		return $this->input;
	}
	protected function setInput($input) {
		$this->input = $input;
		return $this;
	}
	
	public function getInputValue($key, $default=null) {
		return apath_get($this->input, $key, $default);
	}
	
	public function hasInputValue($key) {
		return $this->getInputValue($key, null) !== null;
	}
	
	protected static $mainRequest;

	/**
	 * @return InputRequest
	 */
	public static function getMainRequest() {
		return static::$mainRequest;
	}
	
	public function getRouteName() {
		return $this->route->getName();
	}
	
	public function getRoute() {
		return $this->route;
	}
	public function setRoute($route) {
		$this->route = $route;
		return $this;
	}
	
	
	
	
	
}
