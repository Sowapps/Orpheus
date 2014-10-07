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
		}
		return $this->default;
	}
	
	/** 
	 * Parse field type configuration from file string
	 * @param	$field string
	 * @param	$desc string|string[]
	 * @return	FieldDescriptor The parsed field descriptor
	 */
	public static function parseType($field, $desc) {
		$typeDesc				= is_array($desc) ? $desc['type'] : $desc;
		$parse					= EntityDescriptor::parseType($typeDesc);
		/* Field : String name, TypeDescriptor type, Array args, default, writable, nullable */
		$Field					= new static($field, $parse->type);
		$TYPE					= $Field->getType();
		$Field->args			= $TYPE->parseArgs($parse->args);
		$Field->default			= $parse->default;
		// Type's default
		$Field->writable		= $TYPE->isWritable();
		$Field->nullable		= $TYPE->isNullable();
		// Default if no type's default
		if( !isset($Field->writable) ) { $Field->writable = true; }
		if( !isset($Field->nullable) ) { $Field->nullable = false; }
		// 			text('Type nullable: '.b($Field->nullable));
		// Field flags
		if( isset($desc['writable']) ) {
			$Field->writable = !empty($desc['writable']);
		} else if( $Field->writable ) {
			$Field->writable = !in_array('readonly', $parse->flags);
		} else {
			$Field->writable = in_array('writable', $parse->flags);
		}
		if( isset($desc['nullable']) ) {
			$Field->nullable = !empty($desc['nullable']);
		} else if( $Field->nullable ) {
			$Field->nullable = !in_array('notnull', $parse->flags);
		} else {
			$Field->nullable = in_array('nullable', $parse->flags);
		}
		return $Field;
	}
}
