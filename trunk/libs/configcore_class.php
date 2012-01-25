<?php
abstract class ConfigCore {
	
	protected static $main;
	
	protected $config;
	
	public function __get($key) {
		return (isset($this->config[$key])) ? $this->config[$key] : NULL;
	}
	
	public static function build($source, $minor=false) {
		if( !$minor ) {
			if( !isset(static::$main) ) {
				static::$main = $this;
			}
			static::$main->add(static::load($source));
		} else {
			$this->add(static::load($file));
		}
	}
	
	public function get($key) {
		if( !isset(static::$main) ) {
			throw new Exception('No Main Config');
		}
		return static::$main->$key;
	}
	
	public abstract static function load($source);
}