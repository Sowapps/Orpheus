<?php
use Orpheus\Config\Config;
use Orpheus\Config\IniConfig;

/** The main SQL Adapter class

	This class is the mother sql adapter inherited for specific DBMS.
 */
abstract class SQLAdapter {
	
	
	protected $IDFIELD = 'id';
	
	protected $pdo;
	
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

	/**
	 * Constructor
	 * 
	 * @param mixed $config Instance config to use, maybe a config name, a config array or a PDO instance
	 */
	public function __construct($name, $config) {
		
		if( is_object($name) ) {
			
			// Deprecated, retrocompatibility
			// TODO Remove this case
			$this->pdo = $name;
			
			// If is array ?
		} else {
			
			// Use make()
// 			if( is_array($config) ) {
				
// 				// Nothing, its ok
				
// 			} else {
				
// 				$configs = static::listConfig();
				
// 				if( !isset($configs[$config]) ) {
// 					throw new SQLException('Database configuration with name "'.$config.'" not found.', 'Loading configuration');
// 				}
				
// 				$config = $configs[$config];
				
// 			}
			
			$this->connect($config + static::getDefaults());
			static::registerInstance($name, $this);
		}
	}

	protected static $instances = array();
	
	protected static function registerInstance($name, $adapter) {
		static::$instances[$name] = $adapter;
	}
	
	public static function getInstance($name=null) {
		if( !$name ) {
			$name = 'default';
		}
		if( !isset(static::$instances[$name]) ) {
			static::make($name);
		}
		return static::$instances[$name];
	}
	
	protected static $adapters = array();
	
	public static function registerAdapter($driver, $class) {
		static::$adapters[$driver] = $class;
	}
	
	public static function make($name='default') {
		$configs = static::listConfig();
		
		if( !isset($configs[$name]) ) {
			throw new SQLException('Database configuration with name "'.$name.'" not found.', 'Loading configuration');
		}
		
		$config = $configs[$name];
		
		if( empty($config['driver']) ) {
			throw new SQLException('Database configuration with name "'.$name.'" has no driver property.', 'Loading configuration');
		}
		
		if( empty(static::$adapters[$config['driver']]) ) {
			throw new SQLException('Database configuration with name "'.$name.'" requires an unknown driver "'.$config['driver'].'".', 'Loading configuration');
		}
		
		return new static::$adapters[$config['driver']]($name, $config);
		
	}
	
	protected function formatFieldList($fields) {
		if( !is_array($fields) ) {
			return $fields;
		}
		$string	= '';
		foreach( $fields as $key => $value ) {
			$string .= ($string ? ', ' : '').$this->escapeIdentifier($key).'='.$this->formatValue($value);
		}
		return $string;
	}
	
	protected static function getDefaults() {
		return array(
			'host'		=> '127.0.0.1',
			'user'		=> 'root',
			'passwd'	=> ''
		);
	}
	
	protected abstract function connect(array $config);
	
	protected static $configs;
	
	protected static function listConfig() {
		if( static::$configs !== null ) {
			return static::$configs;
		}
		$cache	= new APCache('sqladapter', 'db_configs', 2*3600);
		if( !$cache->get($configs) ) {
			$fileCconfig = IniConfig::build(DBCONF, true, false)->all;
			$configs	= array();
			foreach( $fileCconfig as $key => $value ) {
				if( is_array($value) ) {
					// Instance config
					$configs[$key]	= $value;
					
				} else {
					// Instance config property
					if( !isset($configs['default']) ) {
						$configs['default']	= array();
					}
					$configs['default'][$key] = $value;
				}
			}
			$cache->set($configs);
		}
		return static::$configs = $configs;
	}

	/**
	 * The function to use to query the DB server using db instance of this object
	 * 
	 * @param $Query The query to execute.
	 * @param $Fetch See PDO constants above. Optional, default is PDOQUERY.
	 * @return The result of pdo_query()
	 * @see pdo_query()
	 */
	public function query($Query, $Fetch=PDOQUERY) {
		return pdo_query($Query, $Fetch, $this->pdo);
// 		debug('SQL Query => '.$Query);
// 		try {
// 		} catch ( Exception $e ) {
// 			return 0;
// 		}
	}
	
	/**
	 * The function to use for SELECT queries
	 * 
	 * @param $options The options used to build the query.
	 * @return Mixed return, depending on the adapter.
	 * 
	 * It parses the query from an array to a SELECT query.
	 */
	public abstract function select(array $options=array());
	
