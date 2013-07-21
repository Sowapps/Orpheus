<?php

class FSSession implements SessionInterface {
	//Attributes
	protected static $table = 'sessions';
	protected static $domain = 'sessions';
	//protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
			'id', 'sessid', 'data', 'create_time', 'create_ip', 'edit_time'
	);
	protected static $editableFields = array('sessid', 'data', 'edit_time');
	protected static $validator = array();
	
	public function writeData($data) {
		$this->data = $session_data;
		$this->edit_time = time();
	}
	
	public function readData() {
		return $this->data;
	}
	
	public function sessID() {
		return $this->sessid;
	}
	
// 	public function save();
	
	public static function build($session_id) {
		return Session::load(Session::create(array('sessid'=>$session_id, 'edit_time'=>time())));
	}
	
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

class Session extends DBSession {}