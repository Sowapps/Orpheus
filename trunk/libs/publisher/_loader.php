<?php
/**
 * Loader File for the publisher sources
 */

addAutoload('AbstractPublication',				'publisher/abstractpublication_class.php');
addAutoload('AbstractStatus',					'publisher/abstractstatus_class.php');
addAutoload('Email',							'publisher/email_class.php');
addAutoload('PermanentObject',					'publisher/permanentobject_class.php');
addAutoload('FieldNotFoundException',			'publisher/fieldnotfoundexception_class.php');
addAutoload('UnknownKeyException',				'publisher/unknownkeyexception_class.php');
addAutoload('InvalidFieldException',			'publisher/invalidfieldexception');
addAutoload('User',								'publisher/user_class.php');
addAutoload('AbstractUser',						'publisher/abstractuser_class.php');

defifn('CHECK_MODULE_ACCESS',	true);
defifn('USER_CLASS',			'User');
global $USER_CLASS;
$USER_CLASS = USER_CLASS;

// Hooks
define('HOOK_ACCESSDENIED', 	'accessDenied');
Hook::create(HOOK_ACCESSDENIED);

/** Hook HOOK_APPREADY
 */
Hook::register(HOOK_APPREADY, function () {
// 	debug('Publisher HOOK_APPREADY => '.HOOK_APPREADY);
	global $USER_CLASS;
	$GLOBALS['ACCESS'] = Config::build('access', true);
	$GLOBALS['RIGHTS'] = Config::build('rights', true);
	
	if( $USER_CLASS::isLogged() ) {
		//global $USER;// Do not work in this context.
		$USER = $GLOBALS['USER'] = &$_SESSION['USER'];
		if( !$USER->reload() ) {
			// User does not exist anymore
			$USER->logout();
		}
		
		// If login ip is different from current one, protect against cookie stealing
		if( Config::get('deny_multiple_connections', false) && !$USER->isLogin(User::LOGGED_FORCED) && $USER->login_ip != $_SERVER['REMOTE_ADDR'] ) {
			$USER->logout('loggedFromAnotherComputer');
			return;
		}
	} else
	if( isset($_SERVER['PHP_AUTH_USER']) && Config::get('httpauth_enabled') ) {
		$USER_CLASS::httpAuthenticate();
	}
});

/** Hook 'runModule'
 */
Hook::register(HOOK_RUNMODULE, function () {
	global $USER_CLASS, $Module;
	
// 	debug('Publisher HOOK_RUNMODULE $USER', $USER_CLASS::getLoggedUser());
// 	debug('$Module', $Module);
	// If user can not access to this module, we redirect him to default but if default is forbidden, we can not redirect indefinitely.
	// User should always access to default, even if it redirects him to another module.
	if( !$USER_CLASS::canAccess($Module) && DEFAULTMOD != $Module ) {
		$module	= $Module;
		// If the trigger returns null, 0, '' or false (false equality), it redirects the user if the module has not changed during trigger process
		// If the trigger returns true, 1 or a value, it cancels the redirects
		// This allows the dev to override the authentication, but it allows to use another limitation, like in page authentication or error message
		if( CHECK_MODULE_ACCESS && !Hook::trigger(HOOK_ACCESSDENIED, false, false) && $module===$Module ) {
			redirectTo(u(defined('ACCESSDENIEDMOD') ? ACCESSDENIEDMOD : DEFAULTMOD));
		}
	}
});

function id(&$id) {
	return $id = intval(is_object($id) ? $id->id() : $id);
}