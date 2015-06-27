<?php


class HTTPRequest extends InputRequest {

	protected $method;
	protected $contentType;
	protected $scheme;
// 	protected $query;// Parameters
// 	protected $input;// Input

	/**
	 * @see InputRequest::__construct()
	 */
	public function __construct($method, $path, $scheme, $domain, $parameters, $headers, $contentType, $input) {
		parent::__construct($path, $parameters, $input);
		$this->method		= $method;
		$this->contentType	= $contentType;
	}

	
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
	
	/**
	 * Get the method
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * Get the method
	 * @return HTTPRequest
	 */
	public function generateFromEnvironment() {

		// Get Content type
// 		list($contentType, $contentOptions)	= explodeList(';', $_SERVER['CONTENT_TYPE'], 2);
		list($inputType)	= explodeList(';', $_SERVER['CONTENT_TYPE'], 2);
		$inputType	= trim($inputType);
		
		// Get input
		$input	= null;
		if( $inputType === 'application/json' ) {
// 		if( isset($_SERVER['CONTENT_TYPE']) && strpos(, 'application/json')!==false ) {
			$input	= json_decode(file_get_contents('php://input'), true);
			if( $input === null ) {
				throw new Exception('malformedJSONBody', HTTP_BAD_REQUEST);
			}
		} else if( isset($_POST) ) {
			//application/x-www-form-urlencoded
			$input	= $_POST;
		}
// 		$FORMAT	= isGET('format') ? strtolower(GET('format')) : 'json';
// 		$PATH	= GET('_path');
// 		$METHOD	= $_SERVER['REQUEST_METHOD'];
		return new static($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], '', '', $_GET, getallheaders(), $inputType, $input);
	}

	public static function handleCurrentRequest() {
		
		static::$mainRequest	= HTTPRequest::generateFromEnvironment();
		debug('$request', static::$mainRequest);
		die();
		$route		= static::$mainRequest->findFirstMatchingRoute();
		$route->run(static::$mainRequest);
	}
}
