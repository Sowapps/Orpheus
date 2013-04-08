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
$USER_CLASS = USER_CLASS;

// Hooks

//! Hook 'runModule'
Hook::register('runModule', function () {
	global $USER_CLASS;
	if( !$USER_CLASS::canAccess($GLOBALS['Module']) ) {
		redirectTo(( defined('ACCESSDENIEDMOD') ) ? u(ACCESSDENIEDMOD) : DEFAULTLINK);
	}
});