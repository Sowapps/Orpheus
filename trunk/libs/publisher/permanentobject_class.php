<?php
using('sqladapter.SQLAdapter');

//! The permanent object class
/*!
 * Manage a permanent object using the SQL Adapter.
 */
abstract class PermanentObject {
	
	//Attributes
	protected static $IDFIELD = 'id';
	protected static $instances = array();
	
	protected static $table = null;
	protected static $DBInstance = null;
	// Contains all fields
	protected static $fields = array();
	// Contains fields editables by users
	protected static $editableFields = array();
	// Contains the validator. The default one is an array system.
	protected static $validator = array();//! See checkUserInput()
	// Contains the domain. Used as default UserException domain.
	protected static $domain = null;
	
	protected $modFields = array();
	protected $data = array();
	protected $isDeleted = false;
	
	//! Internal static initialization
	public static function selfInit() {
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
	
	//! Gets this permanent object's ID
	/*!
	 * \return The id of this object.
	 * 
	 * Gets this object ID according to the IDFIELD attribute.
	 */
	public function id() {
		return $this->{static::$IDFIELD};
	}
	
	//! Updates this permanent object
	/*!
	 * \param $uInputData The input data we will check and extract, used by children.
	 * \param $fields The array of fields to check. Default value is null.
	 * \return 1 in case of success, else 0.
	 * \overrideit
	 * \sa runForUpdate()
	 * 
	 * This method require to be overridden but it still be called too by the child classes.
	 * Here $uInputData is not used, it is reserved for child classes.
	 * $data must contain a filled array of new data.
	 * This method update the EDIT event log.
	 * Before saving, runForUpdate() is called to let child classes to run custom instructions.
	 * Parameter $fields is really useful to allow partial modification only (against form hack).
	 */
	public function update($uInputData, $fields=null) {
		$data = static::checkUserInput($uInputData, $fields, $this);
		// Don't care about some errors, other fields should be updated.
		try {
			if( empty($data) ) {
				static::throwException('updateEmptyData');
			}
			static::checkForObject(static::completeFields($data));
		} catch(UserException $e) { reportError($e, static::getDomain()); return 0; }
		
		$oldData = $this->all;
		foreach($data as $fieldname => $fieldvalue) {
			if( isset($fieldvalue) && $fieldname != static::$IDFIELD && in_array($fieldname, static::$editableFields) ) {
				$this->$fieldname = $fieldvalue;
// 			} else {
// 				Do not delete real_password
// 				unset($data[$fieldname]);
			}
		}
		if( in_array('edit_time', static::$fields) ) {
			static::logEvent('edit');
		}
		if( $r = $this->save() ) {
			$this->runForUpdate($data, $oldData);
		}
		return $r;
	}
	
	//! Runs for Update
	/*!
	 * \param $data the new data
	 * \param $oldData the old data
	 * \sa update()
	 * 
	 * This function is called by update() before saving new data.
	 * $data contains only edited data, excluding invalids and not changed ones.
	 * In the base class, this method does nothing.
	*/
	public function runForUpdate($data, $oldData) { }
	
	//! Saves this permanent object
	/*!
	 * \return 1 in case of success, else 0
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
				$updQ .= ( (!empty($updQ)) ? ', ' : '').$fieldname.'='.SQLAdapter::quote($this->$fieldname);
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
		return SQLAdapter::doUpdate($options, static::$DBInstance, static::$IDFIELD);
	}
	
	//! Reloads fields from database
	/*!
	 * \param $field The field to reload, default is null (all fields).
	 * 
	 * Updates the current object's fields from database.
	 * If $field is not set, it reloads only one field else all fields.
	 * Also it removes the reloaded fields from the modified ones list.
	*/
	public function reload($field=null) {
		$IDFIELD = static::$IDFIELD;
		$options = array('where' => $IDFIELD.'='.$this->$IDFIELD, 'output' => SQLAdapter::ARR_ASSOC, 'number' => 1);
		if( !is_null($field) ) {
			if( !in_array($field, $fields) ) {
				throw new FieldNotFoundException($field);
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
		if( !is_null($field) ) {
			$this->data[$field] = $data[$field];
		} else {
			$this->data = $data;
		}
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
		if( empty($key) ) {
			return $this->data;
		}
// 		if( !isset($this->data[$key]) ) {
		if( !array_key_exists($key, $this->data) ) {
			log_debug('Key "'.$key.'" not found in array :');
			log_debug($this->data);
			throw new FieldNotFoundException($key);
		}
		return $this->data[$key];
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
			static::throwException("nullValue");
			
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
	public function equals($o) {
		return (get_class($this)==get_class($o) && $this->id()==$o->id());
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
		try {
			$this->setValue($event.'_ip', $log[$event.'_ip']);
		} catch(FieldNotFoundException $e) {}
	}
	
	// *** STATIC METHODS ***
	
	//! Loads a permanent object
	/*!
	 * \param $in The object ID to load or a valid array of the object's data.
	 * \return The object.
	 * \sa get()
	 * Exceptions invalidParameter_IN
	
	 * Loads the object with the ID $id or the array data
	*/
	public static function load($in) {
		if( empty($in) ) {
			static::throwException('invalidParameter_IN');
		}
		$IDFIELD=static::$IDFIELD;
		// If $in is an array, we trust him, as data of the object.
		if( is_array($in) ) {
			$id = $in[$IDFIELD];
			$data = $in;
		} else {
			$id = $in;
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
				'where'	=> "{$IDFIELD}={$id}",
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
	
	//! Deletes a permanent object
	/*!
	 * \param $in The object ID to delete or the delete array.
	 * \return the number of deleted rows.
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
		
		if( !ctype_digit("$in") ) {
			static::throwException('invalidID');
		}
		$IDFIELD=static::$IDFIELD;
		$options = array(
			'table'	=> static::$table,
			'number'=> 1,
			'where'	=> "{$IDFIELD}={$in}",
		);
		$r = SQLAdapter::doDelete($options, static::$DBInstance, static::$IDFIELD);
		if( $r ) {
			if( isset(static::$instances[static::getClass()][$in]) ) {
				static::$instances[static::getClass()][$in]->markAsDeleted();
			}
			static::runForDeletion($in);
		}
		return $r;
	}
	
	//! Runs for Deletion
	/*!
	 * \param $id The deleted object ID.
	 * \sa delete()
	 * 
	 * This function is called by delete() after deleting the object $id.
	 * If you need to get the object before, prefer to inherit delete()
	 * In the base class, this method does nothing.
	*/
	public static function runForDeletion($id) { }
	
	//! Gets some permanent objects
	/*!
	 * \param $options The options used to get the permanents object.
	 * \return An array of array containing object's data.
	 * \sa SQLAdapter
	 * 
	 * Gets an objects' list using this class' table.
	 * 
	*/
	public static function get(array $options=array()) {
		// Going out of documentation (obsolete)
// 	 * The following explanations are for the case where output is SQLAdapter::ARR_OBJECTS
// 	 * If only one object is expected, we try to load and return it, else we return null.
// 	 * In other cases, we load them and return a list of all objects, even if there is no result or only one.
		$options['table'] = static::$table;
		// May be incompatible with old revisions (< R398)
		if( !isset($options['output']) ) {
			$options['output'] = SQLAdapter::ARR_OBJECTS;
		}
		//This method intercepts outputs of array of objects.
		if( $options['output'] == SQLAdapter::ARR_OBJECTS || $options['output'] == SQLAdapter::OBJECT ) {
			if( $options['output'] == SQLAdapter::OBJECT ) {
				$options['number'] = 1;
				$onlyOne = 1;
			}
			$options['output'] = SQLAdapter::ARR_ASSOC;
			$options['what'] = '*';
			$objects = 1;
		}
		$r = SQLAdapter::doSelect($options, static::$DBInstance, static::$IDFIELD);
		if( empty($r) && ($options['output'] == SQLAdapter::ARR_ASSOC || $options['output'] == SQLAdapter::ARR_OBJECTS) ) {
			return array();
		}
		if( !empty($r) && isset($objects) ) {
// 			if( isset($options['number']) && $options['number'] == 1 ) {
			if( isset($onlyOne) ) {
				$r = static::load($r[0]);
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
	 * \sa testUserInput()
	 * 
	 * Creates a new permanent object from ths input data.
	 * When really creating an object, we expect that it is valid, else we throw an exception.
	*/
	public static function create($inputData=array()) {
		$data = static::checkUserInput($inputData, null, null, $errCount);
		if( $errCount ) {
			static::throwException('errorCreateChecking');
		}
		
		if( in_array('create_time', static::$fields) ) {
			$data += static::getLogEvent('create');
		}
// 		text("Creating from ".htmlSecret($data));
		// Check if entry already exist
		static::checkForObject($data);
// 		text("checkForObject ".htmlSecret($data));
		// To do before insertion
		static::runForObject($data);
// 		text("runForObject from ".htmlSecret($data));
		
		$what = array();
		foreach($data as $fieldname => $fieldvalue) {
			if( in_array($fieldname, static::$fields) ) {
				$what[$fieldname] = SQLAdapter::quote($fieldvalue);
			}
		}
		$options = array(
			'table'	=> static::$table,
			'what'=> $what,
		);
// 		text("Class fields ".htmlSecret(static::$fields));
// 		text("Creating query options ".htmlSecret($options));
		SQLAdapter::doInsert($options, static::$DBInstance, static::$IDFIELD);
		$LastInsert = SQLAdapter::doLastID(static::$table, static::$IDFIELD, static::$DBInstance);
		// To do after insertion
		static::applyToObject($data, $LastInsert);
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
			$event.'_ip' => (isset($ipAdd)) ? $ipAdd : (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'NONE' ),
		);
	}
	
	//! Gets the name of this class
	/*!
	 * \return The name of this class.
	*/
	public static function getClass() {
		return get_called_class();
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
	 * \param $fields The array of fields to check. Default value is null.
	 * \param $ref The referenced object (update only). Default value is null.
	 * \param $errCount The resulting error count, as pointer. Output parameter.
	 * \return The valid data.
	 * \overrideit
	 * 
	 * Checks if the class could generate a valid object from $uInputData.
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
			if( empty(static::$editableFields) ) {
				return array();
			}
			$data = array();
			foreach( static::$editableFields as $field ) {
				// If editing the id field
				if( $field == static::$IDFIELD ) {
					continue;
				}
				try {
					$value = null;
					// Field to validate
					if( !empty(static::$validator[$field]) ) {
						$checkMeth = static::$validator[$field];
						// If not defined, we just get the value without check
						$value = static::$checkMeth($uInputData, $ref);

					// Field to NOT validate
					} else if( isset($uInputData[$field]) ) {
						$value = $uInputData[$field];
					}
					if( !is_null($value) &&
						(is_null($ref) || $value != $ref->$field) &&
						(is_null($fields) || in_array($field, $fields))
					) {
						$data[$field] = $value;
					}
				} catch(UserException $e) {
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
	
	//! Checks for object
	/*!
	 * \param $data The new data to process.
	 * \sa create()
	 * 
	 * This function is called by create() after checking user input data and before running for them.
	 * In the base class, this method does nothing.
	*/
	public static function checkForObject($data) { }
	
	//! Tests user input
	/*!
	 * \param $data The new data to process.
	 * \param $fields The array of fields to check. Default value is null.
	 * \param $ref The referenced object (update only). Default value is null.
	 * \param $errCount The resulting error count, as pointer. Output parameter.
	 * \sa create()
	 * \sa checkUserInput()
	 * 
	 * Does a checkUserInput() and a checkForObject()
	*/
	public static function testUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data = static::checkUserInput($uInputData, $fields, $ref, $errCount);
		if( $errCount ) {
			return;
		}
		try {
			static::checkForObject($data);
		} catch(UserException $e) {
			reportError($e, static::getDomain());
		}
	}
	
	//! Initializes class
	public static function init() {
		$parent = get_parent_class(get_called_class());
		if( empty($parent) ) {
			return;
		}
		static::$fields = array_unique(array_merge(static::$fields, $parent::$fields));
		static::$editableFields = array_unique(array_merge(static::$editableFields, $parent::$editableFields));
		if( is_array(static::$validator) && is_array($parent::$validator) ) {
			static::$validator = array_unique(array_merge(static::$validator, $parent::$validator));
		}
		if( is_null(static::$domain) ) {
			static::$domain = static::$table;
		}
	}
	
	//! Throws an UserException
	/*!
	 * \param $message the text message, may be a translation string
	 * \sa UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function throwException($message) {
		throw new UserException($message, static::$domain);
	}
	
	//! Reports an UserException
	/*!
	 * \param $e the UserException
	 * \sa UserException
	 * 
	 * Throws an UserException with the current domain.
	*/
	public static function reportException(UserException $e) {
		reportError($e);
	}
	
}
PermanentObject::selfInit();