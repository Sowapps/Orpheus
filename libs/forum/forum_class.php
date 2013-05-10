<?php
//! The inlay class for contents blocks
/*!
 *
 * Require core and publisher plugin.
 */

class Forum extends AbstractStatus {

	//Attributes
	protected static $table = 'forums';
	protected static $fields = array(
		'name', 'user_id', 'user_name',
		'create_time', 'create_ip', 'edit_time', 'edit_ip'
	);
	protected static $editableFields = array('name', 'user_id', 'user_name');
	protected static $validator = array(
		'name'			=> 'checkName',
		'user_id'		=> 'checkUserID',
		'user_name'		=> 'checkUserName'
	);
	protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));

	// *** OVERRIDDEN METHODS ***
	
	// *** DEV METHODS ***
	
	// *** STATIC METHODS ***
	
	// 		** VALIDATION METHODS **
	
	//! Checks a name
	/*!
	 * \param $inputData The input data from the user.
	 * \return The stripped name.
	 * 
	 * Validates the name field in array $inputData.
	 */
	public static function checkName($inputData, $ref) {
		if( empty($inputData['name']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidName');
		}
		return strip_tags($inputData['name']);
	}
	
	//! Checks a user id
	/*!
	 * \param $inputData The input data from the user.
	 * \return The user id as integer.
	 * 
	 * Validates the user_id field in array $inputData.
	 */
	public static function checkUserID($inputData, $ref) {
		if( !isset($inputData['user_id']) || !is_ID($inputData['user_id']) ) {
			if( !isset($inputData['user_id']) && isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidUserID');
		}
		return (int) $inputData['user_id'];
	}
	
	//! Checks a user name
	/*!
	 * \param $inputData The input data from the user.
	 * \return The stripped user name.
	 * 
	 * Validates the user_name field in array $inputData.
	 */
	public static function checkUserName($inputData, $ref) {
		if( empty($inputData['user_name']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidUserName');
		}
		return strip_tags($inputData['user_name']);
	}
}