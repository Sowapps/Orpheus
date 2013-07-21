<?php
/* PHP File for the website sources
 * It's your app's library.
 *
 * Author: Your name.
 */

addAutoload('SiteUser',							'src/siteuser');
addAutoload('DemoTest',							'src/demotest');
addAutoload('DemoTest_MSSQL',					'src/demotest_mssql');

addAutoload('Session',							'sessionhandler/dbsession');


// Hooks

//! Hook 'startSession'
Hook::register('startSession', function () {
	OSessionHandler::register();
});