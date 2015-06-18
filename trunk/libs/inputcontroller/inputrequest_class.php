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
	public function findRoute();

	/**
	 * Resolve the current request by calling the matching contoller
	 * 
	 * @return Controller
	 */
	public function resolve() {
		$route	= $this->findRoute();
		$route->run();
	}
	
}
