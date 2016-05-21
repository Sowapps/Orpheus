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
			log_error($e);
			return false;
		}
	}
	
	public function read($session_id) {
// 		text("Reading Session $session_id");
		try {
			if( !isset($this->session) ) {
				$this->session = Session::getBySessID($session_id);
				if( empty($this->session) ) {
					$this->session = Session::build($session_id);
				}
			}
// 			log_debug('Loaded session, reading data...');
// 			log_debug($this->session->sessID());
// 			text($this->session);
			return $this->session->readData();
		} catch(Exception $e) {
			log_error($e);
			return '';
		}
	}
	
	public function write($session_id, $session_data) {
// 		text("Writing Session $session_id");
		try {
			if( !isset($this->session) ) {
				throw new Exception('notSetSession');
			}
// 			if( $this->session->sessID() != $session_id ) {
// 				throw new Exception('differentSession_'.$session_id);
// 			}
			$this->session->writeData($session_id, $session_data);
			return true;
		} catch(Exception $e) {
			log_error($e);
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
			log_error($e);
			return false;
		}
	}
	
	public function destroy($session_id) {
		try {
			Session::deleteBySessID($session_id);
			return true;
		} catch(Exception $e) {
			log_error($e);
			return false;
		}
	}
	
	public static function register(OSessionHandler $sessionHandler=null) {
		if( is_null($sessionHandler) ) {
			$sessionHandler = new static();
		}
		session_set_save_handler($sessionHandler, true);
	}
	
	
// 	abstract public bool close ( void )
// 	abstract public bool destroy ( string $session_id )
// 	abstract public bool gc ( string $maxlifetime )
// 	abstract public bool open ( string $save_path , string $name )
// 	abstract public string read ( string $session_id )
// 	abstract public bool write ( string $session_id , string $session_data )
}