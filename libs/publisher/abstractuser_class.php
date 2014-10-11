<?php
/** The user class
 * The user class represents an user known by the current website as a permanent entity.
 * This class is commonly inherited by a user class for registered users.
 * But an user can be a Facebook user or a Site user for example.
 * 
 * Require core plugin.
 * 
 */
class AbstractUser extends PermanentEntity {
	
	//Attributes
	protected static $table		= 'user';
	
	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	//Attributes

	const NOT_LOGGED	= 0;
	const IS_LOGGED		= 1;
	const LOGGED_FORCED	= 3;
	protected $login = self::NOT_LOGGED;

	// *** METHODES SURCHARGEES ***
	
	/** Magic string conversion
	 * @return The string valu of this object.
	 * The string value is the contents of the publication.
	*/
	public function __toString() {
		return $this->fullname;
	}
	
	/** Method when this object is unserialized.
	 * 
	 */
	public function __wakeup() {
		if( $this->login ) {
			static::logEvent('activity');
		}
	}
	
	// *** METHODES UTILISATEUR ***
	
	/** Gets the Log in status of this user in the current session.
	 * 
	 * @param string $f
	 * @return boolean True
	 */
	public function isLogin($f=IS_LOGGED) {
		return bintest($this->login, $f);
	}
	
	/** Log in this user to the current session.
	 * 
	 * @param string $force
	 */
	public function login($force=false) {
		if( !$force && static::isLogged() ) {
			static::throwException('alreadyLoggedin');
		}
		global $USER;
		$_SESSION['USER'] = $USER = $this;
		$this->login = $force ? self::LOGGED_FORCED : self::IS_LOGGED;
		if( !$force ) {
			static::logEvent('login');
		}
		static::logEvent('activity');
	}
	
	/** Log out this user from the current session.
	 * 
	 * @param string $reason
	 * @return boolean
	 */
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

	/** Checks permissions
	 * @param $right The right to compare, can be the right string to look for or an integer.
	 * @return True if this user has enough acess level.
	 * 
	 * Compares the accesslevel of this user to the incoming right.
	 */
	public function checkPerm($right) {
		//$right peut être un entier ou une chaine de caractère correspondant à un droit.
		//Dans ce dernier cas, on va chercher l'entier correspondant.
		if( !ctype_digit("$right") && $right != -1 ) {
			if( $GLOBALS['RIGHTS']->$right===NULL ) {
				throw new UnknownKeyException('unknownRight', $right);
			}
			$right = $GLOBALS['RIGHTS']->$right;
		}
// 		text("Access level: {$this->accesslevel}");
// 		text("Required right: {$right}");
		return ( $this->accesslevel >= $right );
	}
	
	/** Checks access permissions
	 * @param $module The module to check.
	 * @return True if this user has enough acess level to access to this module.
	 * @sa checkPerm()
	 * @warning Obsolete
	 */
	public function checkAccess($module) {
		//$module pdoit être un nom de module.
		if( !isset($GLOBALS['ACCESS']->$module) ) {
			return true;
		}
		return $this->checkPerm((int) $GLOBALS['ACCESS']->$module);
	}
	
	/** Checks if current logged user can edit this one.
	 * @param $inputData The input data.
	 */
	public function checkPermissions($inputData) {
		return static::checkAccessLevel($inputData, $this);
	}
	
	/** Checks if this user can alter data on the given user
	 * @param $user The user we want to edit.
	 * @return True if this user has enough acess level to edit $user or he is altering himself.
	 * @sa loggedCanDo()
	 * 
	 * Checks if this user can alter on $user.
	 */
	public function canAlter(User $user) {
// 		return $this->equals($user) || !$user->accesslevel || $this->accesslevel > $user->accesslevel;
		return !$user->accesslevel || $this->accesslevel > $user->accesslevel;
	}
	
	/** Check if this user can affect data on the given user
	 * @param $action The action to look for.
	 * @param $object The object we want to edit.
	 * @return True if this user has enough access level to alter $object (or he is altering himself).
	 * @sa loggedCanDo()
	 * @sa canAlter()
	 * 
	 * Check if this user can affect $object.
	 */
	public function canDo($action, $object=null) {
		global $USER_CLASS;
		return $this->equals($object) || ( $this->checkPerm($action) && ( !($object instanceof $USER_CLASS) || $this->canAlter($object) ) );
	}
	
	// *** METHODES STATIQUES ***
	
// 	/** Logs in an user from data
// 	/*!
// 	 * @param $data The data for user authentification.
// 	 * 
// 	 * Log in a user from the given data.
// 	 * It tries to validate given data, in case of errors, UserException are thrown.
// 	 */
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

	/** Log in an user from HTTP authentication
	 * 
	 */
	public static function httpLogin() {
		$user = static::get(array(
			'where' => 'name LIKE '.static::formatValue($_SERVER['PHP_AUTH_USER']),
// 			'number' => 1,
			'output' => SQLAdapter::OBJECT
		));
		if( empty($user) )  {
			static::throwNotFound();
		}
		if( $user->password != static::hashPassword($_SERVER['PHP_AUTH_PW']) )  {
			static::throwException("wrongPassword");
		}
		$user->logout();
		$user->login();
	}

