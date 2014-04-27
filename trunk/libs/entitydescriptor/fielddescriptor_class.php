<?php

class FieldDescriptor {

	public $name;
	public $type;
	public $args;
	public $default;
	public $writable;
	public $nullable;
	/* Field : String name, TypeDescriptor type, Array args, default, writable, nullable */
	
	public function __construct($name, $type) {
		$this->name	= $name;
		$this->type	= $type;
	}
	
	public function __toString() {
		return $this->name;
	}
	
	// Getter
	public function __get($key) {
		return $this->$key;
	}
	
	public function arg($key) {
		return $this->args->$key;
	}
	
	public function getHTMLInputAttr() {
// 		debug('Type ', $this->getType());
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
	
	/**
	 * @param TypeDescriptor $type
	 * @return TypeDescriptor
	 */
	public function getType(&$type=null) {
		return EntityDescriptor::getType($this->type, $type);
	}
	
	public function getDefault() {
		if( is_object($this->default) ) {
			$this->default = call_user_func_array($this->default->type, (array) $this->default->args);
		}
		return $this->default;
	}
	
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
