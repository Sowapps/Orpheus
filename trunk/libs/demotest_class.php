<?php
//! A sample demo test class
/*!
	Use permanent object.
*/
class DemoTest extends PermanentObject {
	
	//Attributes
	protected static $table = 'test';
	protected static $fields = array('id', 'name');
	protected static $userEditableFields = array('name');
	
	// *** OVERLOADED METHODS ***
		
	//! Update this DemoTest object
	/*!
		\sa PermanentObject::update()
	*/
	public function update($uInputData, array $data=array()) {
		try {
			$inputData['name'] = self::checkName($uInputData);
			if( $inputData['name'] != $this->name ) {
				$data['name'] = $inputData['name'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		return parent::update($uInputData, $data);
	}
	
	// *** STATIC METHODS ***
	
	
	// 		** CHECK METHODS **
	
	//! Check Field 'name'
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
	
	//! Check user input
	/*!
		\sa PermanentObject::checkUserInput()
	*/
	public static function checkUserInput($uInputData) {
		$data = parent::checkUserInput($uInputData);
	
		$data['name'] = self::checkName($uInputData);
	
		return $data;
	}
	
	//! Check for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data) {
		if( empty($data['name']) ) {
			return;//Nothing to check.
		}
		$options = array(
			'number'=> 1,
			'where'	=> 'name='.SQLMapper::quote($data['name']),
		);
		$data = static::get($options);
		if( empty($data) ) {
			return;//No data got
		}
		throw new UserException("existingObject");
	}
}
?>