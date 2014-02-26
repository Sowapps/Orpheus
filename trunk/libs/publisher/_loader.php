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
	global $USER_CLASS;
	// If user can not access to this module, we redirect him to default but if default is forbidden, we can not redirect indefinitely.
	// User should always access to default, even if it redirects him to another module.
	if( !$USER_CLASS::canAccess($GLOBALS['Module']) && DEFAULTMOD != $GLOBALS['Module'] ) {
		//log_debug(__FILE__.'('.__LINE__.'): Redirecting to default');
		redirectTo(( defined('ACCESSDENIEDMOD') ) ? u(ACCESSDENIEDMOD) : u(DEFAULTMOD));
	}
});

function id(&$id) {
	return $id = is_object($id) ? $id->id() : $id;
}