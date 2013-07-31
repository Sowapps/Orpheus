<?php
//! The site user class
/*!
 * A site user is a registered user.
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
	protected static $editableFields = array(
		'fullname'
	);
	
	// *** OVERLOADED METHODS ***
	
	public function __toString() {
		return escapeText($this->fullname);
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}

	// 		** CHECK METHODS **

	public static function checkFullName($inputData) {
		if( empty($inputData['fullname']) ) {
			throw new UserException('invalidFullName');
		}
		return strip_tags($inputData['fullname']);
	}
	
	public static function checkUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
		$data = parent::checkUserInput($uInputData, $fields, $ref, $errCount);
		if( !empty($uInputData['password']) ) {
			$data['real_password'] = $uInputData['password'];
		}
		return $data;
	}
}
SiteUser::init();
