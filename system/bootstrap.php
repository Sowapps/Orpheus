<?php

use Orpheus\Config\Config;
use Orpheus\Config\IniConfig;
use Orpheus\Core\RequestHandler;
use Orpheus\Hook\Hook;

/**
 * @file Bootstrap.php
 * @brief The Orpheus Core
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 *
 * Website core.
 */

define('IS_CONSOLE', PHP_SAPI === 'cli');
define('IS_WEB', !IS_CONSOLE);

if( isset($SRCPATHS) ) {
	$t = $SRCPATHS;
	unset($SRCPATHS);
}
require_once 'loader.php';

/**
 * The access path, this is independant from the type of access (http, console...)
 * It defines from which folder you access to your application
 */
defifn('ACCESSPATH', dirpath($_SERVER['SCRIPT_FILENAME']));

/**
 * The path to the instance file, this file is optional.
 * This file allows you configure an instance of this application, you could use it to define the DEV_VERSION
 */
defifn('INSTANCEFILEPATH', findFileInTree('instance.php', ACCESSPATH));

if( file_exists(INSTANCEFILEPATH) ) {
	require_once INSTANCEFILEPATH;
}

if( !ini_get('date.timezone') ) {
	// if( !date_default_timezone_get() || date_default_timezone_get() === 'UTC' ) {
	// Set to avoid some PHP warnings
	date_default_timezone_set('UTC');
}

/*
 * Orpheus is able to separate your application sources from its own sources.
 * This allows you to use a common Orpheus source folder for multiple applications, composer is associated to one application.
 * For each application, you are also able to use multiple instances of this application with specific configuration, logs & store.
 * These constants take care about paths through symbolic links.
 */
/**
 * The Orpheus sources
 *
 * @const ORPHEUSPATH The folder to find Orpheus sources
 * Default is the current file one
 */
defifn('ORPHEUSPATH', dirpath(dirname(ACCESSPATH)));

/**
 * The Application sources
 *
 * @const ORPHEUSPATH The folder to find your Application sources
 * Default is Orpheus path
 */
defifn('APPLICATIONPATH', ORPHEUSPATH);

/**
 * The Instance sources
 *
 * @const ORPHEUSPATH The folder containing the instances configuration (may not contain any source)
 * Default is Application path
 */
defifn('INSTANCEPATH', APPLICATIONPATH);

addSrcPath(ORPHEUSPATH);
addSrcPath(APPLICATIONPATH);
addSrcPath(INSTANCEPATH);
if( isset($t) ) {
	foreach( $t as $path ) {
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

defifn('DEV_VERSION', !IS_WEB);// True in all cases but web access

defifn('CHECK_MODULE_ACCESS', true);
defifn('TIME', $_SERVER['REQUEST_TIME']);

// Useful paths
defifn('CONFDIR', 'configs/');
defifn('MODDIR', 'modules/');
defifn('LIBSDIR', 'libs/');
defifn('THEMES_FOLDER', 'themes');

defifn('SRC_PATH', 'src');
defifn('LOGSPATH', pathOf('logs/'));
defifn('STOREPATH', pathOf('store/'));
defifn('CACHEPATH', STOREPATH . 'cache/');

// Routing
defifn('HTTPS', !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] === 'https' : (defined('DEFAULT_IS_SECURE') && DEFAULT_IS_SECURE));
defifn('SCHEME', HTTPS ? 'https' : 'http');
defifn('HOST', !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DEFAULTHOST);
defifn('PATH', !defined('TERMINAL') ? dirpath($_SERVER['SCRIPT_NAME']) : DEFAULTPATH);
defifn('SITEROOT', SCHEME . '://' . HOST . PATH);
defifn('WEB_ROOT', SCHEME . '://' . HOST . (PATH !== '/' ? PATH : ''));
defifn('DEFAULTLINK', SITEROOT);

// Edit the global constants
try {
	require_once CONSTANTSPATH;
} catch( Exception $e ) {
	if( !defined('DEV_VERSION') || !DEV_VERSION ) {
		displayException($e, 'Loading constants');
	} else {
		die('A fatal error occurred.');
	}
}

// Static medias
defifn('JSURL', SITEROOT . 'js/');
defifn('THEMES_URL', SITEROOT . THEMES_FOLDER);

if( !defined('INSTANCE_ID') && defined('HOST') ) {
	// INSTANCE ID to differentiate instances (used by cache)
	defifn('INSTANCE_ID', HOST);
}

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
	function ($errno, $errstr, $errfile, $errline) {
		$exception = new ErrorException($errstr, 0, $errno, $errfile, $errline);
		if( empty($GLOBALS['NO_EXCEPTION']) && (empty($GLOBALS['ERROR_ACTION']) || $GLOBALS['ERROR_ACTION'] == ERROR_THROW_EXCEPTION) ) {//ERROR_THROW_EXCEPTION
			throw $exception;
		} elseif( !empty($GLOBALS['ERROR_ACTION']) && $GLOBALS['ERROR_ACTION'] == ERROR_IGNORE ) {//ERROR_IGNORE
			return;
		} else {//ERROR_DISPLAY_RAW
			if( !function_exists('log_error') ) {
				if( DEV_VERSION ) {
					displayException($exception, null);
				} else {
					die('A fatal error occurred.');
				}
			}
			log_error($exception, 'Handling error', true);
		}
	});

