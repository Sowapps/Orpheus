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
		return $this->fullname;
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
}
SiteUser::init();
