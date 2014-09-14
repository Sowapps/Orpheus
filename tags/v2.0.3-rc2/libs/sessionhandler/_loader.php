<?php
/* Loader File for the orpheus session handler sources
 */

addAutoload('OSessionHandler',					'sessionhandler/osessionhandler');
addAutoload('SessionInterface',					'sessionhandler/sessioninterface');

// addAutoload('Session',							'sessionhandler/dbsession');
// addAutoload('Session',							'sessionhandler/fssession');

// Hooks

//! Hook 'startSession'
// Hook::register('startSession', function () {
	
// });