<?php
using('sqladapter.SQLAdapter');

/** The permanent object class
 * Manage a permanent object using the SQL Adapter.
 */
abstract class PermanentObject {
	
	//Attributes
	protected static $IDFIELD			= 'id';
	protected static $instances			= array();
	
	protected static $table				= null;
	protected static $DBInstance		= null;
	// Contains all fields
	protected static $fields			= array();
	// Contains fields editables by users
	protected static $editableFields	= null;
	// Contains the validator. The default one is an array system.
	protected static $validator			= array();//! See checkUserInput()
	// Contains the domain. Used as default UserException domain.
	protected static $domain			= null;
	
	protected $modFields	= array();
	protected $data			= array();
	protected $isDeleted	= false;
	
	/** Internal static initialization
	 * 
	 */
	public static function selfInit() {
		static::$fields = array(static::$IDFIELD);
	}
	
	/**
	 * Return the object as array
	 * @param array $array
	 * @return array The resulting array
	 */
	public static function set2Array(array $array) {
		foreach( $array as &$value ) {
			$value	= $value->getValue();
		}
		return $array;
	}
	
	/**
	 * Insert this object in the given array using its ID as key
	 * @param array $array
	 */
	public function setTo(array &$array) {
		$array[$this->id()]	= $this;
	}
	
	// *** OVERRIDDEN METHODS ***
	
	/** Constructor
	 * @param $data An array of the object's data to construct
	 */
	public function __construct(array $data) {
		foreach( static::$fields as $fieldname ) {
			// We condiser null as a valid value.
			if( !array_key_exists($fieldname, $data) ) {
				if( !in_array($fieldname, static::$fields) ) {
					throw new FieldNotFoundException($fieldname, static::getClass());
				}
				// Data not found but should be, this object is out of date
				$this->reload();
				// Data not in DB, this class is invalid
				if( !array_key_exists($fieldname, $data) ) {
					throw new Exception('The class '.static::getClass().' is out of date, the field "'.$fieldname.'" is unknown in database.');
				}
			}
			$this->data[$fieldname] = $data[$fieldname];
		}
		$this->modFields = array();
	}
	
	/** Destructor
	 * If something was modified, it saves the new data.
	*/
	public function __destruct() {
		if( !empty($this->modFields) ) {
			try {
				$this->save();
			} catch(Exception $e) {
				// Can be destructed outside of the matrix
				log_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__destruct(): Saving');
			}
		}
	}
	
	/** Magic getter
	 * @param string $name Name of the property to get
	 * @return The value of field $name
	 * 
	 * Gets the value of field $name.
	 * 'all' returns all fields.
	*/
	public function __get($name) {
		return $this->getValue($name == 'all' ? null : $name);
	}
	
	/** Magic setter
	 * @param $name Name of the property to set
	 * @param $value New value of the property
	 * 
	 * Sets the value of field $name.
	*/
	public function __set($name, $value) {
		$this->setValue($name, $value);
	}
	
	/** Magic isset
	 * @param $name Name of the property to check is set
	 * 
	 * Checks if the field $name is set.
	*/
	public function __isset($name) {
        return isset($this->data[$name]);
	}
	
	/** Magic toString
	 * @return The string value of the object.
	 * 
	 * The object's value when casting to string.
	*/
	public function __toString() {
		try {
			return static::getClass().'#'.$this->{static::$IDFIELD};
// 			return '#'.$this->{static::$IDFIELD}.' ('.get_class($this).')';
		} catch( Exception $e ) {
			log_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__toString()', false);
		}
	}
	
	// *** DEV METHODS ***
	
	/** Gets this permanent object's ID
	 * @return The id of this object.
	 * 
	 * Gets this object ID according to the IDFIELD attribute.
	 */
	public function id() {
		return $this->getValue(static::$IDFIELD);
	}
	
	/** Gets this permanent object's unique ID
	 * @return The uid of this object.
	 * 
	 * Gets this object ID according to the table and id.
	 */
	public function uid() {
		return $this->getTable().'#'.$this->id();
	}
	
