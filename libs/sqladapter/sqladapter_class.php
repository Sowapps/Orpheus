<?php
//! The main SQL Adapter class
/*!
	This class is the mother sql adapter inherited for specific DBMS.
*/
abstract class SQLAdapter {
	
	protected static $Adapters = array();
	
	protected $IDFIELD = 'id';
	
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
	const OBJECT		= 1;//!< Object
	const ARR_FIRST		= 2;//!< First element only (from ARR_ASSOC)
	const ARR_ASSOC		= 3;//!< Array of associative arrays
	const ARR_OBJECTS	= 4;//!< Array of objects
	const STATEMENT		= 5;//!< SQL Statement
	const SQLQUERY		= 6;//!< Query String
	const NUMBER		= 7;//!< Number

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
	 * \return The last inserted id value.
	 * 
	 * It requires a successful call of insert() !
	*/
	public function lastID($table) {
		return pdo_lastInsertId($this->instance);
	}

	//! Changes the IDFIELD
	/*!
	 * \param $field The new ID field.
	 * 
	 * Sets the IDFIELD value to $field
	*/
	public function setIDField($field) {
		if( !is_null($field) ) {
			$this->IDFIELD = $field;
		}
	}
	
	
	//! The static function to use for SELECT queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \param $IDField The ID field of the table.
	 * \sa select()
	*/
	public static function doSelect(array $options=array(), $Instance=null, $IDField=null) {
		self::prepareQuery($options, $Instance, $IDField);
		return self::$Adapters[$Instance]->select($options);
	}
	
	//! The static function to use for UPDATE queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \param $IDField The ID field of the table.
	 * \sa update()
	*/
	public static function doUpdate(array $options=array(), $Instance=null, $IDField=null) {
		self::prepareQuery($options, $Instance, $IDField);
		return self::$Adapters[$Instance]->update($options);
	}
	
	//! The static function to use for DELETE queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \param $IDField The ID field of the table.
	 * \sa SQLAdapter::delete()
	*/
	public static function doDelete(array $options=array(), $Instance=null, $IDField=null) {
		self::prepareQuery($options, $Instance, $IDField);
		return self::$Adapters[$Instance]->delete($options);
	}
	
	//! The static function to use for INSERT queries in global context
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \param $IDField The ID field of the table.
	 * \sa SQLAdapter::insert()
	*/
	public static function doInsert(array $options=array(), $Instance=null, $IDField=null) {
		self::prepareQuery($options, $Instance, $IDField);
		return self::$Adapters[$Instance]->insert($options);
	}
	
	//! The static function to use to get last isnert id in global context
	/*!
	 * \param $table The table to get the last ID. Some DBMS ignore it.
	 * \param $IDField The field id name.
	 * \param $Instance The db instance used to send the query.
	 * \sa SQLAdapter::lastID()
	*/
	public static function doLastID($table, $IDField='id', $Instance=null) {
		$options=array();
		self::prepareQuery($options, $Instance, $IDField);
		return self::$Adapters[$Instance]->lastID($table);
	}

	//! The static function to prepare the query for the given instance
	/*!
	 * \param $options The options used to build the query.
	 * \param $Instance The db instance used to send the query.
	 * \param $IDField The ID field of the table.
	*/
	public static function prepareQuery(array &$options=array(), &$Instance=null, $IDField=null) {
		self::prepareInstance($Instance);
		self::$Adapters[$Instance]->setIDField($IDField);
		if( !empty($options) && !empty($options['output']) && $options['output'] == SQLAdapter::ARR_FIRST ) {
			$options['number'] = 1;
		}
	}

	//! The static function to prepareInstance an adapter for the given instance
	/*!
	 * \param $instance The db instance name to prepareInstance.
	*/
	public static function prepareInstance(&$instance=null) {
		if( isset(self::$Adapters[$instance]) ) {
			return;
		}
		global $DBS;
		$Instance = ensure_pdoinstance($instance);
		if( empty($DBS[$Instance]) ) {
			throw new Exception("Adapter unable to connect to the database.");
		}
		$adapterClass = 'SQLAdapter_'.$DBS[$Instance]['driver'];
		// $instance is prepareInstance() name of instance and $Instance is the real one
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
		return is_null($String) ? 'NULL' : "'".str_replace(array("\\", "'"), array("\\\\", "\'"), "$String")."'";
	}
}

includeDir(LIBSPATH.'sqladapter/');
SQLAdapter::prepareInstance();//Object destruction can not load libs and load DB config.