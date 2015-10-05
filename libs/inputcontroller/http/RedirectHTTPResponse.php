<?php


class RedirectHTTPResponse extends HTTPResponse {
	
	protected $destinationURI;
	
	public function __construct($destinationURI) {
		$this->setCode(HTTP_MOVED_TEMPORARILY);
		if( exists_route($destinationURI) ) {
			$destinationURI	= u($destinationURI);
		}
		$this->setDestinationURI($destinationURI);
	}
	
	/**
	 * @see HTTPResponse::process()
	 */
	public function run() {
		
		header('Location: '.$this->destinationURI);
// 			header('HTTP/1.1 301 Moved Permanently', true, 301);
		
	}
	
	public function setPermanent() {
		$this->setCode(HTTP_MOVED_PERMANENTLY);
	}
	
	public function getDestinationURI() {
		return $this->destinationURI;
	}
	public function setDestinationURI($destinationURI) {
		$this->destinationURI = $destinationURI;
		return $this;
	}
	
}