	/** 
	 * Update this permanent object from input data array
	 * @param $uInputData The input data we will check and extract, used by children.
	 * @param $fields The array of fields to check. It never should be null using a validator class, it will be a security breach.
	 * @param $noEmptyWarning True to do not report warning for empty data (instead return 0). Default value is true.
	 * @param $errCount Output parameter for the number of occurred errors validating fields.
	 * @param $successCount Output parameter for the number of successes updating fields.
	 * @return 1 in case of success, else 0.
	 * @overrideit
	 * @see runForUpdate()
	 * 
	 * This method require to be overridden but it still be called too by the child classes.
	 * Here $uInputData is not used, it is reserved for child classes.
	 * $data must contain a filled array of new data.
	 * This method update the EDIT event log.
	 * Before saving, runForUpdate() is called to let child classes to run custom instructions.
	 * Parameter $fields is really useful to allow partial modification only (against form hack).
	 */
	public function update($uInputData, $fields, $noEmptyWarning=true, &$errCount=0, &$successCount=0) {
		$data	= static::checkUserInput($uInputData, $fields, $this, $errCount);
		// Don't care about some errors, other fields should be updated.
		$found	= 0;
		foreach( $data as $fieldname => $fieldvalue ) {
			if( in_array($fieldname, static::$fields) ) {
				$found	= 1;
				continue;
			}
		}
// 		debug('$data', $data);
		try {
			if( !$found ) {
// 			if( empty($data) ) {
				if( !$noEmptyWarning ) {
					reportWarning('updateEmptyData', static::getDomain());
				}
				return 0;
// 				static::throwException('updateEmptyData');
			}
			static::checkForObject(static::completeFields($data), $this);
		} catch(UserException $e) { reportError($e, static::getDomain()); return 0; }
		
		$oldData	= $this->all;
		foreach($data as $fieldname => $fieldvalue) {
			if( in_array($fieldname, static::$fields) && in_array($fieldname, $fields) ) {
// 			if( static::isFieldEditable($fieldname) ) {
// 				text('Set value of '.$fieldname.' to '.($fieldvalue === NULL ? 'NULL' : (is_bool($fieldvalue) ? b($fieldvalue) : $fieldvalue)));
				$this->setValue($fieldname, $fieldvalue);
				$successCount++;
			}
		}
		static::logEvent('edit');
		static::logEvent('update');
		if( $r = $this->save() ) {
			$this->runForUpdate($data, $oldData);
		}
		return $r;
	}
	
	/** Runs for Update
	 * @param $data the new data
	 * @param $oldData the old data
	 * @see update()
	 * 
	 * This function is called by update() before saving new data.
	 * $data contains only edited data, excluding invalids and not changed ones.
	 * In the base class, this method does nothing.
	*/
	public function runForUpdate($data, $oldData) { }
	
	/** Saves this permanent object
	 * @return 1 in case of success, else 0
	 * 
	 * If some fields was modified, it saves these fields using the SQL Adapter.
	*/
	public function save() {
		if( empty($this->modFields) || $this->isDeleted() ) {
			return 0;
		}
		$updQ = '';
		foreach($this->modFields as $fieldname) {
			if( $fieldname != static::$IDFIELD ) {
				$updQ .= ( (!empty($updQ)) ? ', ' : '').static::escapeIdentifier($fieldname).'='.static::formatValue($this->$fieldname);
			}
		}
		$IDFIELD	= static::$IDFIELD;
		$this->modFields	= array();
		$options	= array(
			'what'	=> $updQ,
			'table'	=> static::$table,
			'where'	=> "{$IDFIELD}={$this->{$IDFIELD}}",
			'number'=> 1,
		);
		return SQLAdapter::doUpdate($options, static::$DBInstance, static::$IDFIELD);
	}
	
	public function remove() {
		if( $this->isDeleted() ) { return; }
		return static::delete($this->id());
	}
	public function free() {
		if( $this->remove() ) {
			$this->data			= null;
			$this->modFields	= null;
			return true;
		}
		return false;
	}
	
