<?php
//! The site user class
/*!
 * The site user class represents an user known by the current website as a permanent object.
 * This class is commonly inherited by a user class for registered users.
 * But a site user can be a Facebook user too for example.
 * 
 * Require core plugin.
 * 
 */
class User extends AbstractStatus {
	
	//Attributes
	protected static $table = 'users';
	//protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
		'id', 'name', 'password', 'accesslevel', 'status', 'email', 'email_public',
		'create_time', 'create_ip', 'activation_time', 'activation_ip', 'login_time', 'login_ip', 'activity_time', 'activity_ip'
	);
	protected static $userEditableFields = array(
		'email_public', 'password'
	);
	protected $login = 0;

	// *** METHODES SURCHARGEES ***
	
	//! ToString method for implicit conversions.
	public function __toString() {
		return $this->name;
	}
	
	//! Method when this object is unserialized.
	public function __wakeup() {
		if( $this->login ) {
			static::logEvent('activity');
		}
	}
	
	// *** METHODES UTILISATEUR ***
	
	//! Log in this user to the current session.
	public function login() {
		if( static::is_login() ) {
			throw new UserException('alreadyLogguedIn');
		}
		global $USER;
		$_SESSION['USER'] = $USER = $this;
		$this->login = 1;
		static::logEvent('login');
		static::logEvent('activity');
	}
	
	//! Log out this user from the current session.
	public function logout() {
		global $USER;
		$this->login = 0;//Au cas où l'utilisateur est pointé ailleurs.
		$_SESSION['USER'] = $USER = null;
	}

	//! Checks permissions
	/*!
	 * \param $right The right to compare, can be the right string to look for or an integer.
	 * \return True if this user has enough acess level.
	 * 
	 * Compares the accesslevel of this user to the incoming right.
	 */
	public function checkPerm($right) {
		//$right peut être un entier ou une chaine de caractère correspondant à un droit.
		//Dans ce dernier cas, on va chercher l'entier correspondant.
		if( !ctype_digit("$right") && $right != -1 ) {
			if( !isset($GLOBALS['RIGHTS'][$right]) ) {
				throw new UnknownKeyException('unknownRight', $right);
			}
			$right = $GLOBALS['RIGHTS'][$right];
		}
		return ( $this->accesslevel >= $right );
	}
	
	//! Checks access permissions
	/*!
	 * \param $module The module to check.
	 * \return True if this user has enough acess level to access to this module.
	 * \sa checkPerm()
	 */
	public function checkAccess($module) {
		//$module pdoit être un nom de module.
		if( !isset($GLOBALS['ACCESS']->$module) ) {
			return true;
		}
		return $this->checkPerm((int) $GLOBALS['ACCESS']->$module);
	}
	
	//! Checks if current loggued user can edit this one.
	/*!
	 * \param $inputData The input data.
	 */
	public function checkPermissions($inputData ) {
		global $USER;
		/* Vérifie:
		 * - Si l'utilisateur courant a les droits de modifications.
		 * - Si l'utilisateur courant a strictement plus de permissions que l'utilisateur à éditer.
		 * - Si l'utilisateur courant ne tente pas de donner plus de droits qu'il n'en possède lui même.
		 */
		if( !isset($inputData['accesslevel']) || !is_id($inputData['accesslevel']) || $inputData['accesslevel'] > 200 ) {
			throw new UserException('invalidAccessLevel');
		}
		if( $inputData['accesslevel'] == $this->accesslevel ) {
			throw new UserException('sameAccessLevel');
		}
		if( user_can('rights_grant', $this) || $USER->accesslevel <= $this->accesslevel || $USER->accesslevel <= $inputData['accesslevel'] ) {
			throw new UserException('forbiddenGrant');
		}
		return (int) ( !empty($inputData['accesslevel']) );
	}
	
	//! Checks if this user can do $action on $user.
	public function canOn($action, $user) {
		return $this->checkPerm($action) && $user->accesslevel < $this->accesslevel;
	}
	
	//! Update this user object from given data
	public function update($uInputData, array $data=array()) {
		
		//Si aucun utilisateur n'est connecté ou qu'il n'est ni cet utilisateur ni ne possède les droits suffisants.
		if( !user_can(static::$table.'_edit', $this) ) {
			throw new UserException('forbiddenUpdate');
		}
		
		try {
			$inputData['name'] = self::checkName($uInputData);
			if( $inputData['name'] != $this->name ) {
				$data['name'] = $inputData['name'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		try {
			$inputData['email'] = self::checkEmail($uInputData);
			if( $inputData['email'] != $this->email ) {
				$data['email'] = $inputData['email'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		try {
			$inputData['email_public'] = self::checkPublicEmail($uInputData);
			if( $inputData['email_public'] != $this->email_public ) {
				$data['email_public'] = $inputData['email_public'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		try {
			//Un modérateur n'est pas obligé de fournir une confirmation.
			$inputData['password'] = self::checkPassword($uInputData, !user_can(static::$table.'_edit') );
			if( $inputData['password'] != $this->password ) {
				$data['password'] = $inputData['password'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		try {
			$inputData['accesslevel'] = $this->checkPermissions($uInputData);
			if( $inputData['accesslevel'] != $this->accesslevel ) {
				$data['accesslevel'] = $inputData['accesslevel'];
			}
		} catch(UserException $e) { addUserError($e); }
		
		return parent::update($uInputData, $data);
	}
	
	// *** METHODES STATIQUES ***
	
	public static function userLogin($data) {
		self::checkName($data);
		self::checkPassword($data, false);
		//self::checkForEntry() does not return password and id now.
		
		$user = SQLMapper::doSelect(array(
			'table' => static::$table,
			'what' => 'id, name, password',
			'where' => 'name = '.SQLMapper::quote($data['name']),
			'number' => 1
		));
		//$table=static::$table;
		if( empty($user) )  {
			throw new UserException("unknownName");
		}
		if( $user['password'] != self::hashPassword($data['password']) )  {
			throw new UserException("wrongPassword");
		}
		$user = static::load($user['id']);
		$user->login();
	}
	
	public static function hashPassword($str) {
		//http://www.php.net/manual/en/faq.passwords.php
		$salt = (defined('USER_SALT')) ? USER_SALT : '1$@g&';
		return hash('sha512', $salt.$str.'7');
	}
	
	public static function is_login() {
		return ( !empty($_SESSION['USER']) && is_object($_SESSION['USER']) && $_SESSION['USER'] instanceof User );
	}
	
	public static function load($id) {
		if( !empty($GLOBALS['USER']) && $GLOBALS['USER'] instanceof User && $GLOBALS['USER']->id == $id) {
			return $GLOBALS['USER'];
		}
		return parent::load($id);
	}
	
	public static function delete($id) {
		if( !user_can('users_delete') ) {
			throw new OperationForbiddenException('users_delete');
		}
		return parent::delete($id);
	}
	
	public static function canAccess($Module) {
		global $USER, $ACCESS;
		return !isset($ACCESS->$module) || (
			( empty($USER) && $ACCESS->$module < 0 ) ||
			( !empty($USER) && $ACCESS->$module >= 0 && $USER instanceof SiteUser && $USER->checkAccess($module) )
		);
	}
	/*
function user_can($action, $selfEditUser=null) {
	global $USER;
	return !empty($USER) && ( $USER instanceof SiteUser ) && ( $USER->checkPerm($action) || ( !empty($selfEditUser) && ( $selfEditUser instanceof SiteUser ) && $selfEditUser->equals($USER) ) );
}

function user_access($module) {
	global $USER;
	return !isset($GLOBALS['ACCESS'][$module]) || (
		( empty($USER) && $GLOBALS['ACCESS'][$module] < 0 ) ||
		( !empty($USER) && $GLOBALS['ACCESS'][$module] >= 0 && $USER instanceof SiteUser && $USER->checkAccess($module) )
	);
}
	 */
	
	// 		** METHODES DE VERIFICATION **
	
	public static function checkName($inputData) {
		if( empty($inputData['name']) || !is_name($inputData['name']) ) {
			throw new UserException('invalidName');
		}
		return $inputData['name'];
	}
	
	public static function checkPassword($inputData, $withConfirmation=1) {
		if( empty($inputData['password']) || ( $withConfirmation && empty($inputData['password_conf']) ) ) {
			throw new UserException('invalidPassword');
		} else if( $withConfirmation && $inputData['password'] != $inputData['password_conf'] ) {
			throw new UserException('invalidPasswordConf');
		}
		return static::hashPassword($inputData['password']);
	}
	
	public static function checkEmail($inputData) {
		if( empty($inputData['email']) || !is_email($inputData['email']) ) {
			throw new UserException('invalidEmail');
		}
		return $inputData['email'];
	}
	
	public static function checkPublicEmail($inputData) {
		//Require checkEmail() before.
		if( !empty($inputData['email_public']) ) {
			if( strtolower($inputData['email_public']) == 'on' && !empty($inputData['email']) ) {
				$inputData['email_public'] = $inputData['email'];
			} else if( !is_email($inputData['email_public']) ) {
				throw new UserException('invalidPublicEmail');
			}
		} else {
			$inputData['email_public'] = '';
		}
		return $inputData['email_public'];
	}
	
	public static function checkUserInput($uInputData) {
		$data = array();
		$data['name'] = self::checkName($uInputData);
		$data['password'] = self::checkPassword($uInputData);
		$data['email'] = self::checkEmail($uInputData);
		$data['email_public'] = self::checkPublicEmail($uInputData);
		return $data+parent::checkUserInput($uInputData);
	}
	
	public static function checkForEntry($data) {
		if( empty($data['name']) && empty($data['email']) ) {
			return;//Nothing to check.
		}
		$user = SQLMapper::doSelect(array(
			'table' => static::$table,
			'what' => 'name, email',
			'where' => 'name LIKE '.SQLMapper::quote($data['name']).' OR email LIKE '.SQLMapper::quote($data['email']),
			'number' => 1
		));
		if( !empty($user) ) {
			if( $user['email'] == $data['email'] ) {
				throw new UserException("emailAlreadyUsed");
				
			} else {
				throw new UserException("entryExisting");
			}
		}
	}
	
	// *** STATUS METHODS ***
	public static function checkStatus($newStatus, $currentStatus=null) {
		if( !user_can('users_status', 1) ) {
			throw new UserException('forbiddenUStatus');
		}
		return parent::checkStatus($newStatus, $currentStatus);
	}
}
?>