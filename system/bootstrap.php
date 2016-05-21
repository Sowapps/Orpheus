<?php
use Orpheus\Core\ClassLoader;
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

// echo 'Bootstrap<br />';
if( isset($SRCPATHS) ) {
	$t	= $SRCPATHS; unset($SRCPATHS);
}
require_once 'loader.php';
require_once 'ClassLoader.php';

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
/** Error Handler

	System function to handle PHP errors and convert it into exceptions.
*/
function($errno, $errstr, $errfile, $errline) {
// 	echo 'error_handler<br />';
// 	die(__FILE__.' : '.__LINE__);
// 	debug('(set_error_handler) Error occurred, ob level : '.ob_get_level());
// 	ob_end_to(1);
// 	debug('(set_error_handler) Decreased ob level : '.ob_get_level());
// 	debug("$errstr in $errfile : $errline");
	$exception	= new ErrorException($errstr, 0, $errno, $errfile, $errline);
	if( empty($GLOBALS['NO_EXCEPTION']) && (empty($GLOBALS['ERROR_ACTION']) || $GLOBALS['ERROR_ACTION']==ERROR_THROW_EXCEPTION) ) {//ERROR_THROW_EXCEPTION
// 		debug('(set_error_handler) Error To Exception');
		throw $exception;
// 		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	} else if( !empty($GLOBALS['ERROR_ACTION']) && $GLOBALS['ERROR_ACTION'] == ERROR_IGNORE ) {//ERROR_IGNORE
		return;
	} else {//ERROR_DISPLAY_RAW
// 		$backtrace = '';
// 		foreach( debug_backtrace() as $trace ) {
// 			if( !isset($trace['file']) ) {
// 				$trace['file'] = $trace['line'] = 'N/A';
// 			}
// 			$backtrace .= '
// '.$trace['file'].' ('.$trace['line'].'): '.$trace['function'].'('.print_r($trace['args'], 1).')<br />';
// 		}
		if( !function_exists('log_error') ) {
			if( DEV_VERSION ) {
				displayException($exception, null);
			} else {
				die('A fatal error occurred.');
			}
// 			die($errstr."<br />\n{$backtrace}");
		}
		log_error($exception);
// 		die('A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.'.(DEV_VERSION ? '<br />Reported in '.__FILE__.' : '.__LINE : ''));
	}
});

register_shutdown_function(
/** Shutdown Handler

	System function to handle PHP shutdown and catch uncaught errors.
*/
function() {
	// If there is an error
	$error = error_get_last();
	
// 	echo '(register_shutdown_function) Shutdown script<br />';
// 	die(__FILE__.' : '.__LINE__);
	if( $error ) {
// 		debug('$error', $error);
// 		debug('back trace', debug_backtrace());
// 		die();
// 		debug('(register_shutdown_function) There is an error');
		// Should be ended by error reporter
// 		if( DEV_VERSION ) {
// 			ob_end_flush();
// 		} else {
// 			ob_end_clean();
// 		}
		$exception	= new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
		if( !function_exists('log_error') ) {
			if( DEV_VERSION ) {
				displayException($exception, 'Shutdown script');
			} else {
				die('A fatal error occurred.');
			}
// 			die( ERROR_LEVEL == DEV_LEVEL ? $error['message'].' in '.$error['file'].' ('.$error['line'].')<br />PAGE:<br /><div style="clear: both;">'.$Page.'</div><br />Reported in '.__FILE__.' : '.__LINE
// 				: "A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.");
		}
		log_error($exception, 'Shutdown script');
	}
});

