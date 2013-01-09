<?php
/*!
 * \brief The hooks' default callbacks
 * 
 * PHP File containing default registering of hooks' callbacks.
 */

using('hooks.hook');

//! Callback for Hook 'runModule'
Hook::register('runModule', function ($Module) {
	//If user try to override url rewriting and the requested page is not root.
	if( empty($_SERVER['REDIRECT_rewritten']) && $_SERVER['REQUEST_URI'][strlen($_SERVER['REQUEST_URI'])-1] != '/' && $Module != 'remote' ) {
		if( $Module == DEFAULTMOD ) {
			$redirLink = DEFAULTLINK;
		} else {
			$redirLink = u($Module);
		}
		permanentRedirectTo($redirLink);
	}
	// If the module is the default but with wrong link.
	if( $Module == DEFAULTMOD && empty($Action) && $_SERVER['REQUEST_URI'][strlen($_SERVER['REQUEST_URI'])-1] != '/' ) {
		permanentRedirectTo(DEFAULTLINK);
	}
});

//! Callback for Hook 'checkModule'
Hook::register('checkModule', function () {
	if( User::is_login() ) {
		//global $USER;// Do not work in this context.
		$GLOBALS['USER'] = &$_SESSION['USER'];
	}
	$GLOBALS['ACCESS'] = Config::build('access', true);
	$GLOBALS['RIGHTS'] = Config::build('rights', true);
});
