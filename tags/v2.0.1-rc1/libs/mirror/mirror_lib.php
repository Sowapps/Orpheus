<?php


trait Cast {

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

	public static function cast($input) {
		$obj = new static();
		if( !$obj->castFrom($input) ) {
			return null;
		}
		return $obj;
	}
}