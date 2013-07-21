<?php

interface SessionInterface {
	
	public function sessID();
	
	public function writeData($data);
	
	public function readData();
	
	public function save();
	
	public static function build($session_id);
	
	public static function getBySessID($session_id);
	
	public static function deleteBySessID($session_id);
	
	public static function deleteFrom($delay);
}
