<?php

use Orpheus\Email\Email;
use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\Hook\Hook;

/**
 * PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

defifn('DOMAIN_SETUP', 'setup');

// Entities
PermanentEntity::registerEntity('File');
PermanentEntity::registerEntity('User');
PermanentEntity::registerEntity('DemoEntity');


// Hooks

/** Hook 'runModule'
 *
 */
Hook::register(HOOK_RUNMODULE, function ($module) {
	if( getModuleAccess($module) > 0 ) {
		HTMLRendering::$theme = 'admin';
	}
});

function getModuleAccess($module = null) {
	if( $module === null ) {
		$module = &$GLOBALS['Module'];
	}
	global $ACCESS;
	return !empty($ACCESS) && isset($ACCESS->$module) ? $ACCESS->$module : -2;
}

/**
 * @param User $user
 */
function sendAdminRegistrationEmail($user) {
	$SITENAME = t('app_name');
	$SITEURL = DEFAULTLINK;
	$e = new Email('Orpheus - Registration of ' . $user->fullname);
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
	$SITENAME = t('app_name');
	$e = new Email('Orpheus - New message of ' . $tm->user_name);
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
	require_once ORPHEUSPATH . 'src/admin-form.php';
}
