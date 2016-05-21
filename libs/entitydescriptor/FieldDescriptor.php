<?php

class FieldDescriptor {

	public $name;
	public $type;
	public $args;
	public $default;
	public $writable;
	public $nullable;
	/* Field : String name, TypeDescriptor type, Array args, default, writable, nullable */
	
	/** 
	 * Constructs the Field Descriptor
	 * @param string $name
	 * @param string $type
	 */
	public function __construct($name, $type) {
		$this->name	= $name;
		$this->type	= $type;
	}
	
	/** 
	 * Magic toString
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}
	
	/** 
	 * Get arg value for this field
	 * @param	$key string The argument key
	 * @return	string|integer|NULL The argument value
	 */
	public function arg($key) {
		return isset($this->args->$key) ? $this->args->$key : null;
	}
	
	/** Get the HTML input tag for this field
	 * @return string
	 */
	public function getHTMLInputAttr() {
		return $this->getType()->getHTMLInputAttr($this);
	}
	
// 	public function getName() {
// 		return $this->name;
// 	}
	
// 	public function isWritable() {
// 		return $this->writable;
// 	}

	// 	public function isNullable() {
	// 		return $this->nullable;
	// 	}
	
	/** Get the type of the field
	 * @param TypeDescriptor $type Optional output parameter for the type
	 * @return TypeDescriptor
	 */
	public function getType(&$type=null) {
		return EntityDescriptor::getType($this->type, $type);
	}
	
	/** Get the default value (if this field is NULL)
	 * @return string|integer
	 */
	public function getDefault() {
		if( is_object($this->default) ) {
			$this->default = call_user_func_array($this->default->type, (array) $this->default->args);
		} else
		if( defined($this->default) ) {
			return constant($this->default);
		}
		return $this->default;
	}
	
	/** 
	 * Parse field type configuration from file string
	 * @param	$field string
	 * @param	$desc string|string[]
	 * @return	FieldDescriptor The parsed field descriptor
	 */
	public static function parseType($fieldName, $desc) {
		if( is_array($desc) ) {
			$typeDesc				= $desc['type'];
		} else {
			$typeDesc				= $desc;
			$desc					= array();
		}
// 		debug('parseType - '.$field, $typeDesc);
		$parse					= EntityDescriptor::parseType($fieldName, $typeDesc);
		/* Field : String name, TypeDescriptor type, Array args, default, writable, nullable */
		$field					= new static($fieldName, $parse->type);
		$TYPE					= $field->getType();
		$field->args			= $TYPE->parseArgs($parse->args);
		$field->default			= $parse->default;
		// Type's default
		$field->writable		= $TYPE->isWritable();
		$field->nullable		= $TYPE->isNullable();
		// Default if no type's default
		if( !isset($field->writable) ) { $field->writable = true; }
		if( !isset($field->nullable) ) { $field->nullable = false; }
		// 			text('Type nullable: '.b($field->nullable));
		// Field flags
		if( isset($desc['writable']) ) {
			$field->writable = !empty($desc['writable']);
		} else if( $field->writable ) {
			$field->writable = !in_array('readonly', $parse->flags);
		} else {
			$field->writable = in_array('writable', $parse->flags);
		}
		if( isset($desc['nullable']) ) {
			$field->nullable = !empty($desc['nullable']);
		} else if( $field->nullable ) {
			$field->nullable = !in_array('notnull', $parse->flags);
		} else {
			$field->nullable = in_array('nullable', $parse->flags);
		}
// 		debug('$field', $field);
		return $field;
	}
	
	public static function buildIDField($name) {
		$field					= new static($name, 'ref');
		$TYPE					= $field->getType();
		$field->args			= $TYPE->parseArgs(array());
		$field->default			= null;
		$field->writable		= false;
		$field->nullable		= false;
		return $field;
	}
}
