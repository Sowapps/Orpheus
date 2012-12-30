<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	//!	Loads new configuration source.
	/*!
		\param $source An identifier to get the source.
	
		Loads a configuration from a .ini file in CONFPATH.
	*/
	public function load($source) {
		if( !is_readable(CONFPATH.$source.'.ini') ) {
			return array();
		}
		$parsed = parse_ini_file(CONFPATH.$source.'.ini', true);
		$this->add($parsed);
		return $parsed;
	}
	
}
