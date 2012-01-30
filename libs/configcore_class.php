<?php
abstract class ConfigCore {
	
	protected static $main;
	
	protected $config = array();
	
	public function __get($key) {
		return (isset($this->config[$key])) ? $this->config[$key] : NULL;
	}
	
	public function add($conf) {
		$this->config += $conf;
	}
	
	public static function build($source, $minor=false) {
		$newConf = new static();
		if( !$minor ) {
			if( !isset(static::$main) ) {
				static::$main = $newConf;
				$GLOBALS['CONFIG'] = &$main;
			}
			static::$main->load($source);
		} else {
			$newConf->load($source);
		}
	}
	
	public static function get($key) {
		if( !isset(static::$main) ) {
			throw new Exception('No Main Config');
		}
		return static::$main->$key;
	}
	
	public abstract function load($source);
}
