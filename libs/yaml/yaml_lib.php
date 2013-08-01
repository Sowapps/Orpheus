<?php
//! The YAML class
/*!
	This class is made to get YAML configuration.
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
		} else if( is_readable(static::getFilePath($source)) ) {
			$confPath = static::getFilePath($source);
			
		/// File not found
		} else {
			return array();
		}
		$parsed = yaml_parse_file($confPath);
		$this->add($parsed);
		return $parsed;
	}

	//!	Gets the file path
	/*!
		\param $source An identifier to get the source.
		\return The configuration file path according to Orpheus file are organized.
	
		Gets the configuration file path in CONFPATH.
	*/
	public static function getFilePath($source) {
		return CONFPATH.$source.'.'.self::EXT;
	}
	
}

if( !class_exists('Config', false) ) {
	class Config extends YAML {}
}
