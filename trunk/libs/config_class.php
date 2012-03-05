<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	//!	Load new configuration source.
	/*!
		\param $source An identifier to get the source.
	
		Load a configuration from a .ini file in CONFPATH.
	*/
	public function load($source) {
		$parsed = parse_ini_file(CONFPATH.$source.'.ini', true);
		$this->add($parsed);
		return $parsed;
	}
	
}
