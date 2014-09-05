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
	
	public function htmlInputAttr($args) {
		return '';
	}
	
	public function getHTMLInputAttr($Field) {
		return array();
	}
	
	public function emptyIsNull($field) {
		return true;
	}
	
	
	public function parseArgs($args) {
		return new stdClass();
	}
	
	public function validate($Field, &$value, $inputData, &$ref) {}
	
	public function format($args, &$value) {}
	
}
