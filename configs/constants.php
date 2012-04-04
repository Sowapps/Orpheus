<?php
/* configs/constants.php -> CONSTANTS
 * All web site constants.
 * Required for initialization.
 *
 * Auteur: Florent Hazard.
 * Revision: 1
 */

define('ERROR_LEVEL', E_ALL | E_STRICT);//Development
//define('ERROR_LEVEL', 0);//Production

define('TIME', $_SERVER['REQUEST_TIME']);
define('DS', DIRECTORY_SEPARATOR);
define('INSIDE', true);


//Useful paths.
define('CONFPATH',		ORPHEUSPATH.'configs'.DS);
define('MODPATH',		ORPHEUSPATH.'modules'.DS);
define('LIBSPATH',		ORPHEUSPATH.'libs'.DS);
define('LOGSPATH',		ORPHEUSPATH.'logs'.DS);
define('THEMESPATH',	ORPHEUSPATH.'themes'.DS);


//Static medias
define('JSPATH',		'js'.DS);


//Miscelanous
define('DEFAULTMOD',	'home');
define('DEFAULTLINK',	'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));

define('AUTHORNAME',	'Florent HAZARD');
define('SITENAME',		'Orpheus');

define('SYSLOGFILENAME','.system');
