<?php
//! The main SQL Mapper class
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
	const ARR_OBJECTS	= 1;//!< Array of objects
	const ARR_ASSOC		= 2;//!< Associative array
	const STATEMENT		= 3;//!< SQL Statement
	const SQLQUERY		= 4;//!< Query String
	
	public abstract static function select(array $options=array());
	public abstract static function update(array $options=array());
}

includeDir();