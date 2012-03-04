<?php
/* class/abstracttable_class.php
 * PHP File for class: AbstractTable
 * Classe abstraite de surcharge de table de base de données.
 *
 * Author: Florent Hazard (Cartman34).
 * Revision: 6
 * 
 * Requiert:
 * pdo_query()
 * user_can()
 */ 

abstract class AbstractTable {
	
	//Attributes
	
	protected $modFields = array();
	protected $data = array();
	protected static $table = null;
	protected static $fields = array();
	protected static $userEditableFields = array();
	protected static $IDFIELD = 'id';
	
	// *** OVERLOADED METHODS ***
	
	public function __construct($data) {
		foreach( static::$fields as $fieldname ) {
			if( !isset($data[$fieldname]) ) {
				throw new FieldNotFoundException($fieldname);
			}
			$this->data[$fieldname] = $data[$fieldname];
		}
		$this->modFields = array();
	}
	
	public function __get($name) {
		try {
			return $this->getData($name);
		} catch(FieldNotFoundException $e) {
			/* Previously, we got the attribute if
			 * the data does not exist but private is private.
			 */
			throw $e;
		}
	}
	
	public function __set($name, $value) {
		try {
			$this->setData($name, $value);
		} catch(FieldNotFoundException $e) {
			/* Previously, we set the attribute if
			 * the data does not exist but private is private.
			 */
			throw $e;
		}
	}
	
	public function __destruct() {
		if( !empty($this->modFields) ) {
			try {
				$this->save();
			} catch(Exception $e) {
				text('An error occured while saving (__destruct):');
				text($e->getMessage());
			}
		}
	}
	
	public function __toString() {
		return '#'.$this->{static::$IDFIELD}.' ('.static::getClass().')';
	}
	
	// *** USER METHODS ***
	
	public static function getClass() {
		return __CLASS__;
	}
	
	public function update($uInputData, array $data=array()) {
		try {
			if( empty($data) ) {
				throw new UserException('updateEmptyData');
			}
			static::checkForEntry(static::completeFields($data));
		} catch(UserException $e) { addUserError($e); return 0; }
		
		foreach($data as $fieldname => $fieldvalue) {
			if( $fieldname != static::$IDFIELD && (user_can(static::$table.'_edit') || in_array($fieldname, static::$userEditableFields)) ) {
				$this->$fieldname = $fieldvalue;
			}
		}
		if( in_array('edit_time', static::$fields) ) {
			static::logEvent('edit');
		}
		$this->runForUpdate();
		return $this->save();
	}
	
	public function runForUpdate() { }
	
	public function save() {
		if( empty($this->modFields) ) {
			return 0;
		}
		$updQ = '';
		foreach($this->modFields as $fieldname) {
			if( $fieldname != static::$IDFIELD ) {
				$updQ .= ( (!empty($updQ)) ? ', ' : '').$fieldname.'='.pdo_quote($this->$fieldname);
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
	
	private function addModFields($field) {
		if( !in_array($field, $this->modFields) ) {
			$this->modFields[] = $field;
		}
	}
	
	public function setData($key, $value) {
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
	
	public function getData($key=null) {
		if( !empty($key) ) {
			if( !in_array($key, static::$fields) ) {
				throw new FieldNotFoundException($key);
			}
			return $this->data[$key];
		}
		return $this->data;
	}
	
	public function equals(AbstractTable $o) {
		return (static::getClass()==$o::getClass() && $this->{static::$IDFIELD}==$o->{static::$IDFIELD});
	}
	
	public function logEvent($event, $time=null, $addIP=null) {
		$log = static::getLogEvent($event, $time, $addIP);
		$this->setData($event.'_time', $log[$event.'_time']);
		$this->setData($event.'_ip', $log[$event.'_ip']);
	}
	
	// *** STATIC METHODS ***
	
	public static function load($id) {
		if( !ctype_digit("$id") ) {
			throw new UserException('invalidID');
		}
		$IDFIELD=static::$IDFIELD;
		$options = array(
			'table'	=> static::$table,
			'number'=> 1,
			'where'	=> "{$IDFIELD}={$id}",
		);
		$data = SQLMapper::doSelect($options);
		if( empty($data) ) {
			throw new UserException('inexistantEntry');
		}
		return new static($data[0]);
	}
	
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
		return SQLMapper::doDelete($options);
	}
	
	public static function get(array $options=array()) {
		$options['table'] = static::$table;
		return SQLMapper::doSelect($options);
	}
	
	public static function create($inputData) {
		$data = static::checkUserInput($inputData);
		
		if( in_array('create_time', static::$fields) ) {
			$data += static::getLogEvent('create');
		}
		//Check if entry already exist
		static::checkForEntry($data);
		//Other Checks and to do before insertion
		static::runForEntry($data);
		
		$insertQ = '';
		foreach($data as $fieldname => $fieldvalue) {
			$insertQ .= ( (!empty($insertQ)) ? ', ' : '').$fieldname.'='.pdo_quote($fieldvalue);
		}
		$options = array(
			'table'	=> static::$table,
			'what'=> $insertQ,
		);
		SQLMapper::doInsert($options);
		$LastInsert = pdo_query("SELECT LAST_INSERT_ID();", PDOFETCHFIRSTCOL);
		//To do after insertion
		static::applyToEntry($data, $LastInsert);//old ['LAST_INSERT_ID()']
		return $LastInsert;
	}
	
	public static function getLogEvent($event, $time=null, $addIP=null) {
		if( !isset($time) ) {
			$time=time();
		}
		if( !isset($addIP) ) {
			$addIP=$_SERVER['REMOTE_ADDR'];
		}
		return array($event.'_time' => $time, $event.'_ip' => $addIP);
	}
	
	public static function getTable() {
		return static::$table;
	}
	
	public static function getIDField() {
		return static::$IDFIELD;
	}
	
	public static function completeFields($data) {
		foreach( static::$fields as $fieldname ) {
			if( !isset($data[$fieldname]) ) {
				$data[$fieldname] = '';
			}
		}
		return $data;
	}
	
	public static function runForEntry(&$data) { }
	
	public static function applyToEntry(&$data, $id) { }
	
	// 		** CHECK METHODS **
	
	public static function checkUserInput($uInputData) { }
	
	public static function checkForEntry($data) { }
}
?>