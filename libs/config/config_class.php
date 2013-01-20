<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	//!	Loads new configuration source.
	/*!
		\param $source An identifier or a path to get the source.
	
		If an identifier, loads a configuration from a .ini file in CONFPATH.
		Else $source is a full path to the ini configuration file.
	*/
	public function load($source) {		
		// Full path given
		if( is_readable($source) ) {
			$confPath = $source;
			
		// File in configs folder
		} else if( is_readable(CONFPATH.$source.'.ini') ) {
			$confPath = CONFPATH.$source.'.ini';
			
		/// File not found
		} else {
			return array();
		}
		$parsed = parse_ini_file($confPath, true);
		$this->add($parsed);
		return $parsed;
	}
	
}
