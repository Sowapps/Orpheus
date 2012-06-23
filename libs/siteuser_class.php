<?php
//! The abstract status class
/*!
 * An "user" is a registered user.
 * 
 * Require:
 * is_id()
 * is_email()
 * pdo_query()
 * 
 */

class SiteUser extends User {
	
	//Attributes
	protected static $fields = array(
		'fullname'
	);
	protected static $userEditableFields = array(
		'fullname'
	);

	// *** METHODES SURCHARGEES ***
	
	public function __toString() {
		return $this->fullname;
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}
	
	public function update($uInputData, array $data=array()) {
		
		try {
			$inputData['fullname'] = self::checkFullName($uInputData);
			if( $inputData['fullname'] != $this->fullname ) {
				$data['fullname'] = $inputData['fullname'];
			}
		} catch(UserException $e) { addUserError($e); }
		
//		if( !empty($data['name']) ) {
//			$name = $data['name'];
//		}
		
		$r = parent::update($uInputData, $data);
//		if( $r && isset($name) && $name == ) {
//			
//		}
		return $r;
	}
	
	public function runForUpdate() {
	}
	
	// 		** METHODES DE VERIFICATION **

	public static function checkFullName($inputData) {
		if( empty($inputData['fullname']) ) {
			throw new UserException('invalidFullName');
		}
		return strip_tags($inputData['fullname']);
	}
	
	public static function checkUserInput($uInputData) {
		$data = parent::checkUserInput($uInputData);
		$data['fullname'] = static::checkFullName($uInputData);
		return $data;
	}
	
	public static function init() {
		self::$fields = array_unique(array_merge(self::$fields,parent::$fields));
		self::$userEditableFields = array_unique(array_merge(self::$userEditableFields,parent::$userEditableFields));
	}
}
SiteUser::init();
?>