	/** Reloads fields from database
	 * @param $field The field to reload, default is null (all fields).
	 * 
	 * Updates the current object's fields from database.
	 * If $field is not set, it reloads only one field else all fields.
	 * Also it removes the reloaded fields from the modified ones list.
	*/
	public function reload($field=null) {
		$IDFIELD = static::$IDFIELD;
		$options = array('where' => $IDFIELD.'='.$this->$IDFIELD, 'output' => SQLAdapter::ARR_FIRST);
		if( !is_null($field) ) {
			if( !in_array($field, $fields) ) {
				throw new FieldNotFoundException($field, static::getClass());
			}
			$i = array_search($this->modFields);
			if( $i !== false ) {
				unset($this->modFields[$i]);
			}
			$options['what'] = $field;
		} else {
			$this->modFields = array();
		}
		$data = static::get($options);
		if( empty($data) ) {
			$this->markAsDeleted();
			return false;
		}
		if( !is_null($field) ) {
			$this->data[$field] = $data[$field];
		} else {
			$this->data = $data;
		}
		return true;
	}
	
	/** Marks the field as modified
	 * @param $field The field to mark as modified.
	 * 
	 * Adds the $field to the modified fields array.
	*/
	private function addModFields($field) {
		if( !in_array($field, $this->modFields) ) {
			$this->modFields[] = $field;
		}
	}
	
	/** Checks if this object is deleted
	 * @return True if this object is deleted.
	 * 
	 * Checks if this object is known as deleted.
	*/
	public function isDeleted() {
		return $this->isDeleted;
	}
	
	/** Checks if this object is valid
	 * @return True if this object is valid.
	 * 
	 * Checks if this object is not deleted.
	 * May be used for others cases.
	*/
	public function isValid() {
		return !$this->isDeleted();
	}
	
	/** Marks this object as deleted
	 * @see isDeleted()
	 * @warning Be sure what you are doing before calling this function (never out of this class' context).
	 * 
	 * Marks this object as deleted
	 */
	public function markAsDeleted() {
		$this->isDeleted = true;
	}
	
	/** Gets one value or all values.
	 * @param $key Name of the field to get.
	 * 
	 * Gets the value of field $key or all data values if $key is null.
	*/
	public function getValue($key=null) {
		if( empty($key) ) {
			return $this->data;
		}
		if( !array_key_exists($key, $this->data) ) {
// 			log_debug('Key "'.$key.'" not found in array : '.print_r($this->data, 1));
			throw new FieldNotFoundException($key, static::getClass());
		}
		return $this->data[$key];
	}
	
	/** Sets the value of a field
	 * @param $key Name of the field to set.
	 * @param $value New value of the field.
	 * 
	 * Sets the field $key with the new $value.
	*/
	public function setValue($key, $value) {
		if( !isset($key) ) {//$value
			throw new Exception("nullKey");
		} else
		if( !in_array($key, static::$fields) ) {
			throw new FieldNotFoundException($key, static::getClass());
		} else
		if( $key == static::$IDFIELD ) {
			throw new Exception("idNotEditable");
		} else
		if( empty($this->data[$key]) || $value !== $this->data[$key] ) {
			$this->addModFields($key);
			$this->data[$key] = $value;
		}
	}
	
	/** Verifies equality
	 * @param $o The object to compare.
	 * @return True if this object represents the same data, else False.
	 * 
	 * Compares the class and the ID field value of the 2 objects.
	*/
	public function equals($o) {
		return (get_class($this)==get_class($o) && $this->id()==$o->id());
	}
	
	/** Logs an event
	 * @param $event The event to log in this object.
	 * @param $time A specified time to use for logging event.
	 * @param $ipAdd A specified IP Adress to use for logging event.
	 * @see getLogEvent()
	 * 
	 * Logs an event to this object's data.
	*/
	public function logEvent($event, $time=null, $ipAdd=null) {
		$log = static::getLogEvent($event, $time, $ipAdd);
		if( in_array($event.'_time', static::$fields) ) {
			$this->setValue($event.'_time', $log[$event.'_time']);
		} else
		if( in_array($event.'_date', static::$fields) ) {
			$this->setValue($event.'_date', sqlDatetime($log[$event.'_time']));
		} else {
			return;
		}
		if( in_array($event.'_agent', static::$fields) && isset($_SERVER['HTTP_USER_AGENT']) ) {
			$this->setValue($event.'_agent', $_SERVER['HTTP_USER_AGENT']);
		}
		if( in_array($event.'_referer', static::$fields) && isset($_SERVER['HTTP_REFERER']) ) {
			$this->setValue($event.'_referer', $_SERVER['HTTP_REFERER']);
		}
		try {
			$this->setValue($event.'_ip', $log[$event.'_ip']);
		} catch(FieldNotFoundException $e) {}
	}
	
