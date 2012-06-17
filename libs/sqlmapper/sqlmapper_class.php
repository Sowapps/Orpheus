<?php
//! The main SQL Mapper class
/*!
	This class is the mother sql mapper inherited for specific DBMS.
*/
abstract class SQLMapper {
	
	protected static $Mapper;
	
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
	const NUMBER		= 5;//!< Number
	
	
	//! The static function to use for SELECT queries in global context.
	/*!
	 	\sa select()
	*/
	public static function doSelect(array $options=array()) {
		self::prepare();
		return self::$Mapper->select($options);
	}
	
	//! The static function to use for UPDATE queries in global context.
	/*!
		\sa update()
	*/
	public static function doUpdate(array $options=array()) {
		self::prepare();
		return self::$Mapper->update($options);
	}
	
	//! The static function to use for DELETE queries in global context.
	/*!
		\sa SQLMapper::delete()
	*/
	public static function doDelete(array $options=array()) {
		self::prepare();
		return self::$Mapper->delete($options);
	}
	
	//! The static function to use for INSERT queries in global context.
	/*!
		\sa SQLMapper::insert()
	*/
	public static function doInsert(array $options=array()) {
		self::prepare();
		return self::$Mapper->insert($options);
	}
	
	//! The static function to use to get last isnert id in global context.
	/*!
		\sa SQLMapper::lastID()
	*/
	public static function doLastID($table) {
		self::prepare();
		return self::$Mapper->lastID($table);
	}
	
	
	//! The function to use for SELECT queries
	/*!
		\param $options The options used to build the query.
		\return Mixed return, depending on the mapper.
		
		It parses the query from an array to a SELECT query.
	*/
	public abstract function select(array $options=array());
	
	//! The function to use for UPDATE queries
	/*!
		\param $options The options used to build the query.
		\return The number of affected rows.
		
		It parses the query from an array to a UPDATE query.
	*/
	public abstract function update(array $options=array());
	
	//! The function to use for DELETE queries
	/*!
		\param $options The options used to build the query.
		\return The number of deleted rows.
		
		It parses the query from an array to a DELETE query.
	*/
	public abstract function delete(array $options=array());
	
	//! The function to use for INSERT queries
	/*!
		\param $options The options used to build the query.
		\return The number of inserted rows.
		
		It parses the query from an array to a INSERT query.
	*/
	public abstract function insert(array $options=array());
	
	//! The function to get the last inserted ID
	/*!
		\param $table The table to get the last inserted id.
		\return The last inserted id value.
		
		It requires a successful call of insert() !
	*/
	public abstract function lastID($table);
	
	public static function prepare() {
		if( self::$Mapper != null ) {
			return;
		}
		global $DBS;
		$Instance = ensure_pdoinstance();
		if( empty($DBS[$Instance]) ) {
			throw new Exception("Mapper unable to connect to the database.");
		}
		$mapperClass = 'SQLMapper_'.$DBS[$Instance]['driver'];
		self::$Mapper = new $mapperClass();
		//$pdoInstances[$Instance]->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
	
	public static function quote($String) {
		return "'".addslashes($String)."'";
	}
}

includeDir(LIBSPATH.'sqlmapper/');