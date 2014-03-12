<?php
//! The user class
/*!
 * The user class represents an user known by the current website as a permanent object.
 * This class is commonly inherited by a user class for registered users.
 * But an user can be a Facebook user or a Site user for example.
 * 
 * Require core plugin.
 * 
 */
class User extends AbstractStatus {
	
	//Attributes
	protected static $table = 'users';
	protected static $domain = 'users';
	//protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
		'id', 'name', 'password', 'accesslevel', 'status', 'email', 'email_public',
		'create_time', 'create_ip', 'activation_time', 'activation_ip', 'login_time', 'login_ip', 'activity_time', 'activity_ip'
	);
	protected static $editableFields = array('name', 'password', 'email', 'email_public', 'accesslevel');
	protected static $validator = array(
		'name'			=> 'checkName',
		'password'		=> 'checkPassword',
		'email'			=> 'checkEmail',
		'email_public'	=> 'checkPublicEmail',
		'accesslevel'	=> 'checkAccessLevel'
	);

	const NOT_LOGGED = 0;
	const IS_LOGGED = 1;
	const LOGGED_FORCED = 3;
	protected $login = self::NOT_LOGGED;

	// *** METHODES SURCHARGEES ***
	
	//! Magic string conversion
	/*!
		\return The string valu of this object.
		
		The string value is the contents of the publication.
	*/
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
	
	//! Gets the Log in status of this user in the current session.
	public function isLogin($f=IS_LOGGED) {
		return bintest($this->login, $f);
	}
	
	//! Log in this user to the current session.
	public function login($force=false) {
		if( !$force && static::is_login() ) {
			static::throwException('alreadyLogguedIn');
		}
		global $USER;
		$_SESSION['USER'] = $USER = $this;
		$this->login = $force ? self::LOGGED_FORCED : self::IS_LOGGED;
		if( !$force ) {
			static::logEvent('login');
		}
		static::logEvent('activity');
	}
	
	//! Log out this user from the current session.
	public function logout($reason=null) {
		global $USER;
		if( !$this->login ) {
			return false;
		}
		$this->login = self::NOT_LOGGED;
		$_SESSION['USER'] = $USER = null;
		$_SESSION['LOGOUT_REASON'] = $reason;
		return true;
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
			if( is_null($GLOBALS['RIGHTS']->$right) ) {
				throw new UnknownKeyException('unknownRight', $right);
			}
			$right = $GLOBALS['RIGHTS']->$right;
		}
		return ( $this->accesslevel >= $right );
	}
	
	//! Checks access permissions
	/*!
	 * \param $module The module to check.
	 * \return True if this user has enough acess level to access to this module.
	 * \sa checkPerm()
	 * \warning Obsolete
	 */
	public function checkAccess($module) {
		//$module pdoit être un nom de module.
		if( !isset($GLOBALS['ACCESS']->$module) ) {
			return true;
		}
		return $this->checkPerm((int) $GLOBALS['ACCESS']->$module);
	}
	
	//! Checks if current logged user can edit this one.
	/*!
	 * \param $inputData The input data.
	 */
	public function checkPermissions($inputData) {
		return static::checkAccessLevel($inputData, $this);
	}
	
	//! Checks if this user can alter data on the given user
	/*!
	 * \param $user The user we want to edit.
	 * \return True if this user has enough acess level to edit $user or he is altering himself.
	 * \sa loggedCanDo()
	 * 
	 * Checks if this user can alter on $user.
	 */
	public function canAlter(User $user) {
// 		return $this->equals($user) || !$user->accesslevel || $this->accesslevel > $user->accesslevel;
		return !$user->accesslevel || $this->accesslevel > $user->accesslevel;
	}
	
	//! Checks if this user can affect data on the given user
	/*!
	 * \param $action The action to look for.
	 * \param $object The object we want to edit.
	 * \return True if this user has enough access level to alter $object (or he is altering himself).
	 * \sa loggedCanDo()
	 * \sa canAlter()
	 * 
	 * Checks if this user can affect $object.
	 */
	public function canDo($action, $object=null) {
		return $this->equals($object) || ( $this->checkPerm($action) && ( !($object instanceof User) || $this->canAlter($object) ) );
	}
	
	// *** METHODES STATIQUES ***
	
	//! Logs in a user from data
	/*!
	 * \param $data The data for user authentification.
	 * 
	 * Log in a user from the given data.
	 * It tries to validate given data, in case of errors, UserException are thrown.
	 */
	public static function userLogin($data) {
		$name = self::checkName($data);
		$password = self::checkPassword($data);
		//self::checkForEntry() does not return password and id now.
		
		$user = static::get(array(
			'where' => 'name LIKE '.static::formatValue($name),
			'number' => 1,
			'output' => SQLAdapter::OBJECT
		));
		if( empty($user) )  {
			static::throwException("unknownName");
		}
		if( $user->password != $password )  {
			static::throwException("wrongPassword");
		}
		$user->logout();
		$user->login();
	}
	
	//! Hashes a password
	/*!
	 * \param $str The clear password.
	 * \return The hashed string.
	 * 
	 * Hashes $str using a salt.
	 * Define constant USER_SALT to use your own salt.
	 */
	public static function hashPassword($str) {
		return hashString($str);
	}
	
	//! Checks if the client is logged in
	/*!
	 * \return True if the current client is logged in.
	 * 
	 * Checks if the client is logged in.
	 * It verifies if a valid session exist.
	 */
	public static function is_login() {
		return ( !empty($_SESSION['USER']) && is_object($_SESSION['USER']) && $_SESSION['USER'] instanceof User && $_SESSION['USER']->login);
	}
	
	//! Gets ID if user is logged
	/*!
	 * \return The id of the current client logged in.
	 * 
	 * Gets the ID of the current user or 0.
	 */
	public static function getLoggedUserID() {
		return static::is_login() ? $_SESSION['USER']->id : 0;
	}
	
	//! Loads an user object
	/*!
	 * \sa PermanentObject::load()
	 * 
	 * It tries to optimize by getting directly the logged user if he has the same ID.
	 */
	public static function load($id) {
		if( static::getLoggedUserID() == $id) {
			return $GLOBALS['USER'];
		}
		return parent::load($id);
	}
	
	//! Deletes an user object
	/*!
	 * \sa PermanentObject::delete()
	 * 
	 * It tries to check current user rights.
	 */
