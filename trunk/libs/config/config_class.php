<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	const EXT = 'ini';

	//!	Loads configuration from new source.
	/*!
		\param $source An identifier or a path to get the source.
		\return The loaded configuration array.
	
		If an identifier, loads a configuration from a .ini file in CONFDIR.
		Else $source is a full path to the ini configuration file.
	*/
	public function load($source) {
		// Full path given
		if( is_readable($source) ) {
			$confPath = $source;
			
		// File in configs folder
		} else {
			$confPath = static::getFilePath($source);
			if( empty($confPath) ) { return false; }
		}
		$parsed = parse_ini_file($confPath, true);
		$this->add($parsed);
		return true;
	}

	//!	Checks if configuration source exists
	/*!
		\param $source An identifier to check the source.
	
	Checks the configuration from the source is available.
	*/
	public function checkSource($source) {
		try {
			return is_readable($source) || is_readable(static::getFilePath($source));
		} catch( Exception $e ) {
			return false;
		}
	}

	//!	Gets the file path
	/*!
		\param $source An identifier to get the source.
		\return The configuration file path according to Orpheus file are organized.
	
		Gets the configuration file path in CONFDIR.
	*/
	public static function getFilePath($source) {
		return pathOf(CONFDIR.$source.'.'.self::EXT, true);
	}
}
