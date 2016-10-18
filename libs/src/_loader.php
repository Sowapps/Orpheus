<?php
use Orpheus\Hook\Hook;
use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\Publisher\Email\Email;

/**
 * PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

addAutoload('File',								'src/File');
addAutoload('UploadedFile',						'src/UploadedFile');

addAutoload('AbstractFile',						'src/AbstractFile');
addAutoload('TextFile',							'src/TextFile');
addAutoload('GZFile',							'src/GZFile');

// addAutoload('GlobalConfig',						'src/GlobalConfig');
addAutoload('User',								'src/User');

addAutoload('DemoTest',							'src/DemoTest');
addAutoload('DemoTest_MSSQL',					'src/DemoTest_MSSQL');
addAutoload('DemoEntity',						'src/DemoEntity');
addAutoload('ThreadMessage',					'src/ThreadMessage');

addAutoload('HomeController',					'src/controllers/HomeController');
addAutoload('LoginController',					'src/controllers/LoginController');
addAutoload('LogoutController',					'src/controllers/LogoutController');
addAutoload('DownloadController',				'src/controllers/DownloadController');

addAutoload('FileDownloadController',			'src/controllers/FileDownloadController');

addAutoload('AdminController',					'src/controllers/admin/AdminController');
addAutoload('AdminMySettingsController',		'src/controllers/admin/AdminMySettingsController');
addAutoload('AdminUserListController',			'src/controllers/admin/AdminUserListController');
addAutoload('AdminUserEditController',			'src/controllers/admin/AdminUserEditController');

addAutoload('UserLoginController',				'src/controllers/UserLoginController');

addAutoload('HomeController',					'src/controllers/HomeController');
addAutoload('TwigSampleController',				'src/controllers/TwigSampleController');
addAutoload('ThreadController',					'src/controllers/ThreadController');

addAutoload('AdminController',					'src/controllers/admin/AdminController');
addAutoload('AdminDemoController',				'src/controllers/admin/AdminDemoController');
addAutoload('AdminUserListController',			'src/controllers/admin/AdminUserListController');
addAutoload('AdminConfigController',			'src/controllers/admin/AdminConfigController');

// addAutoload('DevToolsController',				'src/controllers/devtools/DevToolsController');
addAutoload('DevController',					'src/controllers/devtools/DevController');
addAutoload('DevHomeController',				'src/controllers/devtools/DevHomeController');
addAutoload('DevSystemController',				'src/controllers/devtools/DevSystemController');
addAutoload('DevEntitiesController',			'src/controllers/devtools/DevEntitiesController');
addAutoload('DevLogListController',				'src/controllers/devtools/DevLogListController');
addAutoload('DevLogViewController',				'src/controllers/devtools/DevLogViewController');
addAutoload('DevComposerController',			'src/controllers/devtools/DevComposerController');
addAutoload('DevAppTranslateController',		'src/controllers/devtools/DevAppTranslateController');

addAutoload('SetupController',					'src/controllers/setup/SetupController');
addAutoload('StartSetupController',				'src/controllers/setup/StartSetupController');
addAutoload('CheckFileSystemSetupController',	'src/controllers/setup/CheckFileSystemSetupController');
addAutoload('CheckDatabaseSetupController',		'src/controllers/setup/CheckDatabaseSetupController');
addAutoload('InstallDatabaseSetupController',	'src/controllers/setup/InstallDatabaseSetupController');
addAutoload('InstallFixturesSetupController',	'src/controllers/setup/InstallFixturesSetupController');
addAutoload('EndSetupController',				'src/controllers/setup/EndSetupController');

defifn('DOMAIN_SETUP',		'setup');

// Entities
PermanentEntity::registerEntity('File');
PermanentEntity::registerEntity('User');
PermanentEntity::registerEntity('DemoEntity');


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
 * @param User $user
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

function includeHTMLAdminFeatures() {
	require_once ORPHEUSPATH.LIBSDIR.'src/admin-form.php';
}
