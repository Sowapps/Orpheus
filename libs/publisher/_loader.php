<?php
/**
 * Loader File for the publisher sources
 */

addAutoload('AbstractPublication',				'publisher/AbstractPublication');
addAutoload('AbstractStatus',					'publisher/AbstractStatus');
addAutoload('Email',							'publisher/Email');
addAutoload('PermanentObject',					'publisher/PermanentObject');
addAutoload('FieldNotFoundException',			'publisher/FieldNotFoundException');
addAutoload('UnknownKeyException',				'publisher/UnknownKeyException');
addAutoload('InvalidFieldException',			'publisher/InvalidFieldException');
// addAutoload('User',								'publisher/user_class.php');
addAutoload('AbstractUser',						'publisher/AbstractUser');
addAutoload('FixtureInterface',					'publisher/Fixture');
addAutoload('FixtureRepository',				'publisher/Fixture');
addAutoload('PasswordGenerator',				'publisher/PasswordGenerator');

defifn('CHECK_MODULE_ACCESS',	true);
// defifn('USER_CLASS',			'User');
// global $USER_CLASS;
// $USER_CLASS = USER_CLASS;

// Hooks
define('HOOK_ACCESSDENIED', 	'accessDenied');
Hook::create(HOOK_ACCESSDENIED);

/**
 * Hook HOOK_APPREADY
 * Previously HOOK_CHECKMODULE but we need session was initialized before checking app things
 * HOOK_CHECKMODULE is called before session is initialized
 */
Hook::register(HOOK_APPREADY, function () {
// 	debug('Publisher HOOK_APPREADY => '.HOOK_APPREADY);
// 	global $USER_CLASS;
	$GLOBALS['ACCESS'] = Config::build('access', true);
	$GLOBALS['RIGHTS'] = Config::build('rights', true);
	
	if( User::isLogged() ) {
		//global $USER;// Do not work in this context.
		/* @var User $USER */
		$USER = $GLOBALS['USER'] = &$_SESSION['USER'];
		if( !$USER->reload() ) {
			// User does not exist anymore
			$USER->logout();
		}
		$USER->onConnected();
		
		// If login ip is different from current one, protect against cookie stealing
		if( Config::get('deny_multiple_connections', false) && !$USER->isLogin(AbstractUser::LOGGED_FORCED) && $USER->login_ip != $_SERVER['REMOTE_ADDR'] ) {
			$USER->logout('loggedFromAnotherComputer');
			return;
		}
	} else
	if( isset($_SERVER['PHP_AUTH_USER']) && Config::get('httpauth_enabled') ) {
		User::httpAuthenticate();
	}
});

/** Hook 'runModule'
 */
 /*
Hook::register(HOOK_RUNMODULE, function () {
// 	global $USER_CLASS, $Module;
	global $Module;
	
// 	debug('Publisher HOOK_RUNMODULE $USER', User::getLoggedUser());
// 	debug('$Module', $Module);
	// If user can not access to this module, we redirect him to default but if default is forbidden, we can not redirect indefinitely.
	// User should always access to default, even if it redirects him to another module.
	if( !User::canAccess($Module) && DEFAULTROUTE != $Module ) {
		$module	= $Module;
		// If the trigger returns null, 0, '' or false (false equality), it redirects the user if the module has not changed during trigger process
		// If the trigger returns true, 1 or a value, it cancels the redirects
		// This allows the dev to override the authentication, but it allows to use another limitation, like in page authentication or error message
		if( CHECK_MODULE_ACCESS && !Hook::trigger(HOOK_ACCESSDENIED, false, false) && $module===$Module ) {
			redirectTo(u(defined('ACCESSDENIEDMOD') ? ACCESSDENIEDMOD : DEFAULTROUTE));
		}
	}
});
*/

HTTPRoute::registerAccessRestriction('role', function($route, $options) {
	if( !is_string($options) ) {
		throw new Exception('Invalid route access restriction option in routes config, allow string only');
	}
	return User::loggedCanAccessToRoute($route, $options);
});

function id(&$id) {
	return $id = intval(is_object($id) ? $id->id() : $id);
}