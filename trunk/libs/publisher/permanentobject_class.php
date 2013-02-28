<?php
using('sqlmapper.SQLMapper');

//! The permanent object class
/*!
 * Manage a permanent object using the SQL Mapper.
 */
abstract class PermanentObject {
	
	//Attributes
	protected static $IDFIELD = 'id';
	protected static $instances = array();
	
	protected static $table = null;
	protected static $fields = array();
	protected static $editableFields = array();
	protected static $validator = null;//! See checkUserInput()
	protected static $domain = null;
	
	protected $modFields = array();
	protected $data = array();
	protected $isDeleted = false;
	
	//! Internal static initialization
	public static function init() {
		static::$fields = array(static::$IDFIELD);
	}
	
	// *** OVERRIDDEN METHODS ***
	
	//! Constructor
	/*!
	 * \param $data An array of the object's data to construct
	 */
	public function __construct(array $data) {
		foreach( static::$fields as $fieldname ) {
			// We condiser null as a valid value.
			if( !array_key_exists($fieldname, $data) ) {
				throw new FieldNotFoundException($fieldname);
			}
			$this->data[$fieldname] = $data[$fieldname];
		}
		$this->modFields = array();
	}
	
	//! Destructor
	/*!
	 * If something was modified, it saves the new data.
	*/
	public function __destruct() {
		if( !empty($this->modFields) ) {
			try {
				$this->save();
			} catch(Exception $e) {
				// Can be destructed outside of the matrix
				sys_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__destruct(): Saving');
			}
		}
	}
	
	//! Magic getter
	/*!
	 * \param $name Name of the property to get
	 * \return The value of field $name
	 * 
	 * Gets the value of field $name.
	 * 'all' returns all fields.
	*/
	public function __get($name) {
		return $this->getValue(($name == 'all') ? null : $name);
	}
	
	//! Magic setter
	/*!
	 * \param $name Name of the property to set
	 * \param $value New value of the property
	 * 
	 * Sets the value of field $name.
	*/
	public function __set($name, $value) {
		$this->setValue($name, $value);
	}
	
	//! Magic isset
	/*!
	 * \param $name Name of the property to check is set
	 * 
	 * Checks if the field $name is set.
	*/
	public function __isset($name) {
        return isset($this->data[$name]);
	}
	
	//! Magic toString
	/*!
	 * \return The string value of the object.
	 * 
	 * The object's value when casting to string.
	*/
	public function __toString() {
		try {
			return '#'.$this->{static::$IDFIELD}.' ('.get_class($this).')';
		} catch( Exception $e ) {
			sys_error($e->getMessage()."<br />\n".$e->getTraceAsString(), 'PermanentObject::__toString()');
		}
	}
	
	// *** DEV METHODS ***
	
	//! Updates this permanent object
	/*!
	* \param $uInputData The input data we will check and extract, used by children.
	* \param $data The data from wich it will update this object, used by parents, including this one.
	* \return 1 in case of success, else 0.
	* \overrideit
	* \sa runForUpdate()
	* 
	* This method require to be overridden but it still be called too by the child classes.
	* Here $uInputData is not used, it is reserved for child classes.
	* $data must contain a filled array of new data.
	* This method update the EDIT event log.
	* Before saving, runForUpdate() is called to let child classes to run custom instructions.
	*/
	public function update($uInputData, array $data=array()) {
		try {
			if( empty($data) ) {
				throw new UserException('updateEmptyData');
			}
			static::checkForObject(static::completeFields($data));
		} catch(UserException $e) { reportError($e, static::getDomain()); return 0; }
		
		foreach($data as $fieldname => $fieldvalue) {
			if( $fieldname != static::$IDFIELD && in_array($fieldname, static::$userEditableFields) ) {
				$this->$fieldname = $fieldvalue;
			}
		}
		if( in_array('edit_time', static::$fields) ) {
			static::logEvent('edit');
		}
		$this->runForUpdate();
		return $this->save();
	}
	
