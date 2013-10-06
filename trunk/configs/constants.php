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
defifn('TIME',				$_SERVER['REQUEST_TIME']);
define('INSIDE',			true);

define('DEV_LEVEL',			E_ALL | E_STRICT);//Development
define('PROD_LEVEL',		0);//Production

defifn('ERROR_LEVEL',		(basename(dirname($_SERVER['SCRIPT_FILENAME']).'/') == 'dev') ? DEV_LEVEL : PROD_LEVEL);

defifn('USER_CLASS',		'SiteUser');

// Useful paths.
defifn('CONFDIR',			'configs/');
defifn('MODPATH',			'modules/');
defifn('LIBSPATH',			ORPHEUSPATH.'libs/');
defifn('THEMESPATH',		ORPHEUSPATH.'themes/');
defifn('THEMESURL',			'themes/');
defifn('LOGSPATH',			INSTANCEPATH.'logs/');
defifn('SRCPATH',			'src/');

// Static medias
defifn('JSURL',				'js/');

// LIB Initernationalization
defifn('LANGPATH',			ORPHEUSPATH.'languages/');
defifn('LANG',				'en_US');


// Miscelanous
defifn('DEFAULTMOD',		'home');
defifn('DEFAULTHOST',		'domain.com');
defifn('DEFAULTPATH',		'');
defifn('HTTPS',				!empty($_SERVER['HTTPS']));
defifn('SCHEME',			(HTTPS) ? 'https' : 'http' );
defifn('HOST',				(!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : DEFAULTHOST);
defifn('PATH',				(!defined("TERMINAL")) ? dirpath($_SERVER['SCRIPT_NAME']) : DEFAULTPATH);
defifn('SITEROOT',			SCHEME.'://'.HOST.PATH);
defifn('DEFAULTLINK',		SITEROOT);

defifn('AUTHORNAME',		'Florent HAZARD');
defifn('SITENAME',			'Orpheus');

defifn('PDOLOGFILENAME',	'.pdo_error');
defifn('SYSLOGFILENAME',	'.system');
defifn('DEBUGFILENAME',		'.debug');
