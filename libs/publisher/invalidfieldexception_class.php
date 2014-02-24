<?php

class InvalidFieldException extends UserException {
	
	private $type;
	private $field;
	private $value;
	private $args;
// 	private $path;
	
// 	public static $Path = null;
	
	// $field is INPUT field (not always the same as db field)
	public function __construct($message, $field, $value, $type=null, $domain=null, $typeArgs=array()) {
// 		debug('Creating with domain', $domain);
		parent::__construct($message, $domain);
		$this->field	= $field;
		$this->type		= $type;
		$this->value	= $value;
		$this->args		= is_array($typeArgs) ? $typeArgs : (is_object($typeArgs) ? (array) $typeArgs : array($typeArgs));
// 		$this->path		= static::$Path;
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
	
	public function removeArgs() {
		$this->args	= array();
	}
	
	public function getArgs() {
		return $this->args;
	}
	
// 	public function getPath() {
// 		return $this->args;
// 	}
	
	public function getText() {
		$args	= $this->args;
		$msg	= $this->getKey();
		if( !hasTranslation($msg, $this->domain) && hasTranslation($this->getMessage().'_field', $this->domain) ) {
			$msg	= $this->getMessage().'_field';
			$args	= array_merge(array('FIELD'=>t($this->getField(), $this->domain)), $args);
		}
		return t($msg, $this->domain, $args);
	}
	
	public function getKey() {
		return $this->field.'_'.$this->getMessage();
	}
	
	public function getReport() {
		return array(static::getText(), $this->field);
	}
	
	public static function from(UserException $e, $field, $value, $type=null, $args=array()) {
		return new static($e->getMessage(), $field, $value, $type, $e->getDomain(), $args);
	}
}