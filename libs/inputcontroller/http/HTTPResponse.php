<?php


abstract class HTTPResponse extends OutputResponse {
	
	protected $code;
	
	/**
	 * Process response to client
	 */
	public abstract function run();
	
	public function process() {
		if( $this->code ) {
			http_response_code($this->code);
		}
		$this->run();
	}
	

	public function collectFrom($layout, $values=array()) {
		return null;
	}
	
	public function getCode() {
		return $this->code;
	}
	
	public function setCode($code) {
		$this->code = (int) $code;
		return $this;
	}
	
}
