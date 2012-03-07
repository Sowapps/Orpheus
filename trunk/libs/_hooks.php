<?php
/*!
	\brief The hooks' default callbacks
	
	PHP File containing default registering of hooks' callbacks.
 */

//! New callback for Hook 'runModule'
Hook::register('runModule', function ($Module) {
	//If user try to override url rewriting.
	if( empty($_SERVER['REDIRECT_rewritten']) && $_SERVER['REQUEST_URI'] != '/' && $Module != 'remote' ) {
		header('HTTP/1.1 301 Moved Permanently', false, 301);
		header('Location: '.$Module.'.html');
		exit();
	}
});

// if( !empty($_GET['module']) && is_name($_GET['module']) && file_exists(MODPATH.$_GET['module'].'.php') ) {
// 	if( user_access($_GET['module']) ) {
// 		$Module = $_GET['module'];
// 	} else {
// 		$Module = 'access_denied';
// 	}
// } else {
// 	$Module = DEFAULTMOD;
// }