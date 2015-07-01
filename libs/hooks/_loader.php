<?php
/* Loader File for the hooks sources
 */

addAutoload('Hook',	'hooks/hook');

/*
* Some predefined hooks are specified in this file, it serves for the orpheus' core.
*/

// The libs are loaded, we start running engine
define('HOOK_LIBSLOADED',	'libsLoaded');
Hook::create(HOOK_LIBSLOADED);

// Checking module
define('HOOK_CHECKMODULE', 'checkModule');
Hook::create(HOOK_CHECKMODULE);

// Determine if session is started automatically
define('HOOK_STARTSESSION_AUTO', 'startSessionAuto');
Hook::create(HOOK_STARTSESSION_AUTO);

// Session is started
define('HOOK_STARTSESSION', 'startSession');
Hook::create(HOOK_STARTSESSION);

// Application ready
define('HOOK_APPREADY', 'appReady');
Hook::create(HOOK_APPREADY);

// Running module
define('HOOK_RUNMODULE', 'runModule');
Hook::create(HOOK_RUNMODULE);

// Show rendering
define('HOOK_SHOWRENDERING', 'showRendering');
Hook::create(HOOK_SHOWRENDERING);
