<?php
//! The main SQL Adapter class
/*!
	This class is the mother sql adapter inherited for specific DBMS.
*/
abstract class SQLAdapter {
	
	protected static $Adapter;
	
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
		return self::$Adapter->select($options);
	}
	
	//! The static function to use for UPDATE queries in global context.
	/*!
		\sa update()
	*/
	public static function doUpdate(array $options=array()) {
		self::prepare();
		return self::$Adapter->update($options);
	}
	
	//! The static function to use for DELETE queries in global context.
	/*!
		\sa SQLAdapter::delete()
	*/
	public static function doDelete(array $options=array()) {
		self::prepare();
		return self::$Adapter->delete($options);
	}
	
	//! The static function to use for INSERT queries in global context.
	/*!
		\sa SQLAdapter::insert()
	*/
	public static function doInsert(array $options=array()) {
		self::prepare();
		return self::$Adapter->insert($options);
	}
	
	//! The static function to use to get last isnert id in global context.
	/*!
		\sa SQLAdapter::lastID()
	*/
	public static function doLastID($table) {
		self::prepare();
		return self::$Adapter->lastID($table);
	}
	
	
	//! The function to use for SELECT queries
	/*!
		\param $options The options used to build the query.
		\return Mixed return, depending on the adapter.
		
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
		\param $idfield The field id name.
		\return The last inserted id value.
		
		It requires a successful call of insert() !
	*/
	public abstract function lastID($table, $idfield);
	
	public static function prepare() {
		if( self::$Adapter != null ) {
			return;
		}
		global $DBS;
		$Instance = ensure_pdoinstance();
		if( empty($DBS[$Instance]) ) {
			throw new Exception("Adapter unable to connect to the database.");
		}
		$adapterClass = 'SQLAdapter_'.$DBS[$Instance]['driver'];
		self::$Adapter = new $adapterClass();
		//$pdoInstances[$Instance]->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
	
	public static function quote($String) {
		return "'".addslashes($String)."'";
	}
}

includeDir(LIBSPATH.'sqladapter/');
SQLAdapter::prepare();//Object destruction can not load libs and load DB config.