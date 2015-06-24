<?php


class HTTPRequest extends InputRequest {

	protected $method;
// 	protected $query;// Parameters
// 	protected $body;// Input
	
	/**
	 * Find a matching route according to the request
	 * 
	 * @return Route
	 */
	public function findFirstMatchingRoute() {
		foreach( $this->getRoutes() as $methodRoutes ) {
			if( !isset($methodRoutes[$this->method]) ) { continue; }
			/* @var $route HTTPRoute */
			$route	= $methodRoutes[$this->method];
			if( $route->isMatchingRequest($this) ) {
				return $route;
			}
		}
		return null;
	}
	
	/**
	 * @return HTTPRoute[]
	 * @see InputRequest::getRoutes()
	 */
	public function getRoutes() {
		return HTTPRoute::getRoutes();
	}
}