	// *** STATIC METHODS ***

	public static function object(&$obj) {
		return $obj = is_id($obj) ? static::load($obj) : $obj;
	}

	public static function isFieldEditable($fieldname) {
		if( $fieldname == static::$IDFIELD ) { return false; }
		if( !is_null(static::$editableFields) ) { return in_array($fieldname, static::$editableFields); }
		if( method_exists(static::$validator, 'isFieldEditable') ) { return in_array($fieldname, static::$editableFields); }
		return in_array($fieldname, static::$fields);
	}
	
	/** Load a permanent object
	 * @param	$in mixed|mixed[] The object ID to load or a valid array of the object's data
	 * @return	static The object
	 * @see static::get()
	 * 
	 * Loads the object with the ID $id or the array data.
	 * The return value is always a static object (no null, no array, no other object).
	 */
	public static function load($in) {
		if( empty($in) ) {
// 			static::throwException('invalidParameter_load_'.static::getClass());
			throw new Exception('invalidParameter_load');
		}
		if( is_object($in) && $in instanceof static ) {
			return $in;
		}
		$IDFIELD	= static::$IDFIELD;
		// If $in is an array, we trust him, as data of the object.
		if( is_array($in) ) {
			$id		= $in[$IDFIELD];
			$data	= $in;
		} else {
			$id		= $in;
		}
		if( !is_ID($id) ) {
			static::throwException('invalidID');
		}
		// Loading cached
		if( isset(static::$instances[static::getClass()][$id]) ) {
			return static::$instances[static::getClass()][$id];
		}
		// If we don't get the data, we request them.
		if( empty($data) ) {
			// Getting data
			$obj = static::get(array(
				'where'	=> $IDFIELD.'='.$id,
				'output'=> SQLAdapter::OBJECT,
			));
			// Ho no, we don't have the data, we can't load the object !
			if( empty($obj) ) {
				static::throwException('inexistantObject');
			}
		} else {
			$obj = new static($data);
		}
		// Saving cached
		return static::$instances[static::getClass()][$id] = $obj;
	}
	
	/** Deletes a permanent object
	 * @param $in The object ID to delete or the delete array.
	 * @return the number of deleted rows.
	 * 
	 * Deletes the object with the ID $id or according to the input array.
	 * It calls runForDeletion() only in case of $in is an ID.
	 * 
	 * The cached object is mark as deleted.
	 * Warning ! If several class instantiate the same db row, it only marks the one of the current class, others won't be marked as deleted, this can cause issues !
	 * We advise you to use only one class of one item row or to use it read-only.
	*/
	public static function delete($in) {
		if( is_array($in) ) {
			$in['table'] = static::$table;
			return SQLAdapter::doDelete($in, static::$DBInstance, static::$IDFIELD);
		}
		if( !is_id($in) ) {
			static::throwException('invalidID');
		}
		if( isset(static::$instances[static::getClass()][$in]) ) {
			/* @var $obj static */
			$obj = &static::$instances[static::getClass()][$in];
			if( $obj->isDeleted() ) { return 1; }
		}
		$IDFIELD	= static::$IDFIELD;
		$options	= array(
			'table'		=> static::$table,
			'number'	=> 1,
			'where'		=> "{$IDFIELD}={$in}",
		);
		$r = SQLAdapter::doDelete($options, static::$DBInstance, static::$IDFIELD);
		if( $r ) {
			if( isset($obj) ) {
				$obj->markAsDeleted();
			}
			static::runForDeletion($in);
		}
		return $r;
	}
	
	/**
	 * Removes deleted instances
	 */
	public static function clearDeletedInstances() {
		if( !isset(static::$instances[static::getClass()]) ) { return; }
		$instances	= &static::$instances[static::getClass()];
		foreach( $instances as $id => $obj ) {
			if( $obj->isDeleted() ) {
				unset($instances[$id]);
			}
		}
	}
	/**
	 * Removes deleted instances
	 */
	public static function clearInstances() {
		return static::clearDeletedInstances();
	}
	
