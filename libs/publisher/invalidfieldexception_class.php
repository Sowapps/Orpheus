<?php

class InvalidFieldException extends UserException {
	
	private $type;
	private $field;
	private $value;
	private $args;
	
	// $field is INPUT field (not always the same as db field)
	public function __construct($message, $field, $value, $type=null, $domain=null, $args=array()) {
		parent::__construct($message, $domain);
		$this->field = $field;
		$this->type = $type;
		$this->value = $value;
		$this->args = is_array($args) ? $args : array($args);
	}
	
	public function getField() {
		return $this->field;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getText() {
		return t($this->getMessage(), $this->getDomain(), $this->args);
	}
	
	public static function from(UserException $e, $field, $value, $type=null, $args=array()) {
		return new static($e->getMessage(), $field, $value, $type, $e->getDomain(), $args);
	}
}