	//! Runs for Update
	/*!
	 * \sa update()
	 * 
	 * This function is called by update() before saving new data.
	 * In the base class, this method does nothing.
	*/
	public function runForUpdate() { }
	
	//! Saves this permanent object
	/*!
	 * \return 1 in case of success, else 0.
	 * 
	 * If some fields was modified, it saves these fields using the SQL Mapper.
	*/
	public function save() {
		if( empty($this->modFields) ) {
			return 0;
		}
		$updQ = '';
		foreach($this->modFields as $fieldname) {
			if( $fieldname != static::$IDFIELD ) {
				$updQ .= ( (!empty($updQ)) ? ', ' : '').$fieldname.'='.SQLMapper::quote($this->$fieldname);
			}
		}
		$IDFIELD=static::$IDFIELD;
		$this->modFields = array();
		$options = array(
			'what'	=> $updQ,
			'table'	=> static::$table,
			'where'	=> "{$IDFIELD}={$this->{$IDFIELD}}",
			'number'=> 1,
		);
		return SQLMapper::doUpdate($options);
	}
	
	//! Marks the field as modified
	/*!
	 * \param $field The field to mark as modified.
	 * 
	 * Adds the $field to the modified fields array.
	*/
	private function addModFields($field) {
		if( !in_array($field, $this->modFields) ) {
			$this->modFields[] = $field;
		}
	}
	
	//! Checks if this object is deleted
	/*!
	 * \return True if this object is deleted.
	 * 
	 * Checks if this object is known as deleted.
	*/
	public function isDeleted() {
		return $this->isDeleted;
	}
	
	//! Marks this object as deleted
	/*!
	 * \sa isDeleted()
	 * \warning Be sure what you are doing before calling this function (never out of this class' context).
	 * 
	 * Marks this object as deleted
	 */
	public function markAsDeleted() {
		$this->isDeleted = true;
	}
	
	//! Gets one value or all values.
	/*!
	 * \param $key Name of the field to get.
	 * 
	 * Gets the value of field $key or all data values if $key is null.
	*/
	public function getValue($key=null) {
		if( !empty($key) ) {
			if( !in_array($key, static::$fields) ) {
				throw new FieldNotFoundException($key);
			}
			return $this->data[$key];
		}
		return $this->data;
	}
	
	//! Sets the value of a field
	/*!
	 * \param $key Name of the field to set.
	 * \param $value New value of the field.
	 * 
	 * Sets the field $key with the new $value.
	*/
	public function setValue($key, $value) {
		if( !isset($key, $value) ) {
			throw new UserException("nullValue");
			
		} else if( !in_array($key, static::$fields) ) {
			throw new FieldNotFoundException($key);
			
		} else {
			if( empty($this->data[$key]) || $value !== $this->data[$key] ) {
				$this->addModFields($key);
				$this->data[$key] = $value;
			}
		}
	}
	
	//! Verifies equality
	/*!
	 * \param $o The object to compare.
	 * \return True if this object represents the same data, else False.
	 * 
	 * Compares the class and the ID field value of the 2 objects.
	*/
	public function equals(PermanentObject $o) {
		return (get_class($this)==get_class($o) && $this->{static::$IDFIELD}==$o->{static::$IDFIELD});
	}
	
	//! Logs an event
	/*!
	 * \param $event The event to log in this object.
	 * \param $time A specified time to use for logging event.
	 * \param $ipAdd A specified IP Adress to use for logging event.
	 * \sa getLogEvent()
	 * 
	 * Logs an event to this object's data.
	*/
	public function logEvent($event, $time=null, $ipAdd=null) {
		$log = static::getLogEvent($event, $time, $ipAdd);
		$this->setValue($event.'_time', $log[$event.'_time']);
		$this->setValue($event.'_ip', $log[$event.'_ip']);
	}
	
	// *** STATIC METHODS ***
	
