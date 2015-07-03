<?php


abstract class InputRequest {
	
	protected $path;
	protected $parameters;
	protected $input;
	
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
	
	public abstract function getRoutes();

	/**
	 * Resolve the current request by calling the matching contoller
	 * 
	 * @return Controller
	 */
	public function resolve() {
		$route	= $this->findFirstMatchingRoute();
		if( !$route ) {
			throw new NotFoundException('noRoute');
		}
		$route->run();
	}
	
	public function getPath() {
		return $this->path;
	}
	protected function setPath($path) {
		$this->path = $path;
		return $this;
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
	
	protected static $mainRequest;

	public static function getMainRequest() {
		return static::$mainRequest;
	}
	
	
	
	
}
