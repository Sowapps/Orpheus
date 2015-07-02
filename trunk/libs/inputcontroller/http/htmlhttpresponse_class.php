<?php


class HTMLHTTPResponse extends HTTPResponse {

	/**
	 * @var string
	 */
	protected $content;
	
	/**
	 * @param string $content
	 */
	public function __construct($content) {
		$this->content	= $content;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->content;
	}
	
	/**
	 * @see HTTPResponse::process()
	 */
	public function process() {
		header('Content-Type: text/html; charset="UTF-8"');
		echo $this->content;
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
			$code	= 500;
		}
		http_response_code($code);
		return new static(convertExceptionAsHTMLPage($exception, $code, $action));
// 		$code	= $exception->getCode();
// 		http_response_code($code ? $code : 500);
// 		$rendering	= new HTMLRendering();
// 		return new static($rendering->render('error', array(
// 			'action'	=> $action,
// 			'date'		=> dt(),
// 			'report'	=> $exception->getMessage()."<br />\n<pre>".$exception->getTraceAsString()."</pre>",
// 		)));
	}
	
	public static function renderWithPHP($layout, $values=array()) {
		$rendering	= new HTMLRendering();
		return new static($rendering->render($layout, $values));
	}
}
