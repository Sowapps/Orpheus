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
		return $this->render(new HTMLHTTPResponse(), $layout, $values);
	}
	
	public function processUserException(UserException $exception, $values=array()) {
		return $this->getRoute()->processUserException($exception, $values);
	}
}
