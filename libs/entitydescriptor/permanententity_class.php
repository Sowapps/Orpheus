<?php

/** The permanent entity class
 * A permanent entity class that combine a PermanentObject with EntityDescriptor's features.
 */
abstract class PermanentEntity extends PermanentObject {

	//Attributes
	protected static $table				= null;
	protected static $editableFields	= null;
	
	protected static $entityClasses		= array();
	
	// Final class attributes, please inherits them
	/*
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	*/

	/**
	 * Get unix timestamp from the create_date field
	 * @return int
	 * @warning You can not use this function if there is no create_date field
	 */
	public function getCreateTime() {
		return strtotime($this->create_date);
	}
	
	/**
	 * Validate field value from the validator using this entity
	 * @param string $field
	 * @param mixed $value
	 */
	public function validateValue($field, $value) {
		static::$validator->validateFieldValue($field, $value, null, $this);
	}
	
	/**
	 * Validate field value and set it.
	 * @param string $field
	 * @param mixed $value
	 */
	public function putValue($field, $value) {
		$this->validateValue($field, $value);
		$this->setValue($field, $value);
	}
	
	/**
	 * Try to load entity from an entity string and an id integer
	 * @param string $entity
	 * @param int $id
	 */
	public static function loadEntity($entity, $id) {
		return $entity::load($id);
	}

	/** Initializes class - REQUIRED
	 * Initializes entity class
	 * You must call this method after the class declaration
	 */
	public static function init($isFinal=true) {
// 		debug(static::getClass().'::init() ', debug_backtrace());
		if( static::$validator ) {
//			debug('static::$validator', static::$validator);
			throw new Exception('Class '.static::getClass().' with table '.static::$table.' is already initialized.');
		}
		if( static::$domain === NULL ) {
			static::$domain = static::$table;
		}
		if( $isFinal ) {
// 			debug('Entity descritor loading');
			$ed					= EntityDescriptor::load(static::$table, static::getClass());
			static::$fields		= $ed->getFieldsName();
			static::$validator	= $ed;
			static::$entityClasses[static::$table]	= static::getClass();
		}
	}
	
	public static function getEntityObject($objType, $objID=null) {
		if( is_object($objType) ) {
			$objID		= $objType->entity_id;
			$objType	= $objType->entity_type;
		}
		$class	= isset(static::$entityClasses[$objType]) ? static::$entityClasses[$objType] : $objType;
		return $class::load($objID);
	}
	
	/** 
	 * Helper method to get whereclause string from an entity
	 * @param string $prefix The prefix for fields, e.g "table." (with dot)
	 * @return string
	 * 
	 * Helper method to get whereclause string from an entity.
	 * The related entity should have entity_type and entity_id fields.
	 */
	public function getEntityWhereclause($prefix='') {
		return $prefix.'entity_type LIKE '.static::formatValue(static::getEntity()).' AND '.$prefix.'entity_id='.$this->id();
	}
	
	/**
	 * Get field descriptor from field name
	 * @param string $field
	 * @return FieldDescriptor
	 */
	public static function getField($field) {
		return static::$validator->getField($field);
	}

	/**
	 * Get this entity name
	 * @return string
	 */
	public static function getEntity() {
		return static::getTable();
	}
}