	/**
	 * The function to use for UPDATE queries
	 * 
	 * @param $options The options used to build the query.
	 * @return The number of affected rows.
	 * 
	 * It parses the query from an array to a UPDATE query.
	 */
	public abstract function update(array $options=array());
	
	/**
	 * The function to use for DELETE queries
	 * 
	 * @param $options The options used to build the query.
	 * @return The number of deleted rows.
	 * 
	 * It parses the query from an array to a DELETE query.
	 */
	public abstract function delete(array $options=array());
	
	/**
	 * The function to use for INSERT queries
	 * 
	 * @param $options The options used to build the query.
	 * @return The number of inserted rows.
	 * 
	 * It parses the query from an array to a INSERT query.
	 */
	public abstract function insert(array $options=array());

	/**
	 * Escape SQL identifiers
	 * 
	 * @param string $Identifier The identifier to escape.
	 * @return The escaped identifier.
	 * 
	 * Escapes the given string as an SQL identifier.
	 */
	public function escapeIdentifier($Identifier) {
		return '"'.$Identifier.'"';
	}

	/** Formats SQL string
	 * @param string $str The string to format.
	 * @return The formatted string.
	 * 
	 * Formats the given string as an SQL string.
	 */
	public function formatString($str) {
		return "'".str_replace("'", "''", "$str")."'";
// 		return is_null($String) ? 'NULL' : "'".str_replace(array("\\", "'"), array("\\\\", "\'"), "$String")."'";
	}

	/** Formats SQL value
	 * @param string $value The value to format.
	 * @return The formatted value.
	 * 
	 * Formats the given value to the matching SQL type.
	 * If the value is a float, we make french decimal compatible with SQL.
	 * If null, we use the NULL value, else we consider it as a string value.
	 */
	public function formatValue($value) {
		return $this->escapeValue($value);
	}

	/**
	 * Escape SQL value
	 * @param string $value The value to format.
	 * @return The formatted value.
	 * @see formatValue()
	 * 
	 * Formats the given value to the matching SQL type.
	 * If the value is a float, we make french decimal compatible with SQL.
	 * If null, we use the NULL value, else we consider it as a string value.
	 */
	public function escapeValue($value) {
		return $value === null ? 'NULL' : $this->formatString($value);
	}
	
	public function formatValueList(array $list) {
		$str = '';
		foreach( $list as $i => $v ) {
			$str .= ($i ? ',' : '').$this->formatValue($v);
		}
		return $str;
	}
	
	/**
	 * Get the last inserted ID
	 * 
	 * @param $table The table to get the last inserted id.
	 * @return The last inserted id value.
	 * 
	 * It requires a successful call of insert() !
	 */
	public function lastID($table) {
		return $this->pdo->lastInsertId();
// 		return pdo_lastInsertId($this->pdo);
	}

	/** Changes the IDFIELD
	 * @param $field The new ID field.
	 * 
	 * Sets the IDFIELD value to $field
	 */
	public function setIDField($field) {
		if( $field !== null ) {
			$this->IDFIELD = $field;
		}
	}
	
	/**
	 * Start a transaction with DB (require InnoDB)
	 * 
	 * @param string $transactionID The ID of the transaction
	 * @param string $instance The db instance used to send the query
	 * @param string $IDField The ID field of the table
	*/
// 	public static function startTransaction($transactionID, $instance=null, $IDField=null) {
// 		self::prepareQuery($options, $instance, $IDField);
// 		if( !$transactionID ) {
// 			$transactionID	= 'default';
// 		}
//		// Store active $transactionID
// 		$pdoInstance	= pdo_instance($instance);
// 		self::$instances[$instance]->
// 	}
	
	/** The static function to use for SELECT queries in global context

	 * @param array $options The options used to build the query.
	 * @param string $instance The db instance used to send the query.
	 * @param string $IDField The ID field of the table.
	 * @sa select()
	*/
	public static function doSelect(array $options=array(), $instance=null, $IDField=null) {
		self::prepareQuery($options, $instance, $IDField);
		return self::$instances[$instance]->select($options);
	}
	
	/** The static function to use for UPDATE queries in global context

	 * @param $options The options used to build the query.
	 * @param $instance The db instance used to send the query.
	 * @param $IDField The ID field of the table.
	 * @sa update()
	*/
	public static function doUpdate(array $options=array(), $instance=null, $IDField=null) {
		self::prepareQuery($options, $instance, $IDField);
$instancesself::$instances[$instance]->update($options);
	}
	
	/** The static function to use for DELETE queries in global context

	 * @param $options The options used to build the query.
	 * @param $instance The db instance used to send the query.
	 * @param $IDField The ID field of the table.
	 * @sa SQLAdapter::delete()
	*/
	public static function doDelete(array $options=array(), $instance=null, $IDField=null) {
		self::prepareQuery($options, $instance, $IDField);
		return self::$instances[$instance]->delete($options);
	}
	
	/** The static function to use for INSERT queries in global context

	 * @param $options The options used to build the query.
	 * @param $instance The db instance used to send the query.
	 * @param $IDField The ID field of the table.
	 * @sa SQLAdapter::insert()
	*/
	public static function doInsert(array $options=array(), $instance=null, $IDField=null) {
		self::prepareQuery($options, $instance, $IDField);
		return self::$instances[$instance]->insert($options);
	}
	
	/** The static function to use to get last isnert id in global context

	 * @param $table The table to get the last ID. Some DBMS ignore it.
	 * @param $IDField The field id name.
	 * @param $instance The db instance used to send the query.
	 * @sa SQLAdapter::lastID()
	*/
	public static function doLastID($table, $IDField='id', $instance=null) {
		$options=array();
		self::prepareQuery($options, $instance, $IDField);
		return self::$instances[$instance]->lastID($table);
	}

	/** Escapes SQL identifiers

	 * @param $Identifier The identifier to escape.
	 * @param $instance The db instance used to send the query.
	 * @return The escaped identifier.
	 * @sa SQLAdapter::escapeIdentifier()
	 * 
	 * Escapes the given string as an SQL identifier.
	*/
	public static function doEscapeIdentifier($Identifier, $instance=null) {
		self::prepareInstance($instance);
		return self::$instances[$instance]->escapeIdentifier($Identifier);
	}

	/** Escapes SQL identifiers

	 * @param $String The value to format.
	 * @param $instance The db instance used to send the query.
	 * @return The formatted value.
	 * @sa SQLAdapter::formatString()
	 * 
	 * Formats the given value to the matching SQL type.
	 * If the value is a float, we make french decimal compatible with SQL.
	 * If null, we use the NULL value, else we consider it as a string value.
	*/
	public static function doFormatString($String, $instance=null) {
		self::prepareInstance($instance);
		return self::$instances[$instance]->formatString($String);
	}

	/** The static function to quote

	 * @param string $value The string to quote.
	 * @param $instance The db instance used to send the query.
	 * @return The quoted string.
	 * @sa SQLAdapter::formatValue()
	 * 
	 * Add slashes before simple quotes in $String and surrounds it with simple quotes and .
	 * Keep in mind this function does not really protect your DB server, especially against SQL injections.
	*/
	public static function doFormatValue($value, $instance=null) {
		self::prepareInstance($instance);
		return self::$instances[$instance]->formatValue($value);
// 		if( is_float($value) ) {
// 			return strtr($value, ',', '.');
// 		}
// 		return is_null($String) ? 'NULL' : "'".str_replace(array("\\", "'"), array("\\\\", "\'"), "$String")."'";
	}

	/** The static function to prepare the query for the given instance

	 * @param $options The options used to build the query.
	 * @param $instance The db instance used to send the query.
	 * @param $IDField The ID field of the table.
	*/
	public static function prepareQuery(array &$options=array(), &$instance=null, $IDField=null) {
		self::prepareInstance($instance);
		self::$instances[$instance]->setIDField($IDField);
		if( !empty($options) && !empty($options['output']) && $options['output'] == SQLAdapter::ARR_FIRST ) {
			$options['number'] = 1;
		}
	}

	/** The static function to prepareInstance an adapter for the given instance

	 * @param $instance The db instance name to prepareInstance.
	*/
	public static function prepareInstance(&$instance=null) {
		if( isset(self::$instances[$instance]) ) { return; }
		global $DBS;
		$instance = ensure_pdoinstance($instance);
		if( empty($DBS[$instance]) ) {
			throw new Exception("Adapter unable to connect to the database.");
		}
		$adapterClass = 'SQLAdapter_'.$DBS[$instance]['driver'];
		// $instance is prepareInstance() name of instance and $instance is the real one
		self::$instances[$instance] = new $adapterClass($instance, $DBS[$instance]);
// 		self::$instances[$instance] = new $adapterClass($instance);
		if( empty(self::$instances[$instance]) ) {
			// null means default but default is not always 'default'
			self::$instances[$instance] = &self::$instances[$instance];
		}
	}
}

includePath(LIBSDIR.'sqladapter/');
// SQLAdapter::prepareInstance();//Object destruction can not load libs and load DB config.
