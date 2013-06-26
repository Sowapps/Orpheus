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
	public function getAvailableStatus() {
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
	
	//! Checks if this object has the given status
	/*!
	 * \param $status The status to compare
	 * \return True if the status is $status
	 * 
	 * Compares the given status to the current one.
	 */
	public function hasStatus($status) {
		return $this->status = $status;
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
		if( empty($newStatus) ) {
			throw new UserException('invalidStatus');
		}
		if( !isset(static::$status[$newStatus]) ) {
			throw new UserException('unknownStatus');
		}
		//If not new, we check the current status can set to this one.
		if( isset($ref) && !in_array($newStatus, static::$status[$ref->status]) ) {
			throw new UserException('unavailableStatus');
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
	//! Gets available statuses from the given one.
	/*!
	 * \return An array of the available statuses.
	 * 
	 * Gets all available statuses for the given one.
	 */
	public static function availableStatusesFrom($status) {
		return static::$status[$status];
	}
}
AbstractStatus::init();
