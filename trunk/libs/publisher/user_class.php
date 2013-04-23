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
	//protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	protected static $fields = array(
		'id', 'name', 'password', 'accesslevel', 'status', 'email', 'email_public',
		'create_time', 'create_ip', 'activation_time', 'activation_ip', 'login_time', 'login_ip', 'activity_time', 'activity_ip'
	);
	protected static $editableFields = array('name', 'password', 'email', 'email_public');
	protected static $validator = array(
		'name'			=> 'checkName',
		'password'		=> 'checkPassword',
		'email'			=> 'checkEmail',
		'email_public'	=> 'checkPublicEmail'
	);
	
	protected $login = 0;

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
	public function logout($reason=null) {
		global $USER;
		if( !$this->login ) {
			return false;
		}
		$this->login = 0;
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
		global $USER;
		if( !isset($inputData['accesslevel']) ) {
			return null;
		}
		if( !is_id($inputData['accesslevel']) || $inputData['accesslevel'] > 300 ) {
			throw new UserException('invalidAccessLevel');
		}
		if( $inputData['accesslevel'] == $this->accesslevel ) {
			throw new UserException('sameAccessLevel');
		}
		if( !User::canDo('users_grants', $this) // Can the current user do this action ? This user try to edit himself ?
			|| !$USER->checkPerm($this->accesslevel) // Has the current user less accesslevel that the edited one ?
			|| !$USER->checkPerm($inputData['accesslevel']) // Has the current user less accesslevel that he want to grant ?
		) {
			throw new UserException('forbiddenGrant');
		}
		return (int) $inputData['accesslevel'];
	}
	
	//! Checks if this user can do a restricted action on an user
	/*!
	 * \param $action The action to look for.
	 * \param $user The user we want to edit.
	 * \return True if this user has enough acess level to edit $user.
	 * 
	 * Checks if this user can do $action on $user.
	 */
	public function canOn($action, $user) {
		return $this->checkPerm($action) && $user->accesslevel < $this->accesslevel;
	}
	
	//! Updates this publication object
	/*!
	 * \sa PermanentObject::update()
	 * 
	 * This update method manages 'name', 'email', 'email_public', 'password' and 'accesslevel' fields.
	 */
	public function update($uInputData) {
		
		if( !User::canDo(static::$table.'_edit', $this) ) {
			throw new UserException('forbiddenUpdate');
		}
		
		return parent::update($uInputData);
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
			'where' => 'name LIKE '.SQLAdapter::quote($name),
			'number' => 1
		));
		if( empty($user) )  {
			throw new UserException("unknownName");
		}
		if( $user->password != $password )  {
			throw new UserException("wrongPassword");
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
		//http://www.php.net/manual/en/faq.passwords.php
		$salt = (defined('USER_SALT')) ? USER_SALT : '1$@g&';
		return hash('sha512', $salt.$str.'7');
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
		if( !empty($GLOBALS['USER']) && $GLOBALS['USER'] instanceof User && $GLOBALS['USER']->id == $id) {
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
	public static function delete($id) {
		if( !self::canDo('users_delete') ) {
			throw new UserException('forbiddenDelete');
		}
		return parent::delete($id);
	}
	
	//! Checks if this user can access to a module
	/*!
	 * \param $module The module to look for.
	 * \return True if this user can access to $module.
	 * 
	 * Checks if this user can access to $module.
	 */
	public static function canAccess($module) {
		global $USER, $ACCESS;
		return !empty($ACCESS) && (is_null($ACCESS->$module) || 
			( empty($USER) && $ACCESS->$module < 0 ) ||
			( !empty($USER) && $ACCESS->$module >= 0 && $USER instanceof SiteUser
				&& $USER->checkPerm((int) $GLOBALS['ACCESS']->$module)));
	}
	
	//! Checks if this user can do a restricted action
	/*!
	 * \param $action The action to look for.
	 * \param $selfEditUser The user if editing one or null. Default value is null.
	 * \return True if this user can do this $action.
	 * 
	 * Checks if this user can do $action.
	 */
	public static function canDo($action, $selfEditUser=null) {
		global $USER;
		return !empty($USER) && $USER instanceof SiteUser && ( $USER->checkPerm($action) || ( !empty($selfEditUser) && $selfEditUser instanceof SiteUser && $selfEditUser->equals($USER) ) );
	}
	
	// 		** Verification methods **
	
	//! Checks a name
	/*!
	 * \param $inputData The input data from the user.
	 * \return The stripped name.
	 * 
	 * Validates the name in array $inputData.
	 */
	public static function checkName($inputData, $ref=null) {
		if( !isset($inputData['name']) && isset($ref) ) {
			return null;
		}
		if( empty($inputData['name']) || !is_name($inputData['name']) ) {
			throw new UserException('invalidName');
		}
		return $inputData['name'];
	}
	
	//! Checks a Password
	/*!
	 * \param $inputData The input data from the user.
	 * \param $withConfirmation True if the confirmation is required. Default value is true.
	 * \return The hashed password string.
	 * 
	 * Validates the password in array $inputData.
	 */
	public static function checkPassword($inputData, $ref=null) {
		if( empty($inputData['password']) ) {
			if( isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidPassword');
		} else if( isset($inputData['password_conf']) && (empty($inputData['password_conf']) || $inputData['password'] != $inputData['password_conf']) ) {
			throw new UserException('invalidPasswordConf');
		}
		return static::hashPassword($inputData['password']);
	}
	
	//! Checks an Email address
	/*!
	 * \param $inputData The input data from the user.
	 * \return The email address.
	 * 
	 * Validates the email address in array $inputData.
	 */
	public static function checkEmail($inputData, $ref) {
		if( empty($inputData['email']) || !is_email($inputData['email']) ) {
			if( empty($inputData['email']) && isset($ref) ) {//UPDATE
				return null;
			}
			throw new UserException('invalidEmail');
		}
		return $inputData['email'];
	}
	
	//! Checks a public Email address
	/*!
	 * \param $inputData The input data from the user.
	 * \return The public email address.
	 * 
	 * Validates the public email address in array $inputData.
	 * This address is not required, you can use a checkbox to automatically use the real email address.
	 * e.g The email is foo@bar.com and public_email is 'on', the returned public_email will be foo@bar.com.
	 */
	public static function checkPublicEmail($inputData, $ref) {
		if( !isset($inputData['email_public']) && isset($ref) ) {//UPDATING
			return null;
		}
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
	
	//! Checks a access level
	/*!
	 * \param $inputData The input data from the user.
	 * \return The access level.
	 * \see checkPermissions()
	*/
	public static function checkAccessLevel($inputData, $ref) {
		if( !isset($inputData['accesslevel']) && isset($ref) ) {//UPDATING
			return null;
		}
		return $ref->checkPermissions($inputData);
	}
	
	//! Checks for object
	/*!
		\sa PermanentObject::checkForObject()
	*/
	public static function checkForObject($data) {
		if( empty($data['name']) && empty($data['email']) ) {
			return;//Nothing to check.
		}
		$user = static::get(array(
			'what'		=> 'name, email',
			'where'		=> 'name LIKE '.SQLAdapter::quote($data['name']).' OR email LIKE '.SQLAdapter::quote($data['email']),
			'output'	=> SQLAdapter::ARR_ASSOC,
			'number'	=> 1
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
	
	//! Validates a status
	/*!
		\sa AbstractStatus::validateStatus()
	*/
	public static function validateStatus($newStatus, $ref=null) {
		if( !User::canDo('users_status', $ref) ) {
			throw new UserException('forbiddenUStatus');
		}
		return parent::checkStatus($newStatus, $ref, $reportToUser=true);
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