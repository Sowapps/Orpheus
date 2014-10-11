<?php
/** The abstract status class
 * Abstract class implementing a status system to the PermanentObject class.
 * It makes the object as "statuable", it can own a managed status.
 * This class now allows multiple status fields, the default one is 'status'.
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
// 	protected static $status = array('status'=>array('approved'=>array('rejected'), 'rejected'=>array('approved')));
	
	// *** OVERRIDDEN METHODS ***
	
	// *** DEV METHODS ***
	
	/** Checks if current object can reach the given status.
	 * @param $status The status the current object should reach.
	 * @param $field The field to check the status. Default value is 'status'.
	 * @return True if the object can currently validate this status.
	 * 
	 * Checks if current object can reach the given status.
	 */
	public function canReachStatus($status, $field='status') {
		return in_array($status, $this->getAvailableStatutes($field));
	}
	
	/** Gets available status.
	 * @param $field The field to check the status. Default value is 'status'.
	 * @return An array of the available statuses.
	 * 
	 * Gets all available status for the current one.
	 */
	public function getAvailableStatutes($field='status') {
		return isset(static::$status[$field]) ? static::$status[$field][$this->$field] : static::$status[$this->$field];
	}
	
	/** Gets and sets the status of this object
	 * @param $newStatus The new status to set. Default value is null.
	 * @param $field The field to get the status. Default value is 'status'.
	 * @return The status of this object.
	 * 
	 * Gets the status of this object.
	 * Sets it if $newStatus is not null.
	 * The return value is the final one.
	 */
	public function status($newStatus=null, $field='status') {
		if( isset($newStatus) ) {
			static::validateStatus($newStatus, $this, $field);
			$this->setValue($field, $newStatus);
			if( in_array($field.'_time', static::$fields) ) {
				static::logEvent($field);
			}
		}
		return $this->$field;
	}
	
	/** Checks if current object has this status
	 * @param $status The status to check. Could be an array of status to check.
	 * @param $field The field to compare the status. Default value is 'status'.
	 * @return True if $status is equals to the current one.
	 * 
	 * Checks if current object has this status.
	 */
	public function hasStatus($status, $field='status') {
		if( is_array($status) ) {
			foreach( $status as $s ) {
				if( $this->hasStatus($s, $field) ) {
					return true;
				}
			}
			return false;
		}
		return $this->$field == $status;
	}
	
	// *** METHODES STATIQUES ***
	
	// 		** METHODES DE VERIFICATION **
	
	/** Validates a status
	 * @param $newStatus The new status to set
	 * @param $ref The reference to check the status from
	 * @param $field The field to validate the status. Default value is 'status'.
	 * @return The $newStatus.
	 * 
	 * Checks the $newStatus.
	 * If $currentStatus is null, it considers that the objet haven't it, like new one.
	 */
	public static function validateStatus($newStatus, $ref=null, $field='status') {
		if( empty($newStatus) || !is_scalar($newStatus) ) {
			static::throwException('invalidStatus'.($field=='status' ? '' : '_'.$field));
		}
		if( (isset(static::$status[$field]) && !isset(static::$status[$field][$newStatus])) || (!isset(static::$status[$field]) && !isset(static::$status[$newStatus])) ) {
			static::throwException('unknownStatus'.($field=='status' ? '' : '_'.$field));
		}
		//If not new, we check the current status can set to this one.
		if( isset($ref) && !$ref->hasStatus($newStatus, $field) && !$ref->canReachStatus($newStatus, $field) ) {
			static::throwException('unavailableStatus'.($field=='status' ? '' : '_'.$field));
		}
		return $newStatus;
	}
	
	/** Checks a status
	 * @param $inputData The input data from the user.
	 * @param $ref The reference to check the status from
	 * @param $field The field to compare the status. Default value is 'status'.
	 * @return The status.
	 * @see validateStatus()
	 * 
	 * Uses validateStatus() to validate field 'status'.
	 */
	public static function checkStatus($inputData, $ref=null, $field='status') {
		// When creating we set to the default
		if( !isset($inputData[$field]) || !isset($inputData['admin-control']) ) { 
			if( !isset($ref) ) {
				return static::getDefaultStatus($field);
			}
			if( !isset($inputData[$field]) ) {
				return null;
			}
		}
		return static::validateStatus($inputData[$field], $ref, $field);
	}
	
	/** Gets the default status
	 * @param $field The field to get the default value. Default value is 'status'.
	 * @return The default status value for this field.
	 * 
	 * Gets the default status, this is the first of the defined list.
	 */
	public static function getDefaultStatus($field='status') {
		return key(isset(static::$status[$field]) ? static::$status[$field] : static::$status);
	}
}
AbstractStatus::init();
