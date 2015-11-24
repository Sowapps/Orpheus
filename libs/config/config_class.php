<?php
/** The config class
 * This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	protected static $extension = 'ini';

	/**	Load configuration from new source.
	 * @param $source An identifier or a path to get the source.
	 * @return The loaded configuration array.
	 * 
	 * If an identifier, loads a configuration from a .ini file in CONFDIR.
	 * Else $source is a full path to the ini configuration file.
	 */
// 	public function load($source) {
// // 		// Full path given
// // 		if( is_readable($source) ) {
// // 			$confPath = $source;
			
// // 		// File in configs folder
// // 		} else {
// // 			$confPath = static::getFilePath($source);
// // 			if( empty($confPath) ) { return false; }
// // 		}
// // 		$parsed = parse_ini_file($confPath, true);
// 		$parsed = parse_ini_file(static::getFilePath($source), true);
// 		$this->add($parsed);
// 		return true;
// 	}

	/**	Parse configuration from given source.
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

	/**	Checks if configuration source exists
	 * @param $source An identifier to check the source.
	 * 
	 * Checks the configuration from the source is available.
	 */
// 	public function checkSource($source) {
// 		try {
// 			return !!static::getFilePath($source);
// // 			return is_readable($source) || is_readable(static::getFilePath($source));
// 		} catch( Exception $e ) {
// 			return false;
// 		}
// 	}
}
