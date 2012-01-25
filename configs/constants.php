<?php
/* includes/constants.php -> CONSTANTS
 * All web site constants.
 * Required for initialization.
 *
 * Auteur: Florent Hazard.
 * Revision: 1
 */

define('TIME', $_SERVER['REQUEST_TIME']);
define('DS', DIRECTORY_SEPARATOR);
define('INSIDE', true);

//Useful paths.
define('CONFPATH',		'config'.DS);
define('MODPATH',		'modules'.DS);
define('LIBSPATH',		'libs'.DS);
define('LOGSPATH',		'logs'.DS);
define('JSPATH',		'js'.DS);
define('THEMESPATH',		'themes'.DS);

//Miscelanous

define('DEFAULTMOD',		'js'.DS);

define('SITENAME',				'My FrameWork');

define('SYSLOGFILENAME',		'.system');
