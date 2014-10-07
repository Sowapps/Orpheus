<?php

abstract class TypeDescriptor {

	protected $name;
	protected $writable;
	protected $nullable;
	
// 	public function __construct() {
// 	}
	
	/**
	 * Get the type name
	 * @return string the type name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get true if field is writable
	 * @return boolean
	 */
	public function isWritable() {
		return $this->writable;
	}

	/**
	 * Get true if field is nullable
	 * @return boolean
	 */
	public function isNullable() {
		return $this->nullable;
	}

	/**
	 * Get the html input attributes string for the given args
	 * @return string
	 */
	public function htmlInputAttr($args) {
		return '';
	}

	/**
	 * Get the html input attributes array for the given Field descriptor
	 * @return string[]
	 */
	public function getHTMLInputAttr($Field) {
		return array();
	}

	/**
	 * Get true if we consider null an empty input string
	 * @return boolean
	 */
	public function emptyIsNull($field) {
		return true;
	}
	
	/**
	 * Parse args from field declaration
	 * @param $args string[] Arguments
	 * @return stdClass
	 */
	public function parseArgs($args) {
		return new stdClass();
	}
	
	public function validate($Field, &$value, $inputData, &$ref) {}
	
	public function format($args, &$value) {}
	
}
