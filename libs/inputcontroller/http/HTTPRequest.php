<?php

class HTTPRequest extends InputRequest {

	protected $method;
	protected $scheme;
	protected $domain;
	protected $headers;
	protected $cookies;
	protected $files;
	protected $inputType;
	
	/**
	 * The values in path
	 * 
	 * @var strClass
	 */
	protected $pathValues;

	/**
	 * @see InputRequest::__construct()
	 */
	public function __construct($method, $path, $parameters=null, $input=null) {
		parent::__construct($path, $parameters, $input);
		$this->setMethod($method);
	}
	
	public function __toString() {
		return $this->method.'("'.$this->path.'")';
	}

	
	/**
	 * Find a matching route according to the request
	 * 
	 * @return Route
	 */
	public function findFirstMatchingRoute() {
// 		debug('Routes', $this->getRoutes());
		foreach( $this->getRoutes() as $methodRoutes ) {
			if( !isset($methodRoutes[$this->method]) ) { continue; }
			/* @var $route HTTPRoute */
			$route	= $methodRoutes[$this->method];
			if( $route->isMatchingRequest($this, $values) ) {
				$this->pathValues	= (object) $values;
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
	 * @return HTTPRequest
	 */
	public static function generateFromEnvironment() {

		// Get Content type
// 		list($contentType, $contentOptions)	= explodeList(';', $_SERVER['CONTENT_TYPE'], 2);
		if( !empty($_SERVER['CONTENT_TYPE']) ) {
			list($inputType)	= explodeList(';', $_SERVER['CONTENT_TYPE'], 2);
			$inputType	= trim($inputType);
		} else {
			$inputType	= 'application/x-www-form-urlencoded';
		}
		
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
// 		debug('$_SERVER', $_SERVER);
// 		$request	= new static($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET);
		$request	= new static($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $_GET);
		$request->setContent($input, $inputType)
			->setScheme(!empty($_SERVER['HTTPS']) ? 'https' : 'http')
			->setDomain($_SERVER['HTTP_HOST'])
			->setHeaders(getallheaders())
			->setCookies($_COOKIE)
			->setFiles($_FILES);
// 		return new static($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], '', '', $_GET, getallheaders(), $inputType, $input);
		return $request;
	}

	public static function handleCurrentRequest() {
		try {
			HTTPRoute::initialize();
			static::$mainRequest	= static::generateFromEnvironment();
	//		debug('$request', static::$mainRequest);
	//		die();
// 			$route	= static::$mainRequest->findFirstMatchingRoute();
// 			if( !$route ) {
// 				throw new NotFoundException('No route matches the current request '.static::$mainRequest);
// 			}
// 			$response	= $route->run(static::$mainRequest);
			$response	= static::$mainRequest->process();
		} catch( Exception $e ) {
			$response	= HTMLHTTPResponse::generateFromException($e);
		}
		$response->process();
		die();
	}
	
	
	
	/**
	 * Get the method
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}
	protected function setMethod($method) {
		$this->method = $method;
		return $this;
	}
	
	public function isGET() { return $this->method === HTTPRoute::METHOD_GET; }
	public function isPOST() { return $this->method === HTTPRoute::METHOD_POST; }
	public function isPUT() { return $this->method === HTTPRoute::METHOD_PUT; }
	public function isDELETE() { return $this->method === HTTPRoute::METHOD_DELETE; }
	
	public function getScheme() {
		return $this->scheme;
	}
	protected function setScheme($scheme) {
		$this->scheme = $scheme;
		
		return $this;
	}
	public function getDomain() {
		return $this->domain;
	}
	protected function setDomain($domain) {
		$this->domain = $domain;
		return $this;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	protected function setHeaders($headers) {
		$this->headers = $headers;
		return $this;
	}
	
	public function getInputType() {
		return $this->inputType;
	}
	protected function setInputType($inputType) {
		$this->inputType = $inputType;;
		return $this;
	}
	
	protected function setContent($content, $contentType) {
		return $this->setInput($content)->setInputType($contentType);
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	protected function setCookies($cookies) {
		$this->cookies = $cookies;
		return $this;
	}
	
	public function getFiles() {
		return $this->files;
	}
	protected function setFiles($files) {
		$this->files = $files;
		return $this;
	}

	public function getAllData() {
		return $this->getInput();
	}
	
	public function getData($key, $default=null) {
		return $this->getInputValue($key, $default);
	}
	
	public function getArrayData($key) {
		return $this->getInputValue($key, array());
	}
	
	public function hasData($key) {
		return $this->hasInputValue($key);
	}
	
	public function hasDataKey($path=null, &$value=null) {
		$v = $this->getData($path);
		if( !$v || !is_array($v) ) { return false; }
		$value	= key($v);
		return true;
	}
	
	/**
	 * @return stdClass
	 */
	public function getPathValues() {
		return $this->pathValues;
	}
	
	public function getPathValue($key, $default=null) {
		return isset($this->pathValues->$key) ? $this->pathValues->$key : $default;
	}
}
