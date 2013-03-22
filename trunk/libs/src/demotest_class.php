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
	
	// *** OVERLOADED METHODS ***
		
	//! Updates this DemoTest object
	/*!
		\sa PermanentObject::update()
	*/
	public function update($uInputData, array $data=array()) {
		try {
			$inputData['name'] = self::checkName($uInputData);
			if( $inputData['name'] != $this->name ) {
				$data['name'] = $inputData['name'];
			}
		} catch(UserException $e) { reportError($e); }
		
		return parent::update($uInputData, $data);
	}
	
	// *** STATIC METHODS ***
	
	
	// 		** CHECK METHODS **
	
	//! Checks Field 'name'
	/*!
		\param $inputData The user input.
		\return a valid field 'name'.
	*/
	public static function checkName($inputData) {
		if( empty($inputData['name']) ) {
			throw new UserException('emptyName');
		}
		return strip_tags($inputData['name']);
	}
	
	//! Checks user input
	/*!
		\sa PermanentObject::checkUserInput()
	*/
	public static function checkUserInput($uInputData) {
		$data = parent::checkUserInput($uInputData);
	
		$data['name'] = self::checkName($uInputData);
	
		return $data;
	}
	
	//! Checks for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data) {
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