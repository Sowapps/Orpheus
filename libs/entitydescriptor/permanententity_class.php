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

	public function getCreateTime() {
		return strtotime($this->create_date);
	}
	
	public function validateValue($field, $value) {
		static::$validator->validateFieldValue($field, $value, null, $this);
	}
	
	public function putValue($field, $value) {
		$this->validateValue($field, $value);
		$this->setValue($field, $value);
	}
	
	public function genEntityWhereclause() {
		return "entity_type LIKE '{$this->getEntity()}' AND entity_id={$this->id()}";
	}
	
	public static function loadEntity($entity, $id) {
		return $entity::load($id);
	}

	//! Initializes class - REQUIRED
	public static function init() {
		$ed					= EntityDescriptor::load(static::$table, static::getClass());
		static::$fields		= $ed->getFieldsName();
		static::$validator	= $ed;
		if( static::$domain === NULL ) {
			static::$domain = static::$table;
		}
	}
	
	public static function getField($field) {
		return static::$validator->getField($field);
	}

	public static function getEntity() {
		return static::getTable();
	}
}
