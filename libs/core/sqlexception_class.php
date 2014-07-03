<?php
//! The SQL exception class
/*!
	This exception is thrown when an occured caused by the SQL DBMS (or DBMS tools).
*/
class SQLException extends Exception {
	
	protected $original;
	
	public function __construct($message=null, $original=null) {
		parent::__construct($message);
		$this->original	= $original;
	}
	
	public function getOriginal() {
		return $this->original;
	}
	
	public function getReport() {
		return $this->getText();
	}
	
	public function getText() {
		return $this->getMessage();
	}
	
	public function __toString() {
		try {
			return $this->getText();
		} catch(Exception $e) {
			if( ERROR_LEVEL == DEV_LEVEL ) {
				die('A fatal error occurred in UserException::__toString() :<br />'.$e->getMessage());
			}
			die('A fatal error occurred, please report it to an admin.<br />Une erreur fatale est survenue, veuillez contacter un administrateur.<br />');
// 			reportError($e);
		}
		return '';
	}
}
