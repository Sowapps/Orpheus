<?php

/** The cache interface
 * The interface to use to define a cache class.
 */
interface Cache {
	
	/** Gets the cache for the given parameters
	 * @param $cached The output to get the cache
	 * @return True if cache has been retrieved
	 * 
	 * The type should preserved, even for objects.
	 */
	public function get(&$cached);
	
	/** Sets the cache for the given parameters
	 * @param $data The data to put in the cache
	 * @return True if cache has been saved
	 */
	public function set($data);
}