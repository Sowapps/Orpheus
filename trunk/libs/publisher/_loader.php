<?php
/* Loader File for the publisher sources
 */

addAutoload('AbstractPublication',				'publisher/abstractpublication_class.php');
addAutoload('AbstractStatus',					'publisher/abstractstatus_class.php');
addAutoload('Email',							'publisher/email_class.php');
addAutoload('FieldNotFoundException',			'publisher/fieldnotfoundexception_class.php');
addAutoload('PermanentObject',					'publisher/permanentobject_class.php');
addAutoload('UnknownKeyException',				'publisher/unknownkeyexception_class.php');
addAutoload('User',								'publisher/user_class.php');

defifn('USER_CLASS',		'User');
global $USER_CLASS;
$USER_CLASS = USER_CLASS;

// Hooks

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