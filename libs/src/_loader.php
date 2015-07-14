<?php
/** PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

addAutoload('SiteUser',							'src/siteuser');
addAutoload('DemoTest',							'src/demotest');
addAutoload('DemoTest_MSSQL',					'src/demotest_mssql');
addAutoload('DemoEntity',						'src/demoentity');
addAutoload('ThreadMessage',					'src/threadmessage');

addAutoload('HomeController',					'src/controllers/homecontroller');
addAutoload('UserLoginController',				'src/controllers/userlogincontroller');
addAutoload('TwigSampleController',				'src/controllers/twigsamplecontroller');
addAutoload('AdminDemoController',				'src/controllers/admindemocontroller');
addAutoload('ThreadController',					'src/controllers/threadcontroller');

addAutoload('Session',							'sessionhandler/dbsession');


// Hooks

/** Hook 'runModule'
 * 
 */
Hook::register(HOOK_RUNMODULE, function($module) {
	if( getModuleAccess($module) > 0 ) {
		HTMLRendering::$theme = 'admin';
	}
});

/** Hook 'startSession'
 * 
 */
// Hook::register('startSession', function () {
// 	if( version_compare(PHP_VERSION, '5.4', '>=') ) {
// 		OSessionHandler::register();
// 	}
// });

function getModuleAccess($module=null) {
	if( $module === NULL ) {
		$module = &$GLOBALS['Module'];
	}
	global $ACCESS;
	return !empty($ACCESS) && isset($ACCESS->$module) ? $ACCESS->$module : -2;
}

/**
 * @param SiteUser $user
 */
function sendAdminRegistrationEmail($user) {
	$SITENAME	= SITENAME;
	$SITEURL	= DEFAULTLINK;
	$e	= new Email('Orpheus - Registration of '.$user->fullname);
	$e->setText(<<<BODY
Hi master !

A new dude just registered on <a href="{$SITEURL}">{$SITENAME}</a>, he is named {$user} ({$user->name}) with email {$user->email}.

Your humble servant, {$SITENAME}.
BODY
);
	return $e->send(ADMINEMAIL);
}

/**
 * @param ThreadMessage $tm
 */
function sendNewThreadMessageEmail($tm) {
// 	$user	= $tm->getUser();
	$SITENAME	= SITENAME;
	$e	= new Email('Orpheus - New message of '.$tm->user_name);
	$e->setText(<<<BODY
Hi master !

{$tm->getUser()} posted a new thread message:
{$tm}

Your humble servant, {$SITENAME}.
BODY
);
	return $e->send(ADMINEMAIL);
}
