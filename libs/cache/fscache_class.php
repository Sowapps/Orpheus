<?php

//! The file system cache class
/*!
 * Uses File System to cache data.
 * This class is useful for dated data.
 * This class requires a CACHEPATH constant containing the path to the cache folder, you can also override getFolderPath() to determine the path by another way.
 */
class FSCache implements Cache {

	protected $path;
	protected $editTime;

	protected static $ext='.cache';
	protected static $delim='|';
	
	//! Constructor
	/*!
	 * \param $class The class of the cache
	 * \param $name The name of this cache
	 * \param $editTime The last modification time of the cache. Default value is 0 (undefined).
	 */
	public function __construct($class, $name, $editTime=null) {
		$this->editTime = $editTime;
		$this->path = static::getFilePath($class, $name);
		$folder = static::getFolderPath($class);
		if( !is_dir($folder) && !mkdir($folder, 0777, true) ) {
			throw new Exception('unwritableClassFolder');
		}
	}
	
	//! Gets the cache for the given parameters
	/*!
	 * \param $cached The output to get the cache
	 * \return True if cache has been retrieved
	 *
	 * This method serializes the data in the file using json_encode().
	 * The type is preserved, even for objects.
	 */
	public function get(&$cached) {
		if( !is_readable($this->path) ) {
			return false;
		}
		list($editTime, $type, $data) = explodeList(static::$delim, file_get_contents($this->path), 3);
		if( isset($this->editTime) && $editTime != $this->editTime ) {
			return false;
		}
		if( $type != 'scalar' ) {
			$data =  json_decode($data, true);
			if( $type == 'object' ) {
				$data =  (object) $data;
			} else if( $type != 'array' ) {
				$data = $type::cast($data);
			}
		}
		$cached = $data;
		return true;
	}
	
	//! Sets the cache for the given parameters
	/*!
	 * \param $data The data to put in the cache
	 * \return True if cache has been saved
	 *
	 * This method unserializes the data in the file using json_decode().
	 * The type is saved too.
	 */
	public function set($data) {
		$type = 'scalar';
		if( !is_scalar($data) ) {
			if( empty($data) ) {
				$data = array();
			}
			if( is_object($data) ) {
				// If castable, we will recreate object when getting it
				// Else we will return a stdClass Object.
				$type = array_key_exists('cast', class_uses($data)) ? get_class($data) : 'object';
			} else if( is_array($data) ) {
				$type = 'array';
			} else {
				// Not compatible type
				return false;
			}
			$data = json_encode((array) $data);
		}
		return file_put_contents($this->path, $this->editTime.static::$delim.$type.static::$delim.$data);
	}
	
	//! Gets the folder path for the cache
	/*!
	 * \param $class The class to use
	 * \return The path of this cache folder in the global cache folder.
	 */
	public static function getFolderPath($class) {
		return CACHEPATH.$class.'/';
	}
	
	//! Gets the fle path of this cache
	/*!
	 * \param $class The class to use
	 * \param $name The name to use
	 * \return The path of this cache file.
	 */
	public static function getFilePath($class, $name) {
		return static::getFolderPath($class).$name.static::$ext;
	}
}