<?php
class FSCache {

	protected $path;
	protected $editTime;

	protected static $ext='.cache';
	protected static $delim='|';

	public function __construct($class, $name, $editTime=null) {
		$this->editTime = $editTime;
		$this->path = static::getFilePath($class, $name);
		$folder = static::getFolderPath($class);
		if( !is_dir($folder) && !mkdir($folder, 0777, true) ) {
			throw new Exception('unwritableClassFolder');
		}
	}

	public function get(&$cached) {
		if( !is_readable($this->path) ) {
			return false;
		}
		list($editTime, $type, $data) = explodeList(static::$delim, file_get_contents($this->path), 3);
		if( isset($this->editTime) && $editTime != $this->editTime ) {
			return false;
		}
		if( $type != 'scalar' ) {
			$data =  json_decode($data, true);
			if( $type == 'object' ) {
				$data =  (object) $data;
			} else if( $type != 'array' ) {
				$data = $type::cast($data);
			}
		}
		$cached = $data;
		return true;
	}

	public function set($data) {
		$type = 'scalar';
		if( !is_scalar($data) ) {
			if( empty($data) ) {
				$data = array();
			}
			if( is_object($data) ) {
				// If castable, we will recreate object when getting it
				// Else we will return a stdClass Object.
				text(class_uses($data));
				$type = array_key_exists('cast', class_uses($data)) ? get_class($data) : 'object';
			} else if( is_array($data) ) {
				$type = 'array';
			} else {
				// Not compatible type
				return false;
			}
			$data = json_encode((array) $data);
		}
		return file_put_contents($this->path, $this->editTime.static::$delim.$type.static::$delim.$data);
	}

	public static function getFolderPath($class) {
		return CACHEPATH.$class.'/';
	}

	public static function getFilePath($class, $name) {
		return static::getFolderPath($class).$name.static::$ext;
	}
}