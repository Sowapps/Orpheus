<?php
use Orpheus\Core\ClassLoader;
use Orpheus\Config\Config;
use Orpheus\Config\IniConfig;
use Orpheus\Hook\Hook;
use Orpheus\Core\RequestHandler;
/**
 * @file Bootstrap.php
 * @brief The Orpheus Core
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 * 
 * Website core.
 */
 
// define('PHP_SAPI_NAME',	php_sapi_name());
// die(PHP_SAPI_NAME);
define('IS_WEB',		array_key_exists('REQUEST_METHOD', $_SERVER));
define('IS_CONSOLE',	!IS_WEB);

if( isset($SRCPATHS) ) {
	$t = $SRCPATHS; unset($SRCPATHS);
}
require_once 'loader.php';
// Now on packagist (orpheus-core)
// require_once 'ClassLoader.php';

/**
 * The access path, this is independant from the type of access (http, console...)
 * It defines from which folder you access to your application
 */
defifn('ACCESSPATH',		dirpath($_SERVER['SCRIPT_FILENAME']));

/**
 * The path to the instance file, this file is optional.
 * This file allows you configure an instance of this application, you could use it to define the DEV_VERSION
 */
defifn('INSTANCEFILEPATH',	dirpath(dirname(dirname(ACCESSPATH))).'instance.php');

if( file_exists(INSTANCEFILEPATH) ) {
	require_once INSTANCEFILEPATH;
}

defifn('DEV_VERSION',		!IS_WEB);// True in all cases but web access

if( !ini_get('date.timezone') ) {
// if( !date_default_timezone_get() || date_default_timezone_get() === 'UTC' ) {
	// Set to avoid some PHP warnings
	date_default_timezone_set('UTC');
}

// These constants take care about paths through symbolic links.
// defifn('ORPHEUSPATH',		dirpath($_SERVER['SCRIPT_FILENAME']));	// The Orpheus sources
defifn('ORPHEUSPATH',		dirpath(dirname(ACCESSPATH)));	// The Orpheus sources - The current file one
defifn('APPLICATIONPATH',	ORPHEUSPATH);		// The application sources - default is Orpheus path,
defifn('INSTANCEPATH',		APPLICATIONPATH);	// The instance sources - default is Application path
// echo 'ORPHEUSPATH : '.ORPHEUSPATH.'<br />';
// echo 'APPLICATIONPATH : '.APPLICATIONPATH.'<br />';
// echo 'INSTANCEPATH : '.INSTANCEPATH.'<br />';
// die('Stopping script process');

addSrcPath(ORPHEUSPATH);
addSrcPath(APPLICATIONPATH);
addSrcPath(INSTANCEPATH);
if( isset($t) ) {
	foreach($t as $path) {
		addSrcPath($path);
	}
	unset($t);
}

defifn('CONSTANTSPATH', pathOf('configs/constants.php'));
defifn('DEFAULTSPATH', pathOf('configs/defaults.php', true));
// echo 'DEV_VERSION : '.intval(DEV_VERSION).'<br />';

// Edit the constant file according to the system context (OS, directory tree ...).
if( DEFAULTSPATH !== null ) {
	require_once DEFAULTSPATH;
}

defifn('CHECK_MODULE_ACCESS',	true);
defifn('TIME',				$_SERVER['REQUEST_TIME']);

// Useful paths
defifn('CONFDIR',			'configs/');
defifn('MODDIR',			'modules/');
defifn('LIBSDIR',			'libs/');
defifn('THEMESDIR',			'themes/');

defifn('SRCPATH',			pathOf(LIBSDIR.'src/'));
defifn('LOGSPATH',			pathOf('logs/'));
defifn('STOREPATH',			pathOf('store/'));
defifn('CACHEPATH',			STOREPATH.'cache/');

// Routing
defifn('HTTPS',				!empty($_SERVER['HTTPS']));
defifn('SCHEME',			HTTPS ? 'https' : 'http' );
defifn('HOST',				!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DEFAULTHOST);
defifn('PATH',				!defined('TERMINAL') ? dirpath($_SERVER['SCRIPT_NAME']) : DEFAULTPATH);
defifn('SITEROOT',			SCHEME.'://'.HOST.PATH);
defifn('DEFAULTLINK',		SITEROOT);

// Logs
defifn('PDOLOGFILENAME',	'.pdo_error');
defifn('SYSLOGFILENAME',	'.system');
defifn('DEBUGFILENAME',		'.debug');
defifn('HACKLOGFILENAME',	'.hack');
defifn('SERVLOGFILENAME',	'.server');

// Static medias
defifn('JSURL',				SITEROOT.'js/');
defifn('THEMESURL',			SITEROOT.THEMESDIR);

// Edit the global constants
require_once CONSTANTSPATH;

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

// Errors Actions
define('ERROR_THROW_EXCEPTION', 0);
define('ERROR_DISPLAY_RAW', 1);
define('ERROR_IGNORE', 2);
$ERROR_ACTION = ERROR_THROW_EXCEPTION;

