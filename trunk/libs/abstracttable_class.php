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
	//Defaults for getting list
	protected static $listDefaults = array(
		'orderby'		=> '',//Ex: Field1 ASC, Field2 DESC
		'number'		=> -1,//-1 => All
		'offset'		=> 0,//0 => The start
		'fields'		=> '*',//* => All fields
		'output'		=> '2',//2 => ARR_ASSOC
		'whereclause'	=> '',//Additionnal Whereclause
	);
	//List of outputs for getting list
	const ARR_OBJECTS	= 1;
	const ARR_ASSOC		= 2;
	const STATEMENT		= 3;
	const SQLQUERY		= 4;

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
		$table=static::$table;
		$IDFIELD=static::$IDFIELD;
		$this->modFields = array();
		return pdo_query("UPDATE {$table} SET {$updQ} WHERE {$IDFIELD}={$this->{$IDFIELD}} LIMIT 1", PDOEXEC);
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
		$table=static::$table;
		$IDFIELD=static::$IDFIELD;
		$data = pdo_query("SELECT * FROM {$table} WHERE {$IDFIELD}={$id} LIMIT 1", PDOFETCH);
		if( empty($data) ) {
			throw new UserException('inexistantEntry');
		}
		return new static($data);
	}
	
	public static function delete($id) {
		if( !ctype_digit("$id") ) {
			throw new UserException('invalidID');
		}
		$table=static::$table;
		$IDFIELD=static::$IDFIELD;
		return pdo_query("DELETE FROM {$table} WHERE {$IDFIELD}={$id} LIMIT 1", PDOEXEC);
	}
	
	public static function get(array $options=array()) {
		//Si besoin les descendants doivent surcharger cette méthode, surcharger les valeurs par défaut n'est pas suffisant.
		$options += self::$listDefaults;
		$table = static::$table;
		$WC = ( !empty($options['whereclause']) ) ? 'WHERE '.$options['whereclause'] : '';
		$ORDERBY = ( !empty($options['orderby']) ) ? 'ORDER BY '.$options['orderby'] : '';
		$LIMIT = ( $options['number'] > 0 ) ? 'LIMIT '.( ($options['offset'] > 0) ? $options['offset'].', ' : '' ).$options['number'] : '';
		$QUERY = "SELECT {$options['fields']} FROM {$table} {$WC} {$ORDERBY} {$LIMIT};";
		if( $options['output'] == self::SQLQUERY ) {
			return $QUERY;
		}
		$results = pdo_query($QUERY, ($options['output'] == self::STATEMENT) ? PDOSTMT : PDOFETCHALL );
		if( $options['output'] == self::ARR_OBJECTS ) {
			foreach($results as &$r) {
				$r = new static($r);
			}
		}
		return $results;
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
		$table=static::$table;
		pdo_query("INSERT INTO {$table} SET {$insertQ}", PDOEXEC);
		$LastInsert = pdo_query("SELECT LAST_INSERT_ID();", PDOFETCH);
		//To do after insertion
		static::applyToEntry($data, $LastInsert['LAST_INSERT_ID()']);
		return $LastInsert['LAST_INSERT_ID()'];
	}
	
	public static function runForEntry(&$data) { }
	
	public static function applyToEntry(&$data, $id) { }
	
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
	
	// 		** CHECK METHODS **
	
	public static function checkUserInput($uInputData) { }
	
	public static function checkForEntry($data) { }
}
?>