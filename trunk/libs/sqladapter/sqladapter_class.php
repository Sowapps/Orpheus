<?php
//! The main SQL Adapter class
/*!
	This class is the mother sql adapter inherited for specific DBMS.
*/
abstract class SQLAdapter {
	
	protected static $Adapters = array();
	
	protected static $IDFIELD;
	
	protected $instance;
	
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

	//! Constructor
	/*!
	 * \param $Instance The instance to use to execute the future queries.
	*/
	public function __construct($Instance) {
		$this->instance = $Instance;
	}

	//! The function to use to query the DB server using db instance of this object
	/*!
	 * \param $Query The query to execute.
	 * \param $Fetch See PDO constants above. Optional, default is PDOQUERY.
	 * \return The result of pdo_query()
	 * \sa pdo_query()
	*/
	public function query($Query, $Fetch=PDOQUERY) {
		return pdo_query($Query, $Fetch, $this->instance);
	}
	
	//! The function to use for SELECT queries
	/*!
	 * \param $options The options used to build the query.
	 * \return Mixed return, depending on the adapter.
	 * 
	 * It parses the query from an array to a SELECT query.
	*/
	public abstract function select(array $options=array());
	
	//! The function to use for UPDATE queries
	/*!
	 * \param $options The options used to build the query.
	 * \return The number of affected rows.
	 * 
	 * It parses the query from an array to a UPDATE query.
	*/
	public abstract function update(array $options=array());
	
	//! The function to use for DELETE queries
	/*!
	 * \param $options The options used to build the query.
	 * \return The number of deleted rows.
	 * 
	 * It parses the query from an array to a DELETE query.
	*/
	public abstract function delete(array $options=array());
	
	//! The function to use for INSERT queries
	/*!
	 * \param $options The options used to build the query.
	 * \return The number of inserted rows.
	 * 
	 * It parses the query from an array to a INSERT query.
	*/
	public abstract function insert(array $options=array());
	
	//! The function to get the last inserted ID
	/*!
	 * \param $table The table to get the last inserted id.
	 * \param $idfield The field id name.
	 * \return The last inserted id value.
	 * 
	 * It requires a successful call of insert() !
	*/
	public function lastID($table, $idfield) {
		return pdo_lastInsertId($this->instance);
	}
	
	
	//! The static function to use for SELECT queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \sa select()
	*/
	public static function doSelect(array $options=array(), $Instance=null) {
		self::prepare($Instance);
		return self::$Adapters[$Instance]->select($options);
	}
	
	//! The static function to use for UPDATE queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \sa update()
	*/
	public static function doUpdate(array $options=array(), $Instance=null) {
		self::prepare($Instance);
		return self::$Adapters[$Instance]->update($options);
	}
	
	//! The static function to use for DELETE queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \sa SQLAdapter::delete()
	*/
	public static function doDelete(array $options=array(), $Instance=null) {
		self::prepare($Instance);
		return self::$Adapters[$Instance]->delete($options);
	}
	
	//! The static function to use for INSERT queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \sa SQLAdapter::insert()
	*/
	public static function doInsert(array $options=array(), $Instance=null) {
		self::prepare($Instance);
		return self::$Adapters[$Instance]->insert($options);
	}
	
	//! The static function to use to get last isnert id in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $idfield The field id name.
	 * \param $Instance The db instance used to send the query.
	 * \sa SQLAdapter::lastID()
	*/
	public static function doLastID($table, $idfield='id', $Instance=null) {
		self::prepare($Instance);
		return self::$Adapters[$Instance]->lastID($table, $idfield);
	}

	//! The static function to prepare an adapter for the given instance
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \sa SQLAdapter::lastID()
	*/
	public static function prepare($instance=null) {
		if( isset(self::$Adapters[$instance]) ) {
			return;
		}
		global $DBS;
		$Instance = ensure_pdoinstance($instance);
		if( empty($DBS[$Instance]) ) {
			throw new Exception("Adapter unable to connect to the database.");
		}
		$adapterClass = 'SQLAdapter_'.$DBS[$Instance]['driver'];
		// $instance is prepare() name of instance and $Instance is the real one
		self::$Adapters[$instance] = new $adapterClass($Instance);
		if( empty(self::$Adapters[$instance]) ) {
			// null means default but default is not always 'default'
			self::$Adapters[$Instance] = &self::$Adapters[$instance];
		}
	}

	//! The static function to quote
	/*!
	 * \param $String The string to quote.
	 * \return The quoted string.
	 * 
	 * Add slashes before simple quotes in $String and surrounds it with simple quotes and .
	 * Keep in mind this function does not really protect your DB server, especially against SQL injections.
	*/
	public static function quote($String) {
		return "'".str_replace(array("\\", "'"), array("\\\\", "\'"), "$String")."'";
	}
}

includeDir(LIBSPATH.'sqladapter/');
SQLAdapter::prepare();//Object destruction can not load libs and load DB config.