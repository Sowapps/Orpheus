<?php
class Hook {
	protected static $hooks = array();
	
	protected $name;
	protected $callbacks = array();
	
	
	protected function __construct($name) {
		$this->name = $name;
	}
	
	public function registerHook($callback) {
		if( !is_callable($callback) ) {
			throw new Exception('Callback not callable');
		}
		if( in_array($callback, $this->callbacks) ) {
			throw new Exception('Callback already registered');
		}
	}
	
	public function triggerHook() {
		foreach($this->callbacks as $callback) {
			call_user_func($callback);
		}
	}
	
	protected static function slug($name) {
		return strtolower($name);
	}
	
	public static function create($name) {
		$name = static::slug($name);
		static::$hook[$name] = new static($name);
		return self::$hook[$name];
	}
	
	public static function register($name, $callback) {
		$name = static::slug($name);
		if( empty(static::$hook[$name]) ) {
			throw new Exception('No hook with this name');
		}
		return static::$hook[$name]->registerHook($callback);
	}
	
	public static function trigger($name) {
		$name = static::slug($name);
		if( empty(static::$hook[$name]) ) {
			throw new Exception('No hook with this name');
		}
		return static::$hook[$name]->triggerHook();
	}
}