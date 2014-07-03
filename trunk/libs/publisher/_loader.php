<?php
/* Loader File for the publisher sources
 */

addAutoload('AbstractPublication',				'publisher/abstractpublication_class.php');
addAutoload('AbstractStatus',					'publisher/abstractstatus_class.php');
addAutoload('Email',							'publisher/email_class.php');
addAutoload('FieldNotFoundException',			'publisher/fieldnotfoundexception_class.php');
addAutoload('PermanentObject',					'publisher/permanentobject_class.php');
addAutoload('UnknownKeyException',				'publisher/unknownkeyexception_class.php');
addAutoload('InvalidFieldException',			'publisher/invalidfieldexception');
addAutoload('User',								'publisher/user_class.php');

defifn('USER_CLASS',		'User');
global $USER_CLASS;
$USER_CLASS = USER_CLASS;

// Hooks
define('HOOK_ACCESSDENIED', 	'accessDenied');
Hook::create(HOOK_ACCESSDENIED);

//! Hook 'checkModule'
Hook::register('checkModule', function () {
	$GLOBALS['ACCESS'] = Config::build('access', true);
	$GLOBALS['RIGHTS'] = Config::build('rights', true);
	
	if( User::is_login() ) {
		//global $USER;// Do not work in this context.
		$USER = $GLOBALS['USER'] = &$_SESSION['USER'];
		
		// If login ip is different from current one, protect against cookie stealing
		if( Config::get('deny_multiple_connections', false) && !$USER->isLogin(User::LOGGED_FORCED) && $USER->login_ip != $_SERVER['REMOTE_ADDR'] ) {
			$USER->logout('loggedFromAnotherComputer');
			return;
		}
	}
});

//! Hook 'runModule'
Hook::register('runModule', function () {
	global $USER_CLASS, $Module;
	// If user can not access to this module, we redirect him to default but if default is forbidden, we can not redirect indefinitely.
	// User should always access to default, even if it redirects him to another module.
	if( !$USER_CLASS::canAccess($Module) && DEFAULTMOD != $Module ) {
		$module	= $Module;
		// If the trigger returns null, 0, '' or false (false equality), it redirects the user if the module has not changed during trigger process
		// If the trigger returns true, 1 or a value, it cancels the redirects
		// This allows the dev to override the authentication, but it allows to use another limitation, like in page authentication or error message
		if( !Hook::trigger(HOOK_ACCESSDENIED, false, false) && $module==$Module ) {
			redirectTo(u(defined('ACCESSDENIEDMOD') ? ACCESSDENIEDMOD : DEFAULTMOD));
		}
// 		text('Run module hook ending with mod '.$GLOBALS['Module']);
	}
});

function id(&$id) {
	return $id = intval(is_object($id) ? $id->id() : $id);
}