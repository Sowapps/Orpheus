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
	
	public function getAvailableStatus() {
		return static::$status[$this->status];
	}
	
	// *** METHODES STATIQUES ***
	
	// 		** METHODES DE VERIFICATION **
	
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
	
	public function status($newStatus=null) {
		if( !empty($newStatus) ) {
			static::checkStatus($newStatus, $this->status);
			$this->setData('status', $newStatus);
		}
		return $this->status;
	}
	
	public static function checkStatus($newStatus, $currentStatus=null) {
		if( empty($newStatus) ) {
			throw new UserException('invalidStatus');
		}
		if( !isset(static::$status[$newStatus]) ) {
			throw new UserException('unknownStatus');
		}
		//Si pas nouveau, on vérifie que le status actuel permet de passer à ce status.
		if( isset($currentStatus) && !in_array($newStatus, static::$status[$currentStatus]) ) {
			throw new UserException('unavailableStatus');
		}
		return $newStatus;
	}
	
	public static function getDefaultStatus() {
		return key(static::$status);
	}
}
?>