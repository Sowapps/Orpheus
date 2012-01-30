<?php
/* includes/constants.php -> CONSTANTS
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
define('CONFPATH',		'configs'.DS);
define('MODPATH',		'modules'.DS);
define('LIBSPATH',		'libs'.DS);
define('LOGSPATH',		'logs'.DS);
define('JSPATH',		'js'.DS);
define('THEMESPATH',	'themes'.DS);

//Miscelanous

define('DEFAULTMOD',	'');

define('SITENAME',		'Orpheus');

define('SYSLOGFILENAME','.system');