	/** Create user from HTTP authentication
	 * @return User object
	 * @warning Require other data than name and password ard optional
	 *
	 * Create user from HTTP authentication
	 */
	public static function httpCreate() {
		return static::createAndGet(array('name'=>$_SERVER['PHP_AUTH_USER'], 'password'=>$_SERVER['PHP_AUTH_PW']));
	}

	/** Create user from HTTP authentication
	 * @return User object
	 * @warning Require other data than name and password ard optional
	 *
	 * Create user from HTTP authentication
	 */
	public static function httpAuthenticate() {
		try { 
			static::httpLogin();
			return true;
		} catch( NotFoundException $e ) {
			if( Config::get('httpauth_autocreate') ) {
				$user	= static::httpCreate();
				$user->login();
				return true;
			}
		} catch( UserException $e ) { }
		return false;
	}
	
	/** Hash a password
	 * @param $str The clear password.
	 * @return The hashed string.
	 * 
	 * Hash $str using a salt.
	 * Define constant USER_SALT to use your own salt.
	 */
	public static function hashPassword($str) {
		return hashString($str);
	}
	
	/** Check if the client is logged in
	 * @return True if the current client is logged in.
	 * 
	 * Check if the client is logged in.
	 * It verifies if a valid session exist.
	 */
	public static function is_login() {
		throw new Exception('Obsolete is_login() method');
// 		return ( !empty($_SESSION['USER']) && is_object($_SESSION['USER']) && $_SESSION['USER'] instanceof User && $_SESSION['USER']->login);
// 		return !empty($_SESSION['USER']) && $_SESSION['USER']->login;
	}

	/** Checks if the client is logged in
	 * @return True if the current client is logged in.
	 *
	 * Checks if the client is logged in.
	 */
	public static function isLogged() {
		return !empty($_SESSION['USER']) && $_SESSION['USER']->login;
// 		return !empty($_SESSION['USER']) && $_SESSION['USER']->login;
	}
	
	/** Get ID if user is logged
	 * @return The id of the current client logged in.
	 * 
	 * Get the ID of the current user or 0.
	 */
	public static function getLoggedUserID() {
		return static::isLogged() ? $_SESSION['USER']->id : 0;
	}
	
	/** Load an user object
	 * @sa PermanentObject::load()
	 * 
	 * It tries to optimize by getting directly the logged user if he has the same ID.
	 */
	public static function load($id) {
		if( static::getLoggedUserID() == $id) {
			return $GLOBALS['USER'];
		}
		return parent::load($id);
	}

	/** Checks if this user has admin right
	 * @return True if this user is logged and is admin.
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
		if( empty($ACCESS) || !isset($ACCESS->$module) ) { return null; }
		$v	= $ACCESS->$module;
		if( is_numeric($v) ) { return $v; }
		global $RIGHTS;
		if( isset($RIGHTS->$v) ) { return $RIGHTS->$v; }
		return static::getAccessOf($v);
	}
	
	/** Checks if this user can access to a module
	 * @param $module The module to look for.
	 * @return True if this user can access to $module.
	 * 
	 * Checks if this user can access to $module.
	 */
	public static function canAccess($module) {
		/* @var $USER AbstractUser */
		global $USER, $USER_CLASS;
// 		debug('$USER', $USER);
		$access	= static::getAccessOf($module);
		if( $access===NULL ) { return true; }
		$access	= (int) $access;
// 		text('$access : '.$access);
// 		text('User has perm ? '.b($USER->checkPerm($access)));
		return ( empty($USER) && $access < 0 ) ||
			( !empty($USER) && $access >= 0 &&
				$USER instanceof $USER_CLASS && $USER->checkPerm($access));
	}
	
	/** Checks if this user can do a restricted action
	 * @param $action The action to look for.
	 * @param $object The object to edit if editing one or null. Default value is null.
	 * @return True if this user can do this $action.
	 * 
	 * Checks if this user can do $action.
	 */
	public static function loggedCanDo($action, AbstractUser $object=null) {
		global $USER;
		//$USER instanceof USER_CLASS && 
		return !empty($USER) && $USER->canDo($action, $object);
	}
	
	/** Checks for object
	 * @sa PermanentObject::checkForObject()
	 */
	public static function checkForObject($data, $ref=null) {
		$where	= 'email LIKE '.static::formatValue($data['email']);
		$what	= 'email';
		if( empty($data['email']) ) {
			return;//Nothing to check. Email is mandatory.
		}
		if( !empty($data['name']) ) {
			$what	.= ', name';
			$where	.= ' OR name LIKE '.static::formatValue($data['name']);
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
	
	public static function generatePassword() {
		return generatePassword(mt_rand(8, 12));
	}
}
AbstractUser::init();