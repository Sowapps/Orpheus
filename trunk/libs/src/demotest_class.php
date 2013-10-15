<?php
//! A sample demo test class
/*!
	Example of how to use the permanent object.
*/
class DemoTest extends PermanentObject {
	
	//Attributes
	protected static $table = 'test';
	protected static $fields = array('id', 'name');
	
	protected static $editableFields = array('name');
	
	protected static $validator = array('name'=>'checkName');
	
	// *** OVERLOADED METHODS ***
	
	// *** STATIC METHODS ***
	
	
	// 		** CHECK METHODS **
	
	//! Checks Field 'name'
	/*!
	 * \param $inputData The user input.
	 * \param $ref The reference to check the field from.
	 * \return a valid field 'name'.
	 */
	public static function checkName($inputData, $ref=null) {
		if( empty($inputData['name']) ) {
			if( is_null($ref) ) {// Updating
				return null;
			}
			throw new UserException('emptyName');
		}
		return strip_tags($inputData['name']);
	}
	
	//! Checks for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data, $ref=null) {
		if( empty($data['name']) ) {
			return;//Nothing to check.
		}
		$options = array(
			'number'=> 1,
			'where'	=> 'name='.SQLAdapter::quote($data['name']),
		);
		$data = static::get($options);
		if( empty($data) ) {
			return;//No data got
		}
		throw new UserException("existingObject");
	}
}
?>