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

defifn('ERROR_LEVEL',		defined('DEV_VERSION') && DEV_VERSION ? DEV_LEVEL : PROD_LEVEL);
// defifn('ERROR_LEVEL',		(basename(dirname($_SERVER['SCRIPT_FILENAME']).'/') == 'dev' || strpos($_SERVER['SCRIPT_FILENAME'], 'debug') !== false) ? DEV_LEVEL : PROD_LEVEL);

defifn('DEV_TOOLS',			ERROR_LEVEL == DEV_LEVEL && (defined('TERMINAL') || !empty($_SERVER['PHP_AUTH_USER'])));

defifn('USER_CLASS',		'SiteUser');

// Useful paths.
defifn('CONFDIR',			'configs/');
defifn('MODDIR',			'modules/');
defifn('LIBSDIR',			'libs/');
defifn('THEMESDIR',			'themes/');

defifn('SRCPATH',			pathOf(LIBSDIR.'src/'));
defifn('LOGSPATH',			pathOf('logs/'));
defifn('STOREPATH',			pathOf('store/'));
defifn('CACHEPATH',			STOREPATH.'cache/');

// defifn('CONFIGLIB',			'config');
// defifn('CORELIB',			'core');

// Theme
defifn('LAYOUT_MENU',		'menu-bootstrap3');

// LIB Initernationalization
defifn('LANGDIR',			'languages/');
defifn('LANG',				'en_US');
defifn('LANGBASE',			'en');
// defifn('LANGBASE',			array_shift(explode('_', LANG, 2)));
defifn('LOCALE',			LANG.'.utf8');


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

// Static medias
defifn('JSURL',				SITEROOT.'/js/');
defifn('THEMESURL',			SITEROOT.'/'.THEMESDIR);

defifn('AUTHORNAME',		'Florent HAZARD');
defifn('SITENAME',			'Orpheus');

define('CRAC_CONTEXT_APPLICATION',	1);
define('CRAC_CONTEXT_AGENCY',		2);
define('CRAC_CONTEXT_RESOURCE',		3);

defifn('PDOLOGFILENAME',	'.pdo_error');
defifn('SYSLOGFILENAME',	'.system');
defifn('DEBUGFILENAME',		'.debug');
defifn('HACKLOGFILENAME',	'.hack');
defifn('SERVLOGFILENAME',	'.server');
