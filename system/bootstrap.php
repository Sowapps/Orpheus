<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 *
 * Orpheus is able to separate your application sources from its own sources.
 * This allows you to use a common Orpheus source folder for multiple applications, composer is associated to one application.
 * For each application, you are also able to use multiple instances of this application with specific configuration, logs & store.
 * These constants take care about paths through symbolic links.
 */

use Orpheus\Core\RequestHandler;
use Orpheus\Service\ApplicationKernel;


const IS_CONSOLE = PHP_SAPI === 'cli';
const IS_WEB = !IS_CONSOLE;

require_once 'loader.php';

if( !ini_get('date.timezone') ) {
	// Set to fix some PHP warnings
	date_default_timezone_set('UTC');
}

/**
 * The access path, this is independent of the type of access (http, console...)
 * It defines from which folder you access to your application
 */
defifn('ACCESS_PATH', getParentPath($_SERVER['SCRIPT_FILENAME']));

/**
 * The Orpheus sources
 *
 * @const ORPHEUS_PATH The folder to find Orpheus sources
 * Default is the current file one
 */
defifn('ORPHEUS_PATH', getParentPath(dirname(ACCESS_PATH)));

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

$defaultFilePath = safeConstant('DEFAULTS_FILE_PATH') ?? pathOf('/config/defaults.php', true);

// Edit the constant file according to the system context (OS, directory tree ...).
if( $defaultFilePath ) {
	require_once $defaultFilePath;
}
unset($defaultFilePath);

defifn('CHECK_MODULE_ACCESS', true);
defifn('TIME', $_SERVER['REQUEST_TIME']);

// Useful paths
defifn('CONFIG_FOLDER', '/config');
defifn('THEMES_FOLDER', '/themes');
defifn('LANG_FOLDER', '/languages');

defifn('SRC_PATH', '/src');
defifn('STORE_PATH', pathOf('/store'));
defifn('CACHE_PATH', STORE_PATH . '/cache');
defifn('LOGS_PATH', STORE_PATH . '/logs');
defifn('TEMP_PATH', STORE_PATH . '/temp');
defifn('FILE_STORE_PATH', STORE_PATH . '/files');
const THEMES_PATH = ACCESS_PATH . THEMES_FOLDER;

// Defaults
if( !defined('DEFAULT_PATH') ) {
	define('DEFAULT_PATH', '/');
}

// Routing
defifn('HTTPS', !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] === 'https' : (defined('DEFAULT_IS_SECURE') && DEFAULT_IS_SECURE));
defifn('SCHEME', HTTPS ? 'https' : 'http');
defifn('HOST', $_SERVER['HTTP_HOST'] ?? DEFAULT_HOST);
// Web path to app, by default, always "/", you must define it manually to change it in defaults.php file
// This is used to generate URL from routes
// This is also used by session's cookie, so session won't be available out of this path
defifn('PATH', DEFAULT_PATH);

defifn('WEB_ROOT', SCHEME . '://' . HOST . (PATH !== '/' ? PATH : ''));

// Edit the global constants
try {
	require_once safeConstant('CONSTANTS_FILE_PATH') ?? pathOf('/config/constants.php');
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

error_reporting(safeConstant('ERROR_LEVEL') ?? ERROR_DEBUG_LEVEL);

// Error Actions
const ERROR_THROW_EXCEPTION = 0;
const ERROR_DISPLAY_RAW = 1;
const ERROR_IGNORE = 2;
$ERROR_ACTION = ERROR_THROW_EXCEPTION;

set_error_handler(
/**
 * Handle PHP errors and convert it into exceptions.
 * @throws ErrorException
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
function storeBackTrace(): void {
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
		/** @noinspection PhpIncludeInspection */
		$PackageLoader = require VENDOR_PATH . '/autoload.php';
	}
	
	if( existsPathOf(SRC_PATH . '/_loader.php', $path) ) {
		require_once $path;
	}
	
	// Orpheus' libraries are started by Application kernel
	// Any operation from package (core included) should pass by an Orpheus library
	$kernel = ApplicationKernel::get();
	$kernel->configure();
	$kernel->start();
	
	ob_end_clean();
	
	if( $kernel->getEnvironment() !== ApplicationKernel::ENVIRONMENT_TEST ) {
		// Handle current request
		RequestHandler::handleCurrentRequest(IS_CONSOLE ? RequestHandler::TYPE_CONSOLE : RequestHandler::TYPE_HTTP);
	} // Else do not handle request when running unit tests
	
} catch( Exception $exception ) {
	processException($exception, $coreAction);
}
