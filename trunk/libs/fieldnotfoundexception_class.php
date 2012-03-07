<?php
//! The field not found exception class
/*!
	This exception is thrown when a field is not found in a set.
*/
class FieldNotFoundException extends Exception {
	
	private $fieldname;
	
	//! Constructor
	/*!
		\param $fieldname The name of the missing field.
	*/
	public function __construct($fieldname) {
		parent::__construct('fieldNotFound', 1001);
		$this->fieldname = (string) $fieldname;
	}
	
	//! Get the field name
	/*!
		\return The field name.
	*/
	public function getFieldName() {
		return $this->fieldname;
	}
}
