<?php
/** The site user class

 * A site user is a registered user.
 * 
 * Require:
 * is_id()
 * is_email()
 * pdo_query()
 * 
 */

class User extends AbstractUser {
	
	// *** OVERLOADED METHODS ***
	
	public function __toString() {
		try {
			return escapeText($this->fullname);
		} catch( Exception $e ) {
			return '';
		}
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}
	
	public function getRank() {
		$perms = array_flip(static::getAvailRoles());
		return isset($perms[$this->accesslevel]) ? $perms[$this->accesslevel] : 'unknown_rank';
	}
	
	public function getAvailRoles() {
		 $roles = static::getUserRoles();
		 foreach( $roles as $status => $accesslevel ) {
		 	if( !$this->checkPerm($accesslevel) ) {
		 		unset($roles[$status]);
		 	}
		 }
		 return $roles;
	}
	public function getRoleText() {
		$status = array_flip(static::getAvailRoles());
		return isset($status[$this->accesslevel]) ? t('role_'.$status[$this->accesslevel], static::getDomain()) : t('role_unknown', static::getDomain(), $this->accesslevel);
	}
	
	public function getAdminLink($ref=0) {
		return u('adm_user', array('userID'=>$this->id()));
	}
	
	public function getLink() {
		return static::genLink($this->id());
	}
	public static function genLink($id) {
		return u('profile', $id);
	}
	
	public function canUserList($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canUserUpdate();
	}
	/**
	 * @param int $context
	 * @param User $contextResource
	 * @return boolean
	 */
	public function canUserCreate($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_edit');// Only App admins can do it.
	}
	
	/**
	 * @param int $context
	 * @param User $contextResource
	 * @return boolean
	 */
	public function canUserEdit($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		if( $this->canDo('user_edit') ) { return true; }
// 		if( $context == CRAC_CONTEXT_APPLICATION ) { return false; }
		return false;
	}
	public function canSeeDevelopers($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_seedev');// Only App admins can do it.
	}
	public function canUserStatus($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_status');// Only App admins can do it.
	}
	public function canUserDelete($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_delete');// Only App admins can do it.
	}
	public function canUserGrant($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_grant');// Only App admins can do it.
	}
	
	public function canThreadMessageManage($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('threadmessage_manage');// Only App admins can do it.
	}
	
	public function canEntityDelete($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('entity_delete');// Only App admins can do it.
	}
	

	// 		** CHECK METHODS **

// 	public static function checkFullName($inputData) {
// 		if( empty($inputData['fullname']) ) {
// 			static::throwException('invalidFullName');
// 		}
// 		return strip_tags($inputData['fullname']);
// 	}
	
// 	public static function checkUserInput($uInputData, $fields=null, $ref=null, &$errCount=0) {
// 		$data = parent::checkUserInput($uInputData, $fields, $ref, $errCount);
// 		if( !empty($uInputData['password']) ) {
// 			$data['real_password'] = $uInputData['password'];
// 		}
// 		return $data;
// 	}
	
}
User::init();
