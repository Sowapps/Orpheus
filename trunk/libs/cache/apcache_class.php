<?php
class APCache {

	protected $key;
	protected $ttl;

	public function __construct($class, $name, $ttl=0) {
		$this->ttl = $ttl;
		$this->key = $class.'.'.$name;
	}

	public function get(&$cached) {
		$fc = apc_fetch($this->key, $success);
		if( $fc !== false ) {
			$cached = $fc;
		}
		return $success;
	}

	public function set($data) {
		return apc_store($this->key, $data, $this->ttl);
	}
}