	/**
	 * Removes all instances
	 */
	public static function clearAllInstances() {
		if( !isset(static::$instances[static::getClass()]) ) { return; }
		unset(static::$instances[static::getClass()]);
	}
	
	/** Escape identifier through instance
	 * @param $Identifier The identifier to escape
	 * @return The escaped identifier
	 * @see SQLAdapter::escapeIdentifier()
	*/
	public static function escapeIdentifier($Identifier) {
		return SQLAdapter::doEscapeIdentifier($Identifier, static::$DBInstance);
	}
	
	/** Escape value through instance
	 * @param $Value The value to format
	 * @return The formatted $Value
	 * @see SQLAdapter::formatValue()
	*/
	public static function formatValue($Value) {
		return SQLAdapter::doFormatValue($Value, static::$DBInstance);
	}
	
	/** Runs for Deletion
	 * @param $id The deleted object ID.
	 * @see delete()
	 * 
	 * This function is called by delete() after deleting the object $id.
	 * If you need to get the object before, prefer to inherit delete()
	 * In the base class, this method does nothing.
	*/
	public static function runForDeletion($id) { }
	
	/** Gets some permanent objects
	 * @param $options The options used to get the permanents object.
	 * @return An array of array containing object's data.
	 * @see SQLAdapter
	 * 
	 * Gets an objects' list using this class' table.
	 * Take care that output=SQLAdapter::ARR_OBJECTS and number=1 is different from output=SQLAdapter::OBJECT
	 * 
	*/
	/**
	 * @param array $options
	 * @return static|static[]
	 */
	public static function get($options=array()) {
		if( is_string($options) ) {
			$options	= array();
			$args		= func_get_args();
			foreach( array('where', 'orderby') as $i => $key ) {
				if( !isset($args[$i]) ) { break; }
				$options[$key]	= $args[$i];
			}
		}
		$options['table'] = static::$table;
		// May be incompatible with old revisions (< R398)
		if( !isset($options['output']) ) {
			$options['output'] = SQLAdapter::ARR_OBJECTS;
		}
		//This method intercepts outputs of array of objects.
		if( in_array($options['output'], array(SQLAdapter::ARR_OBJECTS, SQLAdapter::OBJECT)) ) {
			if( $options['output'] == SQLAdapter::OBJECT ) {
				$options['number']	= 1;
				$onlyOne	= 1;
			}
			$options['output']	= SQLAdapter::ARR_ASSOC;
// 			$options['what'] = '*';// Could be * or something derived for order e.g
			$objects	= 1;
		}
		$r	= SQLAdapter::doSelect($options, static::$DBInstance, static::$IDFIELD);
		if( empty($r) && in_array($options['output'], array(SQLAdapter::ARR_ASSOC, SQLAdapter::ARR_OBJECTS, SQLAdapter::ARR_FIRST)) ) {
			return array();
		}
		if( !empty($r) && isset($objects) ) {
// 			if( isset($options['number']) && $options['number'] == 1 ) {
			if( isset($onlyOne) ) {
				$r	= static::load($r[0]);
			} else {
				foreach( $r as &$rdata ) {
					$rdata = static::load($rdata);
				}
			}
		}
		return $r;
	}
	
	/** Create a new permanent object
	 * @param $inputData The input data we will check, extract and create the new object.
	 * @param $fields The array of fields to check. Default value is null.
	 * @param $errCount Output parameter to get the number of found errors. Default value is 0
	 * @return The ID of the new permanent object.
	 * @see testUserInput()
	 * @see createAndGet()
	 * 
	 * Create a new permanent object from ths input data.
	 * To create an object, we expect that it is valid, else we throw an exception.
	*/
	public static function create($inputData=array(), $fields=null, &$errCount=0) {
		$data	= static::checkUserInput($inputData, $fields, null, $errCount);
		if( $errCount ) {
			static::throwException('errorCreateChecking');
		}
		$data	= static::getLogEvent('create') + static::getLogEvent('edit') + $data;
		
		// Check if entry already exist
		static::checkForObject($data);
		// To do before insertion
		static::runForObject($data);
		
		$what	= array();
		foreach($data as $fieldname => $fieldvalue) {
			if( in_array($fieldname, static::$fields) ) {
				$what[$fieldname]	= static::formatValue($fieldvalue);
			}
		}
		$options	= array(
			'table'	=> static::$table,
			'what'	=> $what,
		);
		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
		$LastInsert	= SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
		// To do after insertion
		static::applyToObject($data, $LastInsert);
		return $LastInsert;
	}

