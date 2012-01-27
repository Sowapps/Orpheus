<?php

Hook::register('runModule', function ($Module) {
	//If user try to override url rewriting.
	if( empty($_SERVER['REDIRECT_rewritten']) && $_SERVER['REQUEST_URI'] != '/' && $Module != 'remote' ) {
		header('HTTP/1.1 301 Moved Permanently', false, 301);
		header('Location: '.$Module.'.html');
		exit();
	}
});