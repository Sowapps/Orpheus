<?php


class HTMLHTTPResponse extends HTTPResponse {

	/**
	 * @var string
	 */
// 	protected $body;

	/**
	 * @var string
	 */
	protected $layout;
	
	/**
	 * 
	 * @var array
	 */
	protected $values;
	
// 	/**
// 	 * @param string $body
// 	 */
// 	public function __construct() {
// 	public function __construct($body=null) {
// 		$this->setBody($body);
// 	}
	
	
// 	/**
// 	 * @return string
// 	 */
// 	public function __toString() {
// 		return $this->body.'';
// 	}
	
	/**
	 * @see HTTPResponse::run()
	 */
	public function run() {
		header('Content-Type: text/html; charset="UTF-8"');
		$rendering	= new HTMLRendering();
		$env	= $this->values;
		$env['CONTROLLER_OUTPUT']	= $this->getControllerOutput();
// 		debug('Rendering display of '.$this->layout, $env);
// 		die('Die in '.__FILE__.':'.__LINE__);
		$rendering->display($this->layout, $env);
// 		echo $this->body;
	}
	
	public function collectFrom($layout, $values=array()) {
// 		debug('Collect layout '.$layout);
		$this->layout	= $layout;
		$this->values	= $values;
// 		$rendering	= new HTMLRendering();
// 		$values['CONTROLLER_OUTPUT']	= $this->getControllerOutput();
// // 		debug('Render with values '.$layout, $values);
// 		return $rendering->render($layout, $values);
// 		$this->setBody($rendering->render($layout, $values));
	}
	
	public static function render($layout, $values=array()) {
		$response	= new static();
// 		$response->layout	= $layout;
// 		$response->values	= $values;
		$response->collectFrom($layout, $values);
		return $response;
// 		$rendering	= new HTMLRendering();
// 		return new static($rendering->render($layout, $values));
	}
	
	/**
	 * Generate HTMLResponse from 
	 * 
	 * @param Exception $exception
	 * @param string $action
	 */
	public static function generateFromException(Exception $exception, $action='Handling the request') {
		$code	= $exception->getCode();
		if( !$code ) {
			$code	= HTTP_INTERNAL_SERVER_ERROR;
		}
		$response	= new static(convertExceptionAsHTMLPage($exception, $code, $action));
		$response->setCode($code);
// 		http_response_code($code);
		return $response;
// 		$code	= $exception->getCode();
// 		http_response_code($code ? $code : 500);
// 		$rendering	= new HTMLRendering();
// 		return new static($rendering->render('error', array(
// 			'action'	=> $action,
// 			'date'		=> dt(),
// 			'report'	=> $exception->getMessage()."<br />\n<pre>".$exception->getTraceAsString()."</pre>",
// 		)));
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
}
