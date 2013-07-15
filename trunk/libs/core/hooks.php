<?php
/*!
 * \brief The hooks' default callbacks
 * 
 * PHP File containing default registering of hooks' callbacks.
 */

using('hooks');


//! Callback for Hook 'runModule'
Hook::register('runModule', function ($Module) {
	if( defined('TERMINAL') ) {
		return;
	}
	//If user try to override url rewriting and the requested page is not root.
	if( empty($_SERVER['REDIRECT_rewritten']) && !empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'][strlen($_SERVER['REQUEST_URI'])-1] != '/' && $Module != 'remote' ) {
		permanentRedirectTo(u($Module));
	}
	// If the module is the default but with wrong link.
	if( $Module == DEFAULTMOD && empty($GLOBALS['Action']) && !empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'][strlen($_SERVER['REQUEST_URI'])-1] != '/' ) {
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