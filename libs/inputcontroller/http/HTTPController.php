<?php


abstract class HTTPController extends Controller {
	
	/**
	 * 
	 * @param HTTPRequest $request
	 * @return HTTPResponse
	 */
	public abstract function run(HTTPRequest $request);

	public function preRun(HTTPRequest $request) {
	}
	
	public function postRun(HTTPRequest $request, HTTPResponse $response) {
	}
	
	public function renderHTML($layout, $values=array()) {
		// 		$rendering	= static::getRenderer();
// 		$rendering	= new HTMLRendering();
		return $this->render(new HTMLHTTPResponse(), $layout, $values);
	}
	
	public function processUserException(UserException $e) {
		reportError($e);
		return $this->render(new HTMLHTTPResponse(), 'page_skeleton', array(
			'titleRoute'	=> 'usererror',
			'Content'		=> ''
		));
	}
}
