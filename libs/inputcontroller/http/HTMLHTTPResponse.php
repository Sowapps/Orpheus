<?php


class HTMLHTTPResponse extends HTTPResponse {

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var string
	 */
	protected $layout;
	
	/**
	 * 
	 * @var array
	 */
	protected $values;
	
	/**
	 * @param string $body
	 */
	public function __construct($body=null) {
		$this->setBody($body);
	}
	
	
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
		if( !headers_sent() ) {
			header('Content-Type: text/html; charset="UTF-8"');
		}
		if( $this->body ) {
			// if already generated we display the body
			die($this->getBody());
		}
		$rendering	= new HTMLRendering();
		$env	= $this->values;
		$env['CONTROLLER_OUTPUT']	= $this->getControllerOutput();
		$rendering->display($this->layout, $env);
	}
	
	public function collectFrom($layout, $values=array()) {
		$this->layout	= $layout;
		$this->values	= $values;
	}
	
	public static function render($layout, $values=array()) {
		$response	= new static();
		$response->collectFrom($layout, $values);
		return $response;
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