	//! Loads a permanent object
	/*!
	 * \param $in The object ID to load or a valid array of the object's data.
	 * \return The object.
	 * \sa get()
	
	 * Loads the object with the ID $id or the array data
	*/
	public static function load($in) {
		$IDFIELD=static::$IDFIELD;
		// If $in is an array, we trust him, as data of the object.
		if( is_array($in) ) {
			$id = $in[$IDFIELD];
			$data = $in;
		} else {
			$id = $in;
		}
		// Loading cached
		if( isset(static::$instances[static::getTable()][$id]) ) {
			return static::$instances[static::getTable()][$id];
		}
		// If we don't get the data, we request them.
		if( empty($data) ) {
			if( !ctype_digit("$id") ) {
				throw new UserException('invalidID');
			}
			// Getting data
			$data = static::get(array(
				'number'=> 1,
				'where'	=> "{$IDFIELD}={$id}",
			));
			// Ho no, we don't have the data, we can't load the object !
			if( empty($data) ) {
				throw new UserException('inexistantobject');
			}
		}
		// Saving cached
		return static::$instances[static::getTable()][$id] = new static($data);
	}
	
	//! Deletes a permanent object
	/*!
	 * \param $id The object ID to delete.
	 * \return 1 in case of success, else 0.
	 * 
	 * Deletes the object with the ID $id
	*/
	public static function delete($id) {
		if( !ctype_digit("$id") ) {
			throw new UserException('invalidID');
		}
		$IDFIELD=static::$IDFIELD;
		$options = array(
			'table'	=> static::$table,
			'number'=> 1,
			'where'	=> "{$IDFIELD}={$id}",
		);
		$r = SQLMapper::doDelete($options);
		if( $r ) {
			if( isset(static::$instances[static::getTable()][$id]) ) {
				static::$instances[static::getTable()][$id]->markAsDeleted();
			}
			static::runForDeletion($id);
		}
		return $r;
	}
	
	//! Runs for Deletion
	/*!
	 * \sa delete()
	 * 
	 * This function is called by delete() after deleting the object $id.
	 * If you need to get the object before, prefer to inherit delete()
	 * In the base class, this method does nothing.
	*/
	public static function runForDeletion() { }
	
	//! Gets some permanent objects
	/*!
	 * \param $options The options used to get the permanents object.
	 * \return An array of array containing object's data.
	 * \sa SQLMapper
	 * 
	 * Gets an objects' list using this class' table.
	 * The following explanations are for the case where output is SQLMapper::ARR_OBJECTS
	 * If only one object is expected, we try to load and return it, else we return null.
	 * In other cases, we load them and return a list of all objects, event if there is not result or only one.
	*/
	public static function get(array $options=array()) {
		$options['table'] = static::$table;
		// May be incompatible with old revisions (< R398)
		if( !isset($options['output']) ) {
			$options['output'] = SQLMapper::ARR_OBJECTS;
		}
		//This method intercepts outputs of array of objects.
		if( $options['output'] == SQLMapper::ARR_OBJECTS ) {
			$options['output'] = SQLMapper::ARR_ASSOC;
			$objects = 1;
		}
		$r = SQLMapper::doSelect($options);
		if( !empty($r) && isset($objects) ) {
			if( isset($options['number']) && $options['number'] == 1 ) {
				$r = (!empty($r)) ? static::load($r) : null;
			} else {
				foreach( $r as &$rdata ) {
					$rdata = static::load($rdata);
				}
			}
		}
		return $r;
	}
	
	//! Creates a new permanent object
	/*!
	 * \param $inputData The input data we will check, extract and create the new object.
	 * \return The ID of the new permanent object.
	 * 
	 * Creates a new permanent object from ths input data.
	*/
	public static function create($inputData) {
		$data = static::checkUserInput($inputData);
		
		if( in_array('create_time', static::$fields) ) {
			$data += static::getLogEvent('create');
		}
		//Check if entry already exist
		static::checkForObject($data);
		//Other Checks and to do before insertion
		static::runForObject($data);
		
		$what = array();
		foreach($data as $fieldname => $fieldvalue) {
			if( in_array($fieldname, static::$fields) ) {
				$what[$fieldname] = SQLMapper::quote($fieldvalue);
			}
		}
		$options = array(
			'table'	=> static::$table,
			'what'=> $what,
		);
		SQLMapper::doInsert($options);
		$LastInsert = SQLMapper::doLastID(static::$table, static::$IDFIELD);
		//To do after insertion
		static::applyToObject($data, $LastInsert);//old ['LAST_INSERT_ID()']
		return $LastInsert;
	}
	
