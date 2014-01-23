<?php

abstract class TypeDescriptor {

	protected $name;
	protected $writable;
	protected $nullable;
	
// 	public function __construct() {
// 	}
	
	public function getName() {
		return $this->name;
	}
	
	public function isWritable() {
		return $this->writable;
	}
	
	public function isNullable() {
		return $this->nullable;
	}
	
// 	public function isType($type) {
// 		return $this->name==$type;
// 	}
	
// 	public function knowType($type) {
// 		return $this->isType($type) || (isset($this->parent) && $this->parent->knowType($type));
// 	}
	
	public function parseArgs($args) {
		return new stdClass();
	}
	
// 	public function parseArgs($args) {
// 		return isset($this->argsParser) ? call_user_func($this->argsParser, $args) : new stdClass();
// 	}
	
	public function validate($Field, &$value, $inputData) {
// 		if( isset($this->parent) ) {
// 			$this->parent->validate($args, $value, $inputData);
// 		} else
// 		if( !isset($this->validator) ) {
// 			throw new Exception('noValidator');
// 		}
// 		if( isset($this->validator) ) {
// 			call_user_func_array($this->validator, array($args, &$value, $inputData));
// 		}
	}
	
	public function format($args, &$value) {
// 		if( isset($this->parent) ) {
// 			$this->parent->format($args, $value);
// 		}
// 		if( isset($this->formatter) ) {
// 			call_user_func_array($this->formatter, array($args, &$value));
// 		}
	}
	
}
