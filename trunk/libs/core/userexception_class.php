<?php
//! The user exception class
/*!
	This exception is thrown when an occured caused by the user.
*/
class UserException extends Exception {
	
	private $domain;
	
	public function __construct($message=null, $domain=null) {
		parent::__construct($message);
		$this->domain = $domain;
	}
	
	public function getDomain() {
		return $this->domain;
	}
	
	public function getText() {
		return $this->getMessage();
	}
	
	public function __toString() {
		return $this->getText();
	}
}
