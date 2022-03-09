<?php

use Demo\User;
use Orpheus\Email\Email;
use Orpheus\EntityDescriptor\PermanentEntity;

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

User::setUserClass();

// Hooks

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
	$SITEURL = WEB_ROOT;
	$e = new Email('Orpheus - Registration of ' . $user->fullname);
	$e->setText(<<<BODY
Hi master !

A new dude just registered on <a href="{$SITEURL}">{$SITENAME}</a>, he is named {$user} ({$user->name}) with email {$user->email}.

Your humble servant, {$SITENAME}.
BODY
	);
	return $e->send(ADMINEMAIL);
}


function includeHTMLAdminFeatures() {
	require_once ORPHEUS_PATH . 'src/admin-form.php';
}
