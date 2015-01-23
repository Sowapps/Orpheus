<?php
/* Loader File for the hooks sources
 */

addAutoload('Hook',						'hooks/hook');

/*
* Some predefined hooks are specified in this file, it serves for the orpheus' core.\n
* Don't delete existing hooks or your website won't work correctly.\n
* We advise you to use your own library to add hooks.\n
*/
define('HOOK_STARTSESSION', 'startSession');
Hook::create(HOOK_STARTSESSION);

define('HOOK_CHECKMODULE', 'checkModule');
Hook::create(HOOK_CHECKMODULE);

define('HOOK_RUNMODULE', 'runModule');
Hook::create(HOOK_RUNMODULE);

// Hook::create('endModule');

define('HOOK_SHOWRENDERING', 'showRendering');
Hook::create(HOOK_SHOWRENDERING);