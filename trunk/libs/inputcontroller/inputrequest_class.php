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
			throw new NotFoundException('global', 'noRoute');
		}
		$route->run();
	}
	
	public function getPath() {
		return $this->path;
	}
	public function getParameters() {
		return $this->parameters;
	}
	public function getInput() {
		return $this->input;
	}
	
	protected static $mainRequest;

	public static function getMainRequest() {
		return static::$mainRequest;
	}
	
}
