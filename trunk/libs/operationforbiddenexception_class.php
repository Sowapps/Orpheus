<?php
//! The operation forbidden exception class
/*!
	This exception is thrown when the user try to do something he have not enough permissions.
*/
class OperationForbiddenException extends Exception {
	
	private $action;
	
	//! Constructor
	/*!
		\param $action The name of the forbidden action.
	*/
	public function __construct($action) {
		parent::__construct('Error_operationForbidden', 1002);
		$this->action = (string) $action;
	}
	
	//! Gets the action
	/*!
		\return The action.
	*/
	public function getAction() {
		return $this->action;
	}
}
