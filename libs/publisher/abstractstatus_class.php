<?php
//! The abstract status class
/*!
 * Abstract class implementing a status system to the PermanentObject class.
 * It makes the object as "statuable", it can own a managed status.
 *
 * Require core plugin
 * 
 * Statuable require:
 * - $status attribute to determine known statuses with first status in first.
 * - field 'status' VARCHAR(20)
 * 
 * Common example for publications:
 * private static $status = array('draft'=>array('waiting'), 'waiting'=>array('approved', 'rejected'), 'approved'=>array('rejected'), 'rejected'=>array('approved'));
 * Here default is 'draft' (the first in the list), 'draft' only unlock 'waiting' status (waiting for moderation).
 */
abstract class AbstractStatus extends PermanentObject {
	
	//Attributes
	protected static $fields = array('id', 'status');
	protected static $editableFields = array('status');
	protected static $validator = array('status'=>'checkStatus');
	
	protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	
	// *** OVERRIDDEN METHODS ***
	
	// *** DEV METHODS ***
	
	//! Gets available status.
	/*!
	 * \return An array of the available statuses.
	 * 
	 * Gets all available status for the current one.
	 */
	public function getAvailableStatutes() {
		return static::$status[$this->status];
	}
	
	//! Gets and sets the status of this object
	/*!
	 * \param $newStatus The new status to set. Default value is null.
	 * \return The status of this object.
	 * 
	 * Gets the status of this object.
	 * Sets it if $newStatus is not null.
	 * The return value is the final one.
	 */
	public function status($newStatus=null) {
		if( isset($newStatus) ) {
			static::validateStatus($newStatus, $this);
			$this->setValue('status', $newStatus);
			if( in_array('status_time', static::$fields) ) {
				static::logEvent('status');
			}
		}
		return $this->status;
	}
	
	//! Checks if current object has this status
	/*!
	 * \param $status The status to check. Could be an array of status to check.
	 * \return True if $status is equals to the current one.
	 * 
	 * Checks if current object has this status.
	 */
	public function hasStatus($status) {
		if( is_array($status) ) {
			foreach( $status as $s ) {
				if( $this->hasStatus($s) ) {
					return true;
				}
			}
			return false;
		}
		return $this->status == $status;
	}
	
	// *** METHODES STATIQUES ***
	
	// 		** METHODES DE VERIFICATION **
	
	//! Validates a status
	/*!
	 * \param $newStatus The new status to set
	 * \param $ref The reference to check the status from
	 * \return The $newStatus.
	 * 
	 * Checks the $newStatus.
	 * If $currentStatus is null, it considers that the objet haven't it, like new one.
	 */
	public static function validateStatus($newStatus, $ref=null) {
		if( empty($newStatus) || !is_scalar($newStatus) ) {
			static::throwException('invalidStatus');
		}
		if( !isset(static::$status[$newStatus]) ) {
			static::throwException('unknownStatus');
		}
		//If not new, we check the current status can set to this one.
		if( isset($ref) && !$ref->hasStatus($newStatus) && !in_array($newStatus, $ref->getAvailableStatutes()) ) {
			static::throwException('unavailableStatus');
		}
		return $newStatus;
	}
	
	//! Checks a status
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the status from
	 * \return The status.
	 * \see validateStatus()
	 * 
	 * Uses validateStatus() to validate field 'status'.
	 */
	public static function checkStatus($inputData, $ref) {
		// When creating whe set to the default
		if( !isset($ref) ) {
			return static::getDefaultStatus();
		}
		if( !isset($inputData['status']) ) {
			return null;
		}
		return static::validateStatus($inputData['status'], $ref);
	}
	
	//! Gets the default status
	/*!
	 * \return The default status.
	 * 
	 * Gets the default status, this is the first of the defined list.
	 */
	public static function getDefaultStatus() {
		return key(static::$status);
	}
}
AbstractStatus::init();