$DEBUG_BACKTRACE = null;
function storeBackTrace() {
	global $DEBUG_BACKTRACE;
	// Get backtrace & remove first line (this call)
	$DEBUG_BACKTRACE = array_slice(debug_backtrace(), 1);
}

register_shutdown_function(
/**
 * Shutdown Handler
 *
 * System function to handle PHP shutdown and catch uncaught errors.
 */
	function () {
		// If there is an error
		$error = error_get_last();
		
		if( $error ) {
			$exception = new ErrorException($error['message'], 1, $error['type'], $error['file'], $error['line']);
			
			if( !function_exists('log_error') ) {
				if( DEV_VERSION ) {
					displayException($exception, 'Shutdown script');
				} else {
					die('A fatal error occurred.');
				}
			}
			log_error($exception, 'Shutdown script', true);
		}
	});

set_exception_handler(
/**
 * Exception Handler
 *
 * System function to handle all exceptions and stop script execution.
 */
	function ($exception) {
		global $coreAction;
		if( !function_exists('log_error') ) {
			if( DEV_VERSION ) {
				displayException($exception, 'Shutdown script');
			} else {
				die('A fatal error occurred.');
			}
		}
		log_error($exception, $coreAction, true);
	});

$AUTOLOADS = [];

$coreAction = 'initializing_core';

try {
	ob_start();
	
	defifn('VENDORPATH', APPLICATIONPATH . 'vendor/');
	
	// Before lib loading, they can not define it
	// This class MUST extends Orpheus\Config\ConfigCore
	defifn('DEFAULT_CONFIG_CLASS', 'Orpheus\Config\IniConfig');
	
	if( is_file(VENDORPATH . 'autoload.php') ) {
		/* @var Composer\Autoload\ClassLoader $PackageLoader */
		$PackageLoader = require VENDORPATH . 'autoload.php';
	}
	
	if( existsPathOf(SRC_PATH . '/_loader.php', $path) ) {
		require_once $path;
	}
	
	try {
		require_once pathOf('configs/libraries.php');
	} catch( Exception $e ) {
		// Ignore
	}
	
	if( !empty($Libraries) ) {
		foreach( $Libraries as $lib ) {
			if( !existsPathOf(LIBSDIR . $lib . '/_loader.php', $path) ) {
				continue;
			}
			require_once $path;
		}
	}
	
	// App has now loaded all libraries
	Hook::trigger(HOOK_LIBSLOADED);
	
	// After Lib loading
	
	IniConfig::build('engine', false);// Some libs should require to get some configuration.
	
	$RENDERING = Config::get('default_rendering');
	
	ob_end_clean();
	
	// App is now ready to run
	Hook::trigger(HOOK_APPREADY);
	
	// Handle current request
	RequestHandler::handleCurrentRequest(IS_CONSOLE ? RequestHandler::TYPE_CONSOLE : RequestHandler::TYPE_HTTP);
	
} catch( Exception $e ) {
	log_error($e, $coreAction, true);
}
