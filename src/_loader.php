<?php
/**
 * PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

use App\Entity\User;
use Orpheus\Email\Email;

function sendAdminRegistrationEmail(User $user): void {
	$appName = t('app_name');
	$appUrl = WEB_ROOT;
	$e = new Email('Orpheus - Registration of ' . $user->getLabel());
	$e->setText(<<<BODY
Hi master !

A new dude just registered on <a href="{$appUrl}">{$appName}</a>, he is named {$user} with email {$user->email}.

Your humble servant, {$appName}.
BODY
	);
	
	$e->send(ADMIN_EMAIL);
}