// 	public static function delete($id) {
// 		if( !self::loggedCanDo('users_delete') ) {
// 			static::throwException('forbiddenDelete');
// 		}
// 		return parent::delete($id);
// 	}

	//! Checks if this user has admin right
	/*!
	 * \return True if this user is logged and is admin.
	 *
	 * Checks if this user has admin access level.
	 * This is often used to determine if the current user can access to the admin panel.
	 */
	public static function isAdmin() {
		global $USER;
		return ( !empty($USER) && $USER->accesslevel > 0 );
	}
	
	public static function getAccessOf($module) {
		/* @var $ACCESS Config */
		global $ACCESS;
// 		text("getAccessOf($module)");
		if( empty($ACCESS) || !isset($ACCESS->$module) ) { return null; }
		$v	= $ACCESS->$module;
// 		text("Value: $v");
		if( is_numeric($v) ) { return $v; }
		global $RIGHTS;
// 		debug("Reference, search in rights", $RIGHTS);
		if( isset($RIGHTS->$v) ) { return $RIGHTS->$v; }
// 		text("Reference to right not found, look for a reference to another access");
		return static::getAccessOf($v);
	}
	
	//! Checks if this user can access to a module
	/*!
	 * \param $module The module to look for.
	 * \return True if this user can access to $module.
	 * 
	 * Checks if this user can access to $module.
	 */
	public static function canAccess($module) {
		/* @var $USER SiteUser */
		global $USER;
// 		text("canAccess($module)");
		$access	= static::getAccessOf($module);
// 		text('$access : '.$access);
		if( is_null($access) ) { return true; }
		$access	= (int) $access;
		return is_null($access) || 
			( empty($USER) && $access < 0 ) ||
			( !empty($USER) && $access >= 0 &&
				$USER instanceof SiteUser && $USER->checkPerm($access));
	}
	
	//! Checks if this user can do a restricted action
	/*!
	 * \param $action The action to look for.
	 * \param $object The object to edit if editing one or null. Default value is null.
	 * \return True if this user can do this $action.
	 * 
	 * Checks if this user can do $action.
	 */
	public static function loggedCanDo($action, $object=null) {
		global $USER;
		return !empty($USER) && $USER instanceof User && $USER->canDo($action, $object);
	}
	
	// 		** Verification methods **
	
	//! Checks a name
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the field from.
	 * \return The stripped name.
	 * 
	 * Validates the name in array $inputData.
	 */
	public static function checkName($inputData, $ref=null) {
		if( !isset($inputData['name']) && isset($ref) ) {
			return null;
		}
		if( empty($inputData['name']) || !is_name($inputData['name']) ) {
			static::throwException('invalidName');
		}
		return $inputData['name'];
	}
	
	//! Checks a Password
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the field from.
	 * \return The hashed password string.
	 * 
	 * Validates the password in array $inputData.
	 */
	public static function checkPassword($inputData, $ref=null) {
		if( empty($inputData['password']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			static::throwException('invalidPassword');
		} else if( isset($inputData['password_conf']) && (empty($inputData['password_conf']) || $inputData['password'] != $inputData['password_conf']) ) {
			static::throwException('invalidPasswordConf');
		}
		return static::hashPassword($inputData['password']);
	}
	
	//! Checks an Email address
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the field from.
	 * \return The email address.
	 * 
	 * Validates the email address in array $inputData.
	 */
	public static function checkEmail($inputData, $ref=null) {
		if( empty($inputData['email']) || !is_email($inputData['email']) ) {
			if( empty($inputData['email']) && isset($ref) ) {//UPDATE
				return null;
			}
			static::throwException('invalidEmail');
		}
		return $inputData['email'];
	}
	
	//! Checks a public Email address
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the field from.
	 * \return The public email address.
	 * 
	 * Validates the public email address in array $inputData.
	 * This address is not required, you can use a checkbox to automatically use the real email address.
	 * e.g The email is foo@bar.com and public_email is 'on', the returned public_email will be foo@bar.com.
	 */
	public static function checkPublicEmail($inputData, $ref=null) {
		if( !isset($inputData['email_public']) && isset($ref) ) {//UPDATING
			return null;
		}
		//Require checkEmail() before.
		if( !empty($inputData['email_public']) ) {
			if( strtolower($inputData['email_public']) == 'on' && !empty($inputData['email']) ) {
				$inputData['email_public'] = $inputData['email'];
			} else if( !is_email($inputData['email_public']) ) {
				static::throwException('invalidPublicEmail');
			}
		} else {
			$inputData['email_public'] = '';
		}
		return $inputData['email_public'];
	}
	
	//! Checks a access level
	/*!
	 * \param $inputData The input data from the user.
	 * \param $ref The reference to check the field from.
	 * \return The access level.
	 * \see checkPermissions()
	*/
	public static function checkAccessLevel($inputData, $ref=null) {
		if( !isset($inputData['accesslevel']) ) {
			return isset($ref) ? null : 0;
		}
		if( !is_id($inputData['accesslevel']) || $inputData['accesslevel'] > 300 ) {
			static::throwException('invalidAccessLevel');
		}
// 		if( !defined('ALLOW_USER_GRANTING') ) { // Special case for developers
// 			global $USER;
// 			if( !User::loggedCanDo('users_grants', $ref) // Can the current user do this action ? This user try to edit himself ?
// 				|| (isset($ref) && !$USER->checkPerm($ref->accesslevel)) // Has the current user less accesslevel that the edited one ? - Already check in canDo()
// 				|| !$USER->checkPerm($inputData['accesslevel']) // Has the current user less accesslevel that he want to grant ?
// 			) {
// 				static::throwException('forbiddenGrant');
// 			}
// 			if( isset($ref) && $inputData['accesslevel'] == $ref->accesslevel ) {
// 				return null;
// 				static::throwException('sameAccessLevel');
// 			}
// 		}
		return (int) $inputData['accesslevel'];
	}
	
	//! Checks for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data, $ref=null) {
		$where = 'email LIKE '.static::formatValue($data['email']);
		$what = 'email';
		if( empty($data['email']) ) {
			return;//Nothing to check. Email is mandatory.
		}
		if( !empty($data['name']) ) {
			$what .= ', name';
			$where .= ' OR name LIKE '.static::formatValue($data['name']);
		}
		$user = static::get(array(
			'what'		=> $what,
			'where'		=> $where,
			'output'	=> SQLAdapter::ARR_FIRST
		));
		if( !empty($user) ) {
			if( $user['email'] == $data['email'] ) {
				static::throwException("emailAlreadyUsed");
				
			} else {
				static::throwException("entryExisting");
			}
		}
	}
	
	// *** STATUS METHODS ***
	
	//! Validates a status
	/*!
		\sa AbstractStatus::validateStatus()
	*/
	public static function validateStatus($newStatus, $ref=null, $field='status') {
		if( !User::loggedCanDo('users_status', $ref) ) {
			static::throwException('forbiddenUStatus');
		}
		return parent::validateStatus($newStatus, $ref, $field);//, $reportToUser=true
	}
	
	/*
	public static function init() {
		//self::$fields = array_unique(array_merge(self::$fields, parent::$fields));
		self::$editableFields = array_unique(array_merge(self::$editableFields, parent::$editableFields));
		self::$validator = array_unique(array_merge(self::$validator, parent::$validator));
	}
	*/
}
User::init();
/*
MYSQL

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_public` varchar(100) NOT NULL,
  `accesslevel` smallint(6) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `create_ip` varchar(40) NOT NULL DEFAULT '',
  `activation_time` int(10) unsigned NOT NULL DEFAULT '0',
  `activation_ip` varchar(40) NOT NULL DEFAULT '',
  `login_time` int(10) unsigned NOT NULL DEFAULT '0',
  `login_ip` varchar(40) NOT NULL DEFAULT '',
  `activity_time` int(10) unsigned NOT NULL DEFAULT '0',
  `activity_ip` varchar(40) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

*/