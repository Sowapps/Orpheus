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
defifn('TIME',			$_SERVER['REQUEST_TIME']);
define('INSIDE',		true);

define('DEV_LEVEL',		E_ALL | E_STRICT);//Development
define('PROD_LEVEL',	0);//Production

defifn('ERROR_LEVEL',	DEV_LEVEL);

//Useful paths.
defifn('CONFPATH',		ORPHEUSPATH.'configs/');
defifn('MODPATH',		ORPHEUSPATH.'modules/');
defifn('LIBSPATH',		ORPHEUSPATH.'libs/');
defifn('THEMESPATH',	ORPHEUSPATH.'themes/');
defifn('THEMESURL',		'themes/');
defifn('LOGSPATH',		INSTANCEPATH.'logs/');

//Static medias
defifn('JSURL',			'js/');

// LIB Initernationalization
defifn('LANGPATH',		ORPHEUSPATH.'languages/');
defifn('LANG',			'en_US');


//Miscelanous
defifn('DEFAULTMOD',	'home');
define('SITEROOT',		'http://'.$_SERVER['HTTP_HOST'].dirpath($_SERVER['SCRIPT_NAME']));
defifn('DEFAULTLINK',	SITEROOT);

defifn('AUTHORNAME',	'Florent HAZARD');
defifn('SITENAME',		'Orpheus');

defifn('PDOLOGFILENAME','.pdo_error');
defifn('SYSLOGFILENAME','.system');
defifn('DEBUGFILENAME',	'.debug');
