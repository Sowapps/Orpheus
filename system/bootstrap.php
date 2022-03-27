<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

use Orpheus\Config\IniConfig;
use Orpheus\Core\RequestHandler;
use Orpheus\Hook\Hook;


define('IS_CONSOLE', PHP_SAPI === 'cli');
define('IS_WEB', !IS_CONSOLE);

if( isset($APP_SOURCE_PATHS) ) {
	$t = $APP_SOURCE_PATHS;
	unset($APP_SOURCE_PATHS);
}
require_once 'loader.php';

/**
 * The access path, this is independent of the type of access (http, console...)
 * It defines from which folder you access to your application
 *
 * @var string
 */
defifn('ACCESS_PATH', dirpath($_SERVER['SCRIPT_FILENAME']));

/**
 * The path to the instance file, this file is optional.
 * This file allows you to configure an instance of this application, you could use it to define the DEV_VERSION
 */
defifn('INSTANCE_FILE_PATH', findFileInTree('instance.php', ACCESS_PATH));

if( file_exists(INSTANCE_FILE_PATH) ) {
	require_once INSTANCE_FILE_PATH;
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
 * @const ORPHEUS_PATH The folder to find Orpheus sources
 * Default is the current file one
 */
defifn('ORPHEUS_PATH', dirpath(dirname(ACCESS_PATH)));

/**
 * The Application sources
 *
 * @const ORPHEUS_PATH The folder to find your Application sources
 * Default is Orpheus path
 */
defifn('APPLICATION_PATH', ORPHEUS_PATH);

/**
 * The Instance sources
 *
 * @const ORPHEUS_PATH The folder containing the instances configuration (may not contain any source)
 * Default is Application path
 */
defifn('INSTANCE_PATH', APPLICATION_PATH);

addSrcPath(ORPHEUS_PATH);
addSrcPath(APPLICATION_PATH);
addSrcPath(INSTANCE_PATH);
if( isset($t) ) {
	foreach( $t as $path ) {
		addSrcPath($path);
	}
	unset($t);
}

defifn('CONSTANTS_FILE_PATH', pathOf('/configs/constants.php'));
defifn('DEFAULTS_FILE_PATH', pathOf('/configs/defaults.php', true));

// Edit the constant file according to the system context (OS, directory tree ...).
if( DEFAULTS_FILE_PATH !== null ) {
	require_once DEFAULTS_FILE_PATH;
}

defifn('DEV_VERSION', !IS_WEB);// True in all cases but web access

defifn('CHECK_MODULE_ACCESS', true);
defifn('TIME', $_SERVER['REQUEST_TIME']);

// Useful paths
defifn('CONFIG_FOLDER', '/configs');
defifn('LIBRARY_FOLDER', '/libs');
defifn('THEMES_FOLDER', '/themes');
defifn('LANG_FOLDER', '/languages');

defifn('SRC_PATH', '/src');
defifn('LOGS_PATH', pathOf('/logs'));
defifn('STORE_PATH', pathOf('/store'));
defifn('CACHE_PATH', STORE_PATH . '/cache');
defifn('TEMP_PATH', STORE_PATH . '/temp');
defifn('FILE_STORE_PATH', STORE_PATH . '/files');

// Defaults
if( !defined('DEFAULT_PATH') ) {
	define('DEFAULT_PATH', '/');
}

// Routing
defifn('HTTPS', !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] === 'https' : (defined('DEFAULT_IS_SECURE') && DEFAULT_IS_SECURE));
defifn('SCHEME', HTTPS ? 'https' : 'http');
defifn('HOST', !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DEFAULT_HOST);
// Web path to app, by default, always "/", you must define it manually to change it in defaults.php file
// This is used to generate URL from routes
// This is also used by session's cookie, so session won't be available out of this path
defifn('PATH', DEFAULT_PATH);

defifn('WEB_ROOT', SCHEME . '://' . HOST . (PATH !== '/' ? PATH : ''));

// Edit the global constants
try {
	require_once CONSTANTS_FILE_PATH;
} catch( Exception $exception ) {
	processException($exception, false);
}

// Static medias
defifn('JS_URL', WEB_ROOT . 'js/');
defifn('THEMES_URL', WEB_ROOT . THEMES_FOLDER);

if( !defined('INSTANCE_ID') && defined('HOST') ) {
	// INSTANCE ID to differentiate instances (used by cache)
	defifn('INSTANCE_ID', HOST);
}

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

// Error Actions
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
	function ($type, $message, $file, $line) {
		$exception = new ErrorException($message, 0, $type, $file, $line);
		if( empty($GLOBALS['NO_EXCEPTION']) && (empty($GLOBALS['ERROR_ACTION']) || $GLOBALS['ERROR_ACTION'] === ERROR_THROW_EXCEPTION) ) {
			// ERROR_THROW_EXCEPTION
			throw $exception;
		} elseif( !empty($GLOBALS['ERROR_ACTION']) && $GLOBALS['ERROR_ACTION'] === ERROR_IGNORE ) {
			// ERROR_IGNORE
			return;
		} else {
			// ERROR_DISPLAY_RAW
			processException($exception);
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
			processException(getErrorException($error));
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
		processException($exception, $coreAction);
	});

$AUTOLOADS = [];

$coreAction = 'initializing_core';

try {
	ob_start();
	
	defifn('VENDOR_PATH', APPLICATION_PATH . '/vendor');
	
	// Before lib loading, they can not define it
	// This class MUST extends Orpheus\Config\ConfigCore
	defifn('DEFAULT_CONFIG_CLASS', 'Orpheus\Config\IniConfig');
	
	if( is_file(VENDOR_PATH . '/autoload.php') ) {
		/* @var Composer\Autoload\ClassLoader $PackageLoader */
		$PackageLoader = require VENDOR_PATH . '/autoload.php';
	}
	
	if( existsPathOf(SRC_PATH . '/_loader.php', $path) ) {
		require_once $path;
	}
	
	try {
		require_once pathOf('/configs/libraries.php');
	} catch( Exception $e ) {
		// Ignore
	}
	
	if( !empty($Libraries) ) {
		foreach( $Libraries as $lib ) {
			if( !existsPathOf(LIBRARY_FOLDER . '/' . $lib . '/_loader.php', $path) ) {
				continue;
			}
			require_once $path;
		}
	}
	
	// App has now loaded all libraries
	Hook::trigger(HOOK_LIBSLOADED);
	
	// After Lib loading
	
	IniConfig::build('engine', false);// Some libs should require to get some configuration
	
	ob_end_clean();
	
	// App is now ready to run
	Hook::trigger(HOOK_APPREADY);
	
	// Handle current request
	RequestHandler::handleCurrentRequest(IS_CONSOLE ? RequestHandler::TYPE_CONSOLE : RequestHandler::TYPE_HTTP);
	
} catch( Exception $exception ) {
	processException($exception, $coreAction);
}
