<?php

/** The apc cache class
 * Uses APC feature to cache data.
 * This class is useful for perishable data.
 * So, it requires the APC lib to be installed on the server.
 * Look for php-apc package for Linux.
 * http://php.net/manual/en/book.apc.php
 */
class APCache implements Cache {

	protected $key;
	protected $ttl;
	
	/** Constructor
	 * @param $class The class of the cache
	 * @param $name The name of this cache
	 * @param $ttl The time to live, the delay the cache expires for. Default value is 0 (undefined).
	 */
	public function __construct($class, $name, $ttl=0) {
		$this->ttl = $ttl;
		$this->key = $class.'.'.$name;
		$this->get($cached);
	}
	
	/** Gets the cache for the given parameters
	 * @param $cached The output to get the cache
	 * @return True if cache has been retrieved
	 * 
	 * This method uses the apc_fetch() function.
	 * The type is preserved, even for objects.
	 */
	public function get(&$cached) {
		$fc = apc_fetch($this->key, $success);
		if( $fc !== false ) {
			$cached = $fc;
		}
		return $success;
	}
	
	/** Sets the cache for the given parameters
	 * @param $data The data to put in the cache
	 * @return True if cache has been saved
	 * 
	 * This method uses the apc_store() function.
	 */
	public function set($data) {
		return apc_store($this->key, $data, $this->ttl);
	}
}