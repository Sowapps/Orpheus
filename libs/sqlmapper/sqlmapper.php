<?php
//! The main SQL Mapper class
/*!
	This class is the mother sql mapper inherited for specific DBMS.
*/
abstract class SQLMapper {
	
	protected static $IDFIELD;
	
	//! Defaults for selecting
	protected static $selectDefaults = array();
	
	//! Defaults for updating
	protected static $updateDefaults = array();
	
	//! Defaults for deleting
	protected static $deleteDefaults = array();
	
	//! Defaults for inserting
	protected static $insertDefaults = array();
	
	//List of outputs for getting list
	const ARR_OBJECTS	= 1;//!< Array of objects
	const ARR_ASSOC		= 2;//!< Associative array
	const STATEMENT		= 3;//!< SQL Statement
	const SQLQUERY		= 4;//!< Query String
	
	
	//! The function to use for SELECT queries
	/*!
		\param $options The options used to build the query.
		\return Mixed return, depending on the mapper.
		
		It parses the query from an array to a SELECT query.
	*/
	public abstract static function select(array $options=array());
	
	//! The function to use for UPDATE queries
	/*!
		\param $options The options used to build the query.
		\return The number of affected rows.
		
		It parses the query from an array to a UPDATE query.
	*/
	public abstract static function update(array $options=array());
	
	//! The function to use for DELETE queries
	/*!
		\param $options The options used to build the query.
		\return The number of deleted rows.
		
		It parses the query from an array to a DELETE query.
	*/
	public abstract static function delete(array $options=array());
	
	//! The function to use for INSERT queries
	/*!
		\param $options The options used to build the query.
		\return The number of inserted rows.
		
		It parses the query from an array to a INSERT query.
	*/
	public abstract static function insert(array $options=array());
}

includeDir();