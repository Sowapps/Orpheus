<?php

/**
 * Trait to cast an array to an object
 * @author Florent HAZARD
 * @class Cast
 */
trait Cast {

	/**
	 * Cast input to fill the current instance ($this)
	 * @param	$input mixed Input to cast
	 * @return	boolean True in case of success, false if nothing done
	 * @memberof Cast
	 * 
	 * The default behavior is to cast array into the current object, but you can use it to import other objects
	 */
	public function castFrom($input) {
		if( is_scalar($input) ) {
			return false;
		}
		if( empty($input) ) {
			$input =  array();
		} else if( is_object($input) ) {
			$input = (array) $input;
		}
		foreach( $input as $prop => $value ) {
			$prop = explode("\0", $prop);
			$prop = $prop[count($prop)-1];
			$this->$prop = $value;
		}
		return true;
	}

	/**
	 * Cast input to a new instance of the current class (static)
	 * @param	$input mixed Input to cast
	 * @return	NULL|Cast A new instance or null if it failed
	 * @memberof Cast
	 */
	public static function cast($input) {
		$obj = new static();
		if( !$obj->castFrom($input) ) {
			return null;
		}
		return $obj;
	}
}