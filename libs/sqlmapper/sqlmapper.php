<?php
//! The MYSQL Mapper for class
/*!
	This class is the mother sql mapper inherited for specific DBMS.
*/
abstract class SQLMapper {
	
	protected static $IDFIELD;
	
	//! Defaults for selecting
	protected static $selectDefaults = array();
	
	// Defaults for updating
	protected static $updateDefaults = array();
	
	//List of outputs for getting list
	const ARR_OBJECTS	= 1;
	const ARR_ASSOC		= 2;
	const STATEMENT		= 3;
	const SQLQUERY		= 4;
	
	public abstract static function select(array $options=array());
	public abstract static function update(array $options=array());
}

includeDir();