<?php
/*! \file
 * All web site constants.
 *
 * \page constants Constants
 * 
 * You'll may need configure some constants as AUTHORNAME and SITENAME.\n
 * Configure others carefully and only if it's really necessary.\n
 * Set ERROR_LEVEL to set your website in production (with no error reports to the user).
 */

define('DEV_LEVEL', E_ALL | E_STRICT);//Development
define('PROD_LEVEL', 0);//Production

define('ERROR_LEVEL', DEV_LEVEL);

define('TIME', $_SERVER['REQUEST_TIME']);
//define('DS', DIRECTORY_SEPARATOR);
define('INSIDE', true);

//Useful paths.
define('CONFPATH',		ORPHEUSPATH.'configs/');
define('MODPATH',		ORPHEUSPATH.'modules/');
define('LIBSPATH',		ORPHEUSPATH.'libs/');
define('LOGSPATH',		ORPHEUSPATH.'logs/');
define('THEMESPATH',	ORPHEUSPATH.'themes/');

//Static medias
define('JSPATH',		'js/');

// LIB Initernationalization
define('LANGPATH',	ORPHEUSPATH.'languages/');
define('LANG',	'en_US');


//Miscelanous
define('DEFAULTMOD',	'home');
define('SITEROOT',		'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
define('DEFAULTLINK',	SITEROOT);

define('AUTHORNAME',	'Florent HAZARD');
define('SITENAME',		'Orpheus');

define('PDOLOGFILENAME','.pdo_error');
define('SYSLOGFILENAME','.system');