set_error_handler(
/**
 * Error Handler
 * 
 * System function to handle PHP errors and convert it into exceptions.
*/
function($errno, $errstr, $errfile, $errline) {
// 	debug_print_backtrace();
// 	die('<br>'.$errno);
	
	$exception	= new ErrorException($errstr, 0, $errno, $errfile, $errline);
	if( empty($GLOBALS['NO_EXCEPTION']) && (empty($GLOBALS['ERROR_ACTION']) || $GLOBALS['ERROR_ACTION']==ERROR_THROW_EXCEPTION) ) {//ERROR_THROW_EXCEPTION
		throw $exception;
	} else if( !empty($GLOBALS['ERROR_ACTION']) && $GLOBALS['ERROR_ACTION'] == ERROR_IGNORE ) {//ERROR_IGNORE
		return;
	} else {//ERROR_DISPLAY_RAW
		if( !function_exists('log_error') ) {
			if( DEV_VERSION ) {
				displayException($exception, null);
			} else {
				die('A fatal error occurred.');
			}
		}
		log_error($exception);
	}
});

$DEBUG_BACKTRACE = null;
function storeBackTrace() {
	global $DEBUG_BACKTRACE;
// 	debug_print_backtrace();
	// Get backtrace & remove first line (this call)
	$DEBUG_BACKTRACE = array_slice(debug_backtrace(), 1);
// 	unset($backtrace[0]);
// 	debug('Backtrace', debug_backtrace());
// 	die();
}

register_shutdown_function(
/**
 * Shutdown Handler
 * 
 * System function to handle PHP shutdown and catch uncaught errors.
*/
function() {
	// If there is an error
	$error = error_get_last();
	
	if( $error ) {
		$exception	= new ErrorException($error['message'], 1, $error['type'], $error['file'], $error['line']);
		
		if( !function_exists('log_error') ) {
			if( DEV_VERSION ) {
				displayException($exception, 'Shutdown script');
			} else {
				die('A fatal error occurred.');
			}
		}
		log_error($exception, 'Shutdown script');
	}
});

set_exception_handler(
/**
 * Exception Handler
 * 
 * System function to handle all exceptions and stop script execution.
 */
function($exception) {
	global $coreAction;
	if( !function_exists('log_error') ) {
		if( DEV_VERSION ) {
			displayException($exception, 'Shutdown script');
		} else {
			die('A fatal error occurred.');
		}
// 		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	log_error($exception, $coreAction);
// 	log_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
// 	die('A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez réessayer plus tard.');
});

$AUTOLOADS = array();
$Module = $Page = '';// Useful for initializing errors.

$coreAction = 'initializing_core';

try {
	ob_start();
	
// 	if( !isset($REQUEST_HANDLER) && !isset($REQUEST_TYPE) ) {
// 		$REQUEST_TYPE	= IS_CONSOLE ? 'Console' : 'HTTP';
// 		$REQUEST_TYPE	= IS_CONSOLE ? 'Console' : 'HTTP';
// 	}
	
// 	defifn('REQUEST_HANDLER',	isset($REQUEST_HANDLER) ? $REQUEST_HANDLER : $REQUEST_TYPE.'Request');
// 	$REQUEST_HANDLER	= REQUEST_HANDLER;
// 	unset($REQUEST_HANDLER);

	defifn('VENDORPATH', APPLICATIONPATH.'vendor/');
	
	// Before lib loading, they can not define it
	// This class MUST extends Orpheus\Config\ConfigCore
	defifn('DEFAULT_CONFIG_CLASS', 'Orpheus\Config\IniConfig');
	
// 	echo "Autoload path of vendors => ".VENDORPATH.'autoload.php<br>';
	if( file_exists(VENDORPATH.'autoload.php') ) {
		/* @var Composer\Autoload\ClassLoader $PackageLoader */
		$PackageLoader = require VENDORPATH.'autoload.php';
	}
// 	die();
	
// 	defifn('CONSTANTSPATH', pathOf('configs/constants.php'));
// 	// Edit the constant file according to the system context (OS, directory tree ...).
// 	require_once CONSTANTSPATH;
	require_once pathOf('configs/libraries.php');
	
	if( !empty($Libraries) ) {
		foreach( $Libraries as $lib ) {
			if( !existsPathOf(LIBSDIR.$lib.'/_loader.php', $path) ) { continue; }
			require_once $path;
		}
	}
// 	if( empty($Libraries) ) {
// 		throw new Exception('Unable to load libraries, the config variable $Libraries is empty, please edit your configs/libraries.php file.');
// 	}
	
// 	foreach( $Libraries as $lib ) {
// 		if( !existsPathOf(LIBSDIR.$lib.'/_loader.php', $path) ) { continue; }
// 		require_once $path;
// 	}
	
	// After Lib loading
// 	class_alias(DEFAULT_CONFIG_CLASS, 'Orpheus\Config\Config', true);
	
	
	IniConfig::build('engine', false);// Some libs should require to get some configuration.
	
	$RENDERING = Config::get('default_rendering');
	
	// Here starts Hooks and Session too.
	Hook::trigger(HOOK_STARTSESSION);

	if( IS_WEB ) {
		startSession();
	}
	
	ob_end_clean();
	
	// App is now ready to run
	Hook::trigger(HOOK_APPREADY);
	
	// Handle current request
	RequestHandler::handleCurrentRequest(IS_CONSOLE ? RequestHandler::TYPE_CONSOLE : RequestHandler::TYPE_HTTP);
// 	$REQUEST_HANDLER::handleCurrentRequest();
	
	
} catch( Exception $e ) {
	log_error($e, $coreAction, true);
}