	//! Completes missing fields
	/*!
	 * \param $data The data array to complete.
	 * \return The completed data array.
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
	
	//! Gets the log of an event
	/*!
	 * \param $event The event to log in this object.
	 * \param $time A specified time to use for logging event.
	 * \param $ipAdd A specified IP Adress to use for logging event.
	 * \sa logEvent()
	 * 
	 * Builds a new log event for $event for this time and the user IP adress.
	*/
	public static function getLogEvent($event, $time=null, $ipAdd=null) {
		return array(
			$event.'_time' => (isset($time)) ? $time : time(),
			$event.'_ip' => (isset($ipAdd)) ? $ipAdd : $_SERVER['REMOTE_ADDR'],
		);
	}
	
	//! Gets the table of this class
	/*!
	 * \return The table of this class.
	*/
	public static function getTable() {
		return static::$table;
	}
	
	//! Gets the ID field name of this class
	/*!
	 * \return The ID field of this class.
	*/
	public static function getIDField() {
		return static::$IDFIELD;
	}
	
	//! Gets the domain of this class
	/*!
	 * \return The domain of this class.
	 * 
	 * Gets the domain of this class, can be guessed from $table or specified in $domain.
	*/
	public static function getDomain() {
		return ( !is_null(static::$domain) ) ? static::$domain : static::$table;
	}
	
	//! Runs for object
	/*!
	 * \param $data The new data to process.
	 * \sa create()
	 * 
	 * This function is called by create() after checking new data and before inserting them.
	 * In the base class, this method does nothing.
	*/
	public static function runForObject(&$data) { }
	
	//! Applies for object
	/*!
	 * \param $data The new data to process.
	 * \param $id The ID of the new object.
	 * \sa create()
	 * 
	 * This function is called by create() after inserting new data.
	 * In the base class, this method does nothing.
	*/
	public static function applyToObject(&$data, $id) { }
	
	// 		** VALIDATION METHODS **
	
	//! Checks user input
	/*!
	 * \param $uInputData The user input data to check.
	 * \param $ref The referenced object (update only).
	 * \return The valid data.
	 * \overrideit
	 * 
	 * Checks if the class could generate a valid object from $uInputData.
	 * The method could modify the user input to fix them but it must return the data.
	*/
	public static function checkUserInput($uInputData, $ref=null) {
		if( empty(static::$validator) ) {
			return array();
		}
		if( is_array(static::$validator) ) {
			$data = array();
			foreach( static::$validator as $field => $checkMeth ) {
				// If editing an uneditable field.
				if( !is_null($ref) && !in_array($field, static::$editableFields) ) {
					continue;
				}
				try {
					$value = static::$checkMeth($uInputData);
					if( is_null($ref) || $value != $ref->$field ) {
						$data[$field] = $value;
					}
				} catch(UserException $e) {
					// TODO: Exclude empty field from error while updating.
					reportError($e, static::getDomain());
				}
			}
			return $data;
		
		} else if( is_object(static::$validator) ) {
			if( method_exists(static::$validator, 'validate') ) {
				return static::$validator->validate($uInputData);
			}
		}
		return array();
		// TODO: Using config file.
		// else if( is_string(static::$validator) ) { }
	}
	
	//! Checks for object
	/*!
	 * \param $data The new data to process.
	 * \sa create()
	 * 
	 * This function is called by create() after checking user input data and before running for them.
	 * In the base class, this method does nothing.
	*/
	public static function checkForObject($data) { }
}
PermanentObject::init();
?>