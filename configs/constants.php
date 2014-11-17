<?php
/** \file
 * All web site constants.
 *
 * @page constants Constants
 * 
 * This file contains all the main constants, you will often work with it and you need to define your own.
 * You will find here constants like AUTHORNAME and SITENAME, and also path constants.\n
 * Configure others carefully and only if it's really necessary, libraries may require some.\n
 * 
 * Set ERROR_LEVEL to put your website in production (with no error reports to the user).
 * This is compatible with multi-instance architecture, so you can set a dev version and
 * a production version using the same sources on you own server.
 * Official ERROR_LEVEL values are DEV_LEVEL (all errors) and PROD_LEVEL (no errors) and
 * ERROR_LEVEL is set depending on DEV_VERSION value (if set).
 */
defifn('TIME',				$_SERVER['REQUEST_TIME']);
define('INSIDE',			true);

defifn('DEV_VERSION',		false);
defifn('ERROR_LEVEL',		DEV_VERSION && !defined('FORCE_ERRORS') ? DEV_LEVEL : PROD_LEVEL);
// defifn('ERROR_LEVEL',		defined('DEV_VERSION') && DEV_VERSION ? DEV_LEVEL : PROD_LEVEL);
// defifn('ERROR_LEVEL',		(basename(dirname($_SERVER['SCRIPT_FILENAME']).'/') == 'dev' || strpos($_SERVER['SCRIPT_FILENAME'], 'debug') !== false) ? DEV_LEVEL : PROD_LEVEL);

defifn('DEV_TOOLS',			DEV_VERSION && (defined('TERMINAL') || !empty($_SERVER['PHP_AUTH_USER'])));

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


// Route
defifn('DEFAULTMOD',		'home');
defifn('DEFAULTHOST',		'domain.com');
defifn('DEFAULTPATH',		'');

defifn('HTTPS',				!empty($_SERVER['HTTPS']));
defifn('SCHEME',			HTTPS ? 'https' : 'http' );
defifn('HOST',				!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DEFAULTHOST);
defifn('PATH',				!defined("TERMINAL") ? dirpath($_SERVER['SCRIPT_NAME']) : DEFAULTPATH);
defifn('SITEROOT',			SCHEME.'://'.HOST.PATH);
defifn('DEFAULTLINK',		SITEROOT);

// Static medias
defifn('JSURL',				SITEROOT.'/js/');
defifn('THEMESURL',			SITEROOT.'/'.THEMESDIR);

defifn('AUTHORNAME',		'Florent HAZARD');
defifn('SITENAME',			'Orpheus');
defifn('ADMINEMAIL',		'contact@orpheus-framework.com');

define('CRAC_CONTEXT_APPLICATION',	1);
define('CRAC_CONTEXT_AGENCY',		2);
define('CRAC_CONTEXT_RESOURCE',		3);

defifn('PDOLOGFILENAME',	'.pdo_error');
defifn('SYSLOGFILENAME',	'.system');
defifn('DEBUGFILENAME',		'.debug');
defifn('HACKLOGFILENAME',	'.hack');
defifn('SERVLOGFILENAME',	'.server');
