<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class YAML extends ConfigCore {
	
	const EXT = 'yaml';
	
	//!	Loads configuration from new source.
	/*!
		\param $source An identifier or a path to get the source.
		\return The loaded configuration array.
	
		If an identifier, loads a configuration from a .yaml file in CONFPATH.
		Else $source is a full path to the YAML configuration file.
	*/
	public function load($source) {		
		// Full path given
		if( is_readable($source) ) {
			$confPath = $source;
			
		// File in configs folder
		} else if( is_readable(CONFPATH.$source.'.'.self::EXT) ) {
			$confPath = CONFPATH.$source.'.'.self::EXT;
			
		/// File not found
		} else {
			return array();
		}
		$parsed = yaml_parse_file($confPath);
		$this->add($parsed);
		return $parsed;
	}
	
}

if( !class_exists('Config', false) ) {
	class Config extends YAML {}
}
