<?php

class OSessionHandler implements SessionHandlerInterface {
	
	private $session;
	private $gc_enabled;
	
	public function __construct($gc_enabled=true) {
		$this->gc_enabled = $gc_enabled;
	}
	
	public function open($save_path , $name) {
		// Do nothing
		return true;
	}
	
	public function close() {
		// Detach current session
		try {
			if( !is_null($this->session) ) {
				$this->session->save();
				$this->session = null;
			}
			return true;
		} catch(Exception $e) {
			sys_error($e);
			return false;
		}
	}
	
	public function read($session_id) {
		try {
			if( !isset($this->session) ) {
				$this->session = Session::getBySessID($session_id);
				if( is_null($this->session) ) {
					$this->session = Session::build($session_id);
				}
			}
			return $this->session->readData();
		} catch(Exception $e) {
			sys_error($e);
			return '';
		}
	}
	
	public function write($session_id, $session_data) {
		try {
			if( !isset($this->session) || $this->session->sessID() != $session_id ) {
				throw new Exception('notSetOrDifferentSession');
			}
			$this->session->writeData($session_data);
			return true;
		} catch(Exception $e) {
			sys_error($e);
			return false;
		}
	}
	
	public function gc($maxlifetime) {
		try {
			if( $this->gc_enabled ) {
				Session::deleteFrom((int) $maxlifetime);
			}
			return true;
		} catch(Exception $e) {
			sys_error($e);
			return false;
		}
	}
	
	public function destroy($session_id) {
		try {
			Session::deleteBySessID($session_id);
			return true;
		} catch(Exception $e) {
			sys_error($e);
			return false;
		}
	}
	
	public function register() {
		session_set_save_handler($this->session, true);
	}
	
	
// 	abstract public bool close ( void )
// 	abstract public bool destroy ( string $session_id )
// 	abstract public bool gc ( string $maxlifetime )
// 	abstract public bool open ( string $save_path , string $name )
// 	abstract public string read ( string $session_id )
// 	abstract public bool write ( string $session_id , string $session_data )
}

if( !class_exists(Session) ) {
// Fake nested class
class Session extends PermanentObject {
	//Attributes
	protected static $table = 'sessions';
	protected static $domain = 'sessions';
	//protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
			'id', 'sessid', 'data', 'create_time', 'create_ip', 'edit_time'
	);
	protected static $editableFields = array('sessid', 'data', 'edit_time');
	protected static $validator = array();
	
	public static function getBySessID($session_id) {
		return static::get(array(
				'where' => 'sessid='.SQLAdapter::quote($session_id),
				'number' => 1,
		));
	}
	public static function deleteBySessID($session_id) {
		return static::delete(array(
			'where' => 'sessid='.SQLAdapter::quote($session_id),
			'number' => 1,
		));
	}
	public static function deleteFrom($delay) {
		return static::delete(array(
			'where' => 'edit_time < '.(time()-intval($delay))
		));
	}
}
}