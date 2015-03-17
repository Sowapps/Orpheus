<?php
/**
 * @brief The hooks' default callbacks
 * 
 * PHP File containing default registering of hooks' callbacks.
 */

using('hooks');


/** Callback for Hook 'runModule'
 * 
 */
Hook::register('runModule', function ($Module) {
	if( defined('TERMINAL') ) { return; }
	$path		= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$isNotRoot	= !empty($path) && $path[strlen($path)-1] != '/';
// 	if( $isNotRoot ) {
// 		debug('Is not root, path => '.$path);
// 		die();
// 	}
// 	text('PATH => '.PATH);
// 	debug('_SERVER', $_SERVER);
// 	text('_SERVER[REQUEST_URI] => '.$_SERVER['REQUEST_URI']);
// 	text('last char of request uri is different from / => '.b($_SERVER['REQUEST_URI'][strlen($_SERVER['REQUEST_URI'])-1] != '/'));
// 	text('Current path: '.$path);
// 	die('Stopped for tests');

	//If user try to override url rewriting and the requested page is not root.
	if( $Module != 'remote' && empty($_SERVER['REDIRECT_rewritten']) && $isNotRoot ) {
		permanentRedirectTo(u($Module));
	}
	// If the module is the default but with wrong link.
	// REDIRECT_rewritten is essential to allow rewritten url to default mod
	if( $Module == DEFAULTMOD && empty($GLOBALS['Action']) && empty($_SERVER['REDIRECT_rewritten']) && $isNotRoot ) {
// 		debug('Default MOD but wrong link !');
		permanentRedirectTo(DEFAULTLINK);
	}
});

/** Callback for Hook 'checkModule'
 */
// Move to Publisher lib
// Hook::register('checkModule', function () {
// 	if( User::is_login() ) {
// 		//global $USER;// Do not work in this context.
// 		$GLOBALS['USER'] = &$_SESSION['USER'];
// 	}
// 	$GLOBALS['ACCESS'] = Config::build('access', true);
// 	$GLOBALS['RIGHTS'] = Config::build('rights', true);
// });