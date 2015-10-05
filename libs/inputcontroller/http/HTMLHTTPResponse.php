<?php


class HTMLHTTPResponse extends HTTPResponse {

	/**
	 * @var string
	 */
	protected $body;
	
	/**
	 * @param string $body
	 */
	public function __construct($body=null) {
		$this->setBody($body);
	}
	
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->body.'';
	}
	
	/**
	 * @see HTTPResponse::run()
	 */
	public function run() {
		header('Content-Type: text/html; charset="UTF-8"');
		echo $this->body;
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
	
	/**
	 * @var Rendering
	 */
// 	protected static $renderer;
	
// 	public static function getRenderer() {
// 		if( !static::$renderer ) {
// 			static::setRenderer(new HTMLRendering());
// 		}
// 		return static::$renderer;
// 	}
	
// 	public static function setRenderer(Rendering $renderer) {
// 		static::$renderer	= $renderer;
// 	}
	
	public function collectFrom($layout, $values=array()) {
// 		$rendering	= static::getRenderer();
		$rendering	= new HTMLRendering();
		$this->setBody($rendering->render($layout, $values));
	}
	
	public static function render($layout, $values=array()) {
// 		$rendering	= static::getRenderer();
		$rendering	= new HTMLRendering();
		return new static($rendering->render($layout, $values));
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
		return $this;
	}
	
}
