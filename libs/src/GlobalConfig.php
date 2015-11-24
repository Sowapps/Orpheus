<?php

class GlobalConfig {
	
	protected $path	= DYNCONFIGPATH;
	protected $data;
	
	protected function __construct() {
		$this->data	= array();
		if( is_readable($this->path) ) {
			$this->data	= json_decode(file_get_contents($this->path), true);
			debug();
		}
	}

	public function asArray() {
		return $this->data;
	}

	public function preset($key, $default) {
		if( !$this->has($key) ) {
			$this->set($key, $default);
		}
	}

	public function has($key) {
		return isset($this->data[$key]);
	}
	public function get($key, $default=null) {
		return $this->has($key) ? $this->data[$key] : $default;
	}

	public function set($key, $value) {
		$this->data[$key]	= $value;
	}

	public function remove($key) {
		unset($this->data[$key]);
	}

	public function save() {
		return file_put_contents($this->path, json_encode($this->data));
	}

	protected static $instance;
	
	/**
	 * @return GlobalConfig
	 */
	public static function instance() {
		if( !static::$instance ) {
			static::$instance	= new static();
		}
		return static::$instance;
	}
	
}