	/** Create a new permanent object
	 * @param $inputData The input data we will check, extract and create the new object.
	 * @param $fields The array of fields to check. Default value is null.
	 * @param $errCount Output parameter to get the number of found errors. Default value is 0.
	 * @return The new permanent object
	 * @see testUserInput()
	 * @see create()
	 *
	 * Create a new permanent object from ths input data.
	 * To create an object, we expect that it is valid, else we throw an exception.
	 */
	public static function createAndGet($inputData=array(), $fields=null, &$errCount=0) {
		return static::load(static::create($inputData, $fields, $errCount));
	}
	
	/** Completes missing fields
	 * @param $data The data array to complete.
	 * @return The completed data array.
	 * 
	 * Completes an array of data of an object of this class by setting missing fields with empty string.
	*/
	public static function completeFields($data) {
		foreach( static::$fields as $fieldname ) {
			if( !isset($data[$fieldname]) ) {
				$data[$fieldname] = '';
			}
		}
		return $data;
	}
	
	public static function getFields() {
		return static::$fields;
	}
	
	/** Gets the log of an event
	 * @param $event The event to log in this object.
	 * @param $time A specified time to use for logging event.
	 * @param $ipAdd A specified IP Adress to use for logging event.
	 * @see logEvent()
	 * 
	 * Builds a new log event for $event for this time and the user IP address.
	*/
	public static function getLogEvent($event, $time=null, $ipAdd=null) {
		return array(
			$event.'_time'	=> isset($time) ? $time : time(),
			$event.'_date'	=> isset($time) ? sqlDatetime($time) : sqlDatetime(),
			$event.'_ip'	=> isset($ipAdd) ? $ipAdd : (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'NONE' ),
		);
	}
	
	/** Gets the name of this class
	 * @return The name of this class.
	*/
	public static function getClass() {
		return get_called_class();
	}
	
	/** Gets the table of this class
	 * @return The table of this class.
	*/
	public static function getTable() {
		return static::$table;
	}
	
	/** Gets the ID field name of this class
	 * @return The ID field of this class.
	*/
	public static function getIDField() {
		return static::$IDFIELD;
	}
	
	/** Gets the domain of this class
	 * @return The domain of this class.
	 * 
	 * Gets the domain of this class, can be guessed from $table or specified in $domain.
	*/
	public static function getDomain() {
		return static::$domain !== NULL ? static::$domain : static::$table;
	}
	
	/** Gets the validator of this class
	 * @return The validator of this class.
	 * 
	 * Gets the validator of this class.
	*/
	public static function getValidator() {
		return static::$validator;
	}
	
	/** Runs for object
	 * @param $data The new data to process.
	 * @see create()
	 * 
	 * This function is called by create() after checking new data and before inserting them.
	 * In the base class, this method does nothing.
	*/
	public static function runForObject(&$data) { }
	
	/** Apply for new object
	 * @param $data The new data to process.
	 * @param $id The ID of the new object.
	 * @see create()
	 * 
	 * This function is called by create() after inserting new data.
	 * In the base class, this method does nothing.
	*/
	public static function applyToObject(&$data, $id) { }
	
	// 		** VALIDATION METHODS **
	
