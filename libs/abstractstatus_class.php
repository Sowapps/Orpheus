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
	protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array('id', 'status');

	// *** METHODES SURCHARGEES ***
	
	// *** METHODES UTILISATEUR ***
	
	//! Gets available status.
	/*!
	 * \return An array of the available statuses.
	 * 
	 * Gets all available status for the current one.
	 */
	public function getAvailableStatus() {
		return static::$status[$this->status];
	}
	
	// *** METHODES STATIQUES ***
	
	// 		** METHODES DE VERIFICATION **
	
	//! Checks user input
	/*!
	 * \sa PermanentObject::checkUserInput()
	 */
	public static function checkUserInput($uInputData) {
		$data = array();
		//only for create.
		try {
			if( !isset($uInputData['status']) ) {
				throw new Exception();//Juste pour le catch.
			}
			$data['status'] = self::checkStatus($uInputData['status']);
		} catch(Exception $e) {
			$data['status'] = static::getDefaultStatus();
		}
		return $data;
	}
	
	// *** STATUS METHODS ***
	
	//! Gets and sets the status of this object
	/*!
	 * \param $newStatus The new status to set. Default value is null.
	 * \return The status of this object.
	 * 
	 * Gets the status of this object.
	 * Sets it if $newStatus is null.
	 * The return value is the final one.
	 */
	public function status($newStatus=null) {
		if( isset($newStatus) ) {
			static::checkStatus($newStatus, $this->status);
			$this->setData('status', $newStatus);
		}
		return $this->status;
	}
	
	//! Checks a status
	/*!
	 * \param $newStatus The new status to set.
	 * \param $currentStatus The current status. Default value is null.
	 * \return The $newStatus.
	 * 
	 * Checks the $newStatus.
	 * If $currentStatus is null, its consider that the objet haven't it, like new one.
	 */
	public static function checkStatus($newStatus, $currentStatus=null) {
		if( empty($newStatus) ) {
			throw new UserException('invalidStatus');
		}
		if( !isset(static::$status[$newStatus]) ) {
			throw new UserException('unknownStatus');
		}
		//If not new, we check the current status can set to this one.
		if( isset($currentStatus) && !in_array($newStatus, static::$status[$currentStatus]) ) {
			throw new UserException('unavailableStatus');
		}
		return $newStatus;
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
?>