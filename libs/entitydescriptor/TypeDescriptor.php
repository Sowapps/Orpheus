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
	 * @param string[] $args Arguments
	 * @return stdClass
	 */
	public function parseArgs(array $args) {
		return new stdClass();
	}

	/**
	 * Validate value
	 *
	 * @param FieldDescriptor $field The field to validate
	 * @param string $value The field value to validate
	 * @param array $input The input to validate
	 * @param PermanentEntity $ref The object to update, may be null
	 */
	public function validate(FieldDescriptor $field, &$value, $input, &$ref) {}
	
	/**
	 * Format value before being validated
	 * 
	 * @param FieldDescriptor $field The field to format
	 * @param string $value The field value to format
	 * @param array $input The input to validate
	 * @param PermanentEntity $ref The object to update, may be null
	 */
	public function preFormat(FieldDescriptor $field, &$value, $input, &$ref) {}
	
	/**
	 * Format value after being validated
	 * 
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 */
	public function format(FieldDescriptor $field, &$value) {}


	/**
	 * Parse the value from SQL scalar to PHP type
	 *
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 * @return string The parse $value
	 * @see PermanentObject::formatFieldValue()
	 */
	public function parseValue(FieldDescriptor $field, $value) {
		return $value;
	}

	/**
	 * Format the value from PHP type to SQL scalar 
	 *
	 * @param FieldDescriptor $field The field to parse
	 * @param string $value The field value to parse
	 * @return string The parse $value
	 * @see PermanentObject::formatFieldValue()
	 */
	public function formatValue(FieldDescriptor $field, $value) {
		return $value;
	}
	
}
