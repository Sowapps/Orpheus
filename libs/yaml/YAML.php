<?php
use Orpheus\Config\Config;

/**
 * The yaml class
 * 
 * This class is made to get YAML configuration.
 */
class YAML extends Config {

	protected static $extension = 'yaml';

	/**	Parse configuration from given source.
	 * @param $source An identifier or a path to get the source.
	 * @return The loaded configuration array.
	 *
	 * If an identifier, load a configuration from a .yaml file in CONFDIR.
	 * Else $source is a full path to the YAML configuration file.
	 */
	public static function parse($source) {
		$path	= static::getFilePath($source);
		return $path ? yaml_parse_file(static::getFilePath($source)) : array();
	}
	
}
/*
if( !class_exists('Config', false) ) {
	class Config extends YAML {}
}
*/
