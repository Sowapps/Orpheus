<?php

//! The permanent entity class
/*!
 * A permanent entity class that combine a PermanentObject with EntityDescriptor's features.
*/
abstract class PermanentEntity extends PermanentObject {

	//Attributes
	protected static $table				= null;
	protected static $editableFields	= null;
	
	// Final class attributes, please inherits them
	/*
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	*/

	//! Initializes class - REQUIRED
	public static function init() {
		$ed					= EntityDescriptor::load(static::$table);
		static::$fields		= $ed->getFieldsName();
		static::$validator	= $ed;
		if( is_null(static::$domain) ) {
			static::$domain = static::$table;
		}
	}
	
	public static function getField($field) {
		static::$validator->getField($field);
	}
}