set_exception_handler(
/** Exception Handler

	System function to handle all exceptions and stop script execution.
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


// Autoload and register when used, you could set your own as first
// ClassLoader::get();
/*
spl_autoload_register(
/**
 * Class autoload function
 * 
 * @param $className The classname not loaded yet.
 * @see The \ref libraries documentation
 * 
 * Include the file according to the classname in lowercase and suffixed by '_class.php'.
* /
function($className) {
	try {
// 		debug('spl_autoload called with class '.$className);
		global $AUTOLOADS;
// 		, $AUTOLOADSFROMCONF;
		// In the first __autoload() call, we try to load the autoload config from file.
// 		if( !isset($AUTOLOADSFROMCONF) && class_exists('Config') ) {
// 			try {
// 				$AUTOLOADSFROMCONF = true;
// 				$alConf = Config::build('autoloads', true);
// 				$AUTOLOADS = array_merge($AUTOLOADS, $alConf->all);
// 			} catch( Exception $e ) {
// 				// Might be not found (default)
// 			}
// 		}
		// PHP's class' names are not case sensitive.
		$bFile = strtolower($className);
		
		// If the class file path is known in the AUTOLOADS array
		if( !empty($AUTOLOADS[$bFile]) ) {
			$path	= null;
// 			$relativePath	= $AUTOLOADS[$bFile];
			$path	= $AUTOLOADS[$bFile];
			require_once $path;
// 			if( existsPathOf(LIBSDIR.$relativePath, $path) ) {
				
// 				// if the path is a directory, we search the class file into this directory.
// 				if( is_dir($path) ) {
// 					$relativePath	= 1;
// 					if( existsPathOf($path.$bFile.'_class.php', $path) ) {
// 						require_once $path;
// 					}

// 				// if the path is a file, we include the class file.
// 				} else {
// 					require_once $path;
// 				}
// 			}
			if( !class_exists($className, false) && !interface_exists($className, false) ) {
				throw new Exception('Wrong use of Autoloads, the class "'.$className.'" should be declared in the given file "'.$path.'". Please use addAutoload() correctly.');
			}
			// We want to do it by another way
// 			if( method_exists($className, 'onClassLoaded') ) {
// 				list($library)	= explode('/', $relativePath);
// 				$className::onClassLoaded((object) array(
// 					'class_fullpath'	=> $path,
// 					'class_relpath'		=> $relativePath,
// 					'library'			=> $library,
// 					'library_path'		=> pathOf(LIBSDIR.$library),
// 				));
// 			}
		
		// NO MORE USED
		// If the class name is like Package_ClassName, we search the class file "classname" in the "package" directory in libs/.
// 		} else {
// 			$classExp = explode('_', $bFile, 2);
// 			if( count($classExp) > 1 && existsPathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php') ) {
// 				require_once pathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php');
// 				return;
// 			}
// 			// NOT FOUND
// 			//Some libs could add their own autoload function.
// 			//throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		log_error($e, 'loading_class_'.$className);
// 		die('A fatal error occured loading libraries.');
	}
}, true, true );// End of spl_autoload_register()
*/

$AUTOLOADS = array();
$Module = $Page = '';// Useful for initializing errors.

$coreAction = 'initializing_core';

try {
	ob_start();
// 	defifn('CORELIB',		'core');
// 	defifn('CONFIGLIB',		'config');
// 	$_SERVER['PHP_AUTH_PW']	= '******';
// 	debug('$_SERVER', $_SERVER);die();
	if( !isset($REQUEST_HANDLER) && !isset($REQUEST_TYPE) ) {

		$REQUEST_TYPE	= IS_CONSOLE ? 'Console' : 'HTTP';
// 		die();
	}
	
	defifn('REQUEST_HANDLER',	isset($REQUEST_HANDLER) ? $REQUEST_HANDLER : $REQUEST_TYPE.'Request');
	$REQUEST_HANDLER	= REQUEST_HANDLER;
// 	unset($REQUEST_HANDLER);
	
// 	defifn('CONSTANTSPATH', pathOf('configs/constants.php'));
// 	// Edit the constant file according to the system context (OS, directory tree ...).
// 	require_once CONSTANTSPATH;
	require_once pathOf('configs/libraries.php');
	
	if( empty($Libraries) ) {
		throw new Exception('Unable to load libraries, the config variable $Libraries is empty, please edit your configs/libraries.php file.');
	}
	
	foreach( $Libraries as $lib ) {
// 		debug('Try to load library '.$lib.' with path '.LIBSDIR.$lib.'/_loader.php');
		if( !existsPathOf(LIBSDIR.$lib.'/_loader.php', $path) ) { continue; }
		require_once $path;
// 		debug('...Loaded !');
	}
	
	defifn('VENDORPATH', APPLICATIONPATH.'vendor/');
	
	if( file_exists(VENDORPATH.'autoload.php') ) {
		$PackageLoader	= require VENDORPATH.'autoload.php';
	}
	
	Config::build('engine');// Some libs should require to get some configuration.
	
	$RENDERING	= Config::get('default_rendering');
	
	// Here starts Hooks and Session too.
	Hook::trigger(HOOK_STARTSESSION);

	if( IS_WEB ) {

		startSession();
	
	}
	ob_end_clean();
	
	// App is now ready to run
	Hook::trigger(HOOK_APPREADY);
	
	// Handle current request
	$REQUEST_HANDLER::handleCurrentRequest();
	
	
} catch( Exception $e ) {
	log_error($e, $coreAction, true);
}
