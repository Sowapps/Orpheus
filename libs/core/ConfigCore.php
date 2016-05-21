<?php
/**
 * The config core class
 * This class is the core for config classes inherited from custom configuration.
 */
abstract class ConfigCore {
	
	//! Contains the main configuration, reachable from everywhere.
	protected static $main;
	protected static $caching = true;
	
	/**
	 * Contains the configuration for this Config Object.
	 * Must be inherited from ConfigCore.
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * Get this config as array
	 * @return array
	 */
	public function asArray() {
		return $this->config;
	}
	
	/** The magic function to get config
	 * @param $key The key to get the value.
	 * @return A config value.
	 * 
	 * Returns the configuration item with key $key.
	 * Except for:
	 * - 'all' : It returns an array containing all configuration items.
	*/
	public function __get($key) {
		if( $key == 'all' ) {
			return $this->asArray();
		}
		return apath_get($this->config, $key);
	}
	
	/** The magic function to set config
	 * @param $key The key to set the value.
	 * @param $value The new config value.
	 * 
	 * Sets the configuration item with key $key.
	 * Except for:
	 * - 'all' : It sets all the array containing all configuration items.
	*/
	public function __set($key, $value) {
		if( $key == 'all' && is_array($value) ) {
			$this->config = $value;
			return;
		}
		if( isset($this->config[$key]) ) {
			$this->config[$key] = $value;
		}
	}
	
	/** Magic isset
	 * @param $key Key of the config to check is set
	 * 
	 * Checks if the config $key is set.
	*/
	public function __isset($key) {
        return isset($this->config[$key]);
	}
	
	/** Add configuration to this object
	 * @param $conf The configuration array to add to the current object.
	 * 
	 * Add the configuration array $conf to this configuration.
	 */
	public function add($conf) {
		if( empty($conf) ) { return ; }
		$this->config = array_merge($this->config, $conf);
	}
	
	/**
	 * Load new configuration from source
	 * 
	 * @param $source An identifier to get the source
	 * @param $cached True if this configuration should be cached
	 * @return boolean True if this configuration was loaded successfully
	 * 
	 * Load a configuration from a source identified by $source.
	 */
	public function load($source, $cached=true) {
		try {
			if( class_exists('FSCache', true) ) {
// 				debug('Cache class exists');
				// strtr fix an issue with FSCache, FSCache does not allow path, so no / and \ 
// 				debug('Config time for '.$source.' is '.sqlDatetime(filemtime(static::getFilePath($source))));
				$cache	= new FSCache('config', strtr($source, '/\\', '--'), filemtime(static::getFilePath($source)));
				if( !static::$caching || !$cached || !$cache->get($parsed) ) {
// 					debug('No cache, parsing config');
					$parsed	= static::parse($source);
// 					debug('Config parsed', $parsed);
					$cache->set($parsed);
// 					debug('Cache set');
				}
			} else {
				$parsed	= static::parse($source);
			}
// 			debug('$parsed', $parsed);
			$this->add($parsed);
			return true;
			
		} catch( CacheException $e ) {
			log_error($e, 'Caching parsed source '.$source, false);
			
		} catch( Exception $e ) {
			// If not found, we do nothing
			log_error($e, 'Caching parsed source '.$source, false);
		}
		return false;
	}

	/**	Check if source is available
	 * @param string $source An identifier to get the source.
	 * @return boolean True if source is available
	 */
	public function checkSource($source) {
		try {
			return !!static::getFilePath($source);
// 			return is_readable($source) || is_readable(static::getFilePath($source));
		} catch( Exception $e ) {
			return false;
		}
	}
	
	/**	Builds new configuration source
	 * @param $source An identifier to build the source.
	 * @param $minor True if this is a minor configuration.
	 * @param $cached True if this configuration should be cached.
	 * 
	 * Builds a configuration from $source using load() method.
	 * If it is not a minor configuration, that new configuration is added to the main configuration.
	 */
	public static function build($source, $minor=false, $cached=true) {
		if( !$minor ) {
			if( !isset(static::$main) ) {
				static::$main = new static();
				$GLOBALS['CONFIG'] = &static::$main;
			}
			static::$main->load($source, $cached);
			return static::$main;
		}
		$newConf = new static();
		$newConf->load($source, $cached);
		return $newConf;
	}
	
	/** Gets configuration from the main configuration object
	 * @param $key The key to get the value.
	 * @param $default The default value to use.
	 * @return string A config value.
	 * 
	 * Calls __get() method from main configuration object.
	*/
	public static function get($key, $default=null) {
		if( !isset(static::$main) ) {
			return $default;
			//throw new Exception('No Main Config');
		}
// 		debug('static::$main', static::$main);
		return isset(static::$main->$key) ? static::$main->$key : $default;
	}
	
	/** Set configuration to the main configuration object
	 * @param $key The key to set the value.
	 * @param $value The new config value.
	 * 
	 * Call __set() method to main configuration object.
	*/
	public static function set($key, $value) {
		if( !isset(static::$main) ) {
			throw new Exception('No Main Config');
		}
		static::$main->$key = $value;
	}

	protected static $repositories	= array();
	
	/** Add a repository to load configs
	 * @param mixed $repos The repository to add. Commonly a path to a directory.
	*/
	public static function addRepository($repos) {
		static::$repositories[]	= $repos;
	}
	
	/** Add a repository library to load configs
	 * @param string $library The library folder
	*/
	public static function addRepositoryLibrary($library) {
		static::addRepository(pathOf(LIBSDIR.$library).CONFDIR);
	}

	/**	Get the file path
	 * @param string $source An identifier to get the source.
	 * @return array The configuration file path according to how Orpheus files are organized.
	 * 
	 * Get the configuration file path in CONFDIR.
	*/
	public static function getFilePath($source) {
		if( is_readable($source) ) {
			return $source;
		}
		$configFile	= $source.'.'.static::$extension;
		foreach( static::$repositories as $repos ) {
			if( is_readable($repos.$configFile) ) {
				return $repos.$configFile;
			}
		}
// 		if( is_readable($source) ) {
// 			return $source;
// 		}
// 		if( is_readable(static::getFilePath($source)) ) {
// 			$confPath = static::getFilePath($source);
// 			if( empty($confPath) ) {
// 				return false;
// 			}
		return pathOf(CONFDIR.$configFile, true);
	}


	/**	Parse configuration from given source.
	 * @param $source An identifier or a path to get the source.
	 * @return The loaded configuration array.
	 */
	public static function parse($source) {
		throw new Exception('The class "'.get_called_class().'" should override the `parse()` static method from "'.get_class().'"');
	}
	
	public static function isCaching() {
		return self::$caching;
	}
	
	public static function setCaching($caching) {
		self::$caching = $caching;
	}
}
