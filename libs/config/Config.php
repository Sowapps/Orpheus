<?php
/** The config class
 * This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	protected static $extension = 'ini';

	/**
	 * Parse configuration from given source.
	 * 
	 * @param $source An identifier or a path to get the source.
	 * @return The loaded configuration array.
	 *
	 * If an identifier, loads a configuration from a .ini file in CONFDIR.
	 * Else $source is a full path to the ini configuration file.
	 */
	public static function parse($source) {
		$path	= static::getFilePath($source);
		return $path ? parse_ini_file($path, true) : array();
	}
}