	/** Check user input
	 * @param $uInputData The user input data to check.
	 * @param $fields The array of fields to check. Default value is null.
	 * @param $ref The referenced object (update only). Default value is null.
	 * @param $errCount The resulting error count, as pointer. Output parameter.
	 * @return The valid data.
	 * 
	 * Check if the class could generate a valid object from $uInputData.
	 * The method could modify the user input to fix them but it must return the data.
	 * The data are passed through the validator, for different cases:
	 * - If empty, this function return an empty array.
	 * - If an array, it uses an field => checkMethod association.
	*/
	public static function checkUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		if( !isset($errCount) ) {
			$errCount = 0;
		}
		// Allow reversed parameters 2 & 3 - Declared as useless
// 		if( !is_array($fields) && !is_object($ref) ) {
// 			$tmp = $fields; $fields = $ref; $ref = $tmp; unset($tmp);
// 		}
// 		if( is_null($ref) && is_object($ref) ) {
// 			$ref = $fields;
// 			$fields = null;
// 		}
		if( is_array(static::$validator) ) {
			if( $fields===NULL ) {
				$fields	= static::$editableFields;
			}
			if( empty($fields) ) { return array(); }
			$data = array();
			foreach( $fields as $field ) {
				// If editing the id field
				if( $field == static::$IDFIELD ) { continue; }
				$value = $notset = null;
				try {
					try {
						// Field to validate
						if( !empty(static::$validator[$field]) ) {
							$checkMeth	= static::$validator[$field];
							// If not defined, we just get the value without check
							$value	= static::$checkMeth($uInputData, $ref);
	
						// Field to NOT validate
						} else if( array_key_exists($field, $uInputData) ) {
							$value	= $uInputData[$field];
						} else {
							$notset	= 1;
						}
						if( !isset($notset) &&
							( $ref===NULL || $value != $ref->$field) &&
							( $fields===NULL || in_array($field, $fields))
						) {
							$data[$field]	= $value;
						}

					} catch( UserException $e ) {
						if( $value===NULL && isset($uInputData[$field]) ) {
							$value	= $uInputData[$field];
						}
						throw InvalidFieldException::from($e, $field, $value);
					}
					
				} catch( InvalidFieldException $e ) {
					$errCount++;
					reportError($e, static::getDomain());
				}
			}
			return $data;
		
		} else if( is_object(static::$validator) ) {
			if( method_exists(static::$validator, 'validate') ) {
				return static::$validator->validate($uInputData, $fields, $ref, $errCount);
			}
		}
		return array();
	}
	
	/** Checks for object
	 * @param $data The new data to process.
	 * @param $ref The referenced object (update only). Default value is null.
	 * @see create()
	 * @see update()
	 * 
	 * This function is called by create() after checking user input data and before running for them.
	 * In the base class, this method does nothing.
	*/
	public static function checkForObject($data, $ref=null) { }
	
	/** Tests user input
	 * @param $uInputData The new data to process.
	 * @param $fields The array of fields to check. Default value is null.
	 * @param $ref The referenced object (update only). Default value is null.
	 * @param $errCount The resulting error count, as pointer. Output parameter.
	 * @see create()
	 * @see checkUserInput()
	 * 
	 * Does a checkUserInput() and a checkForObject()
	*/
	public static function testUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data = static::checkUserInput($uInputData, $fields, $ref, $errCount);
		if( $errCount ) { return false; }
		try {
			static::checkForObject($data, $ref);
		} catch(UserException $e) {
			$errCount++;
			reportError($e, static::getDomain());
			return false;
		}
		return true;
	}
	
	//! Initializes class
	public static function init() {
		$parent = get_parent_class(get_called_class());
		if( empty($parent) ) { return; }
		
		static::$fields = array_unique(array_merge(static::$fields, $parent::$fields));
		if( !is_null($parent::$editableFields) ) {
			static::$editableFields = is_null(static::$editableFields) ? $parent::$editableFields : array_unique(array_merge(static::$editableFields, $parent::$editableFields));
		}
		if( is_array(static::$validator) && is_array($parent::$validator) ) {
			static::$validator = array_unique(array_merge(static::$validator, $parent::$validator));
		}
		if( is_null(static::$domain) ) {
			static::$domain = static::$table;
		}
	}
	
	/** Throw an UserException
	 * @param $message the text message, may be a translation string
	 * @see UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function throwException($message) {
		throw new UserException($message, static::$domain);
	}
	
	public static function throwNotFound($message=null) {
		throw new NotFoundException(static::$domain, $message);
	}
	
	/** Translate text according to the object domain
	 * @param $text The text to translate
	 * 
	 * Translates text according to the object domain
	*/
	public static function text($text) {
		return t($text, static::$domain);
	}
	
	/** Report an UserException
	 * @param $e the UserException
	 * @see UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function reportException(UserException $e) {
		reportError($e);
	}
	
}
PermanentObject::selfInit();