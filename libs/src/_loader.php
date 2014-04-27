<?php
/* PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

addAutoload('SiteUser',							'src/siteuser');
addAutoload('DemoTest',							'src/demotest');
addAutoload('DemoTest_MSSQL',					'src/demotest_mssql');
addAutoload('DemoEntity',						'src/demoentity');

addAutoload('Session',							'sessionhandler/dbsession');


// Hooks

//! Hook 'runModule'
Hook::register('runModule', function($module) {
	if( getModuleAccess($module) > 0 ) {
		HTMLRendering::$theme = 'admin';
	}
});

//! Hook 'startSession'
Hook::register('startSession', function () {
	if( version_compare(PHP_VERSION, '5.4', '>=') ) {
		OSessionHandler::register();
	}
});

function getModuleAccess($module=null) {
	if( is_null($module) ) {
		$module = &$GLOBALS['Module'];
	}
	global $ACCESS;
	return !empty($ACCESS) && isset($ACCESS->$module) ? $ACCESS->$module : -2;
}

function debug($s, $d=-1) {
	if( $d !== -1 ) {
		$s .= ': '.htmlSecret($d);
	}
	text($s);
}

function htmlSecret($message) {
	if( is_null($message) ) {
		$message = '{NULL}';
	} else if( $message === false ) {
		$message = '{FALSE}';
	} else if( !is_scalar($message) ) {
		$message = '<pre>'.print_r($message, 1).'</pre>';
	}
	return '<button type="button" onclick="var next = this.nextSibling.style.display; next === \'none\' ? \'block\' : \'none\'; return 0;">'.t('Show').'</button><div style="display: none;">'.$message.'</div>';
// 	return '<button type="button" onclick="$(this).next().toggle(); return 0;">'.t('Show').'</button><div style="display: none;">'.$message.'</div>';
}