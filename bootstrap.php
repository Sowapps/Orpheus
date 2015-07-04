<?php

// echo '<pre>'.print_r($_SERVER, 1).'</pre>';
// die('Bootstrap ok');
/**
 * @file Bootstrap.php
 * @brief The Orpheus Core
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 * 
 * Website core.
 */

// echo 'Bootstrap<br />';
if( isset($SRCPATHS) ) {
	$t	= $SRCPATHS; unset($SRCPATHS);
}
require_once 'loader.php';

defifn('WEBPATH',			dirname($_SERVER['SCRIPT_FILENAME']).'/');	// The Orpheus sources

// $f	= dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/instance.php';
// echo '__FILE__ : '.__FILE__.'<br />';
// echo '$_SERVER[SCRIPT_FILENAME] : '.$_SERVER['SCRIPT_FILENAME'].'<br />';
$f	= dirname(dirname(WEBPATH)).'/instance.php';
// echo $f.'<br />';
if( file_exists($f) ) {
	require_once $f;
}
unset($f);

if( !date_default_timezone_get() || date_default_timezone_get() === 'UTC' ) {
	// Set to avoid some PHP warnings
	date_default_timezone_set('UTC');
}

// These constants take care about paths through symbolic links.
// defifn('ORPHEUSPATH',		dirpath($_SERVER['SCRIPT_FILENAME']));	// The Orpheus sources
defifn('ORPHEUSPATH',		dirpath(WEBPATH));	// The Orpheus sources
defifn('APPLICATIONPATH',	ORPHEUSPATH);		// The application sources
defifn('INSTANCEPATH',		APPLICATIONPATH);	// The instance sources
// echo 'ORPHEUSPATH : '.ORPHEUSPATH.'<br />';
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
// echo 'DEV_VERSION : '.intval(DEV_VERSION).'<br />';

// Edit the constant file according to the system context (OS, directory tree ...).
require_once CONSTANTSPATH;

defifn('DEV_VERSION',			false);

defifn('CHECK_MODULE_ACCESS',	true);

// Useful paths
defifn('CONFDIR',			'configs/');
defifn('MODDIR',			'modules/');
defifn('LIBSDIR',			'libs/');
defifn('THEMESDIR',			'themes/');

defifn('SRCPATH',			pathOf(LIBSDIR.'src/'));
defifn('LOGSPATH',			pathOf('logs/'));
defifn('STOREPATH',			pathOf('store/'));
defifn('CACHEPATH',			STOREPATH.'cache/');

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
				displayExceptionAsHTML($exception, null);
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
				displayExceptionAsHTML($exception, 'Shutdown script');
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
			displayExceptionAsHTML($exception, 'Shutdown script');
		} else {
			die('A fatal error occurred.');
		}
// 		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	log_error($exception, $coreAction);
// 	log_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
// 	die('A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez réessayer plus tard.');
});

spl_autoload_register(
// Class autoload function
/*
	\param $className The classname not loaded yet.
	\sa The \ref libraries documentation
	
	Includes the file according to the classname in lowercase and suffixed by '_class.php'.\n
	The script stops if the class file is not found.\n
*/
function($className) {
	try {
		debug('spl_autoload called with class '.$className);
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
			$relativePath	= $AUTOLOADS[$bFile];
			if( existsPathOf(LIBSDIR.$relativePath, $path) ) {
				
				// if the path is a directory, we search the class file into this directory.
				if( is_dir($path) ) {
					$relativePath	= 1;
					if( existsPathOf($path.$bFile.'_class.php', $path) ) {
						require_once $path;
					}

				// if the path is a file, we include the class file.
				} else {
					require_once $path;
				}
			}
			if( !class_exists($className, false) ) {
				throw new Exception('Wrong use of Autoloads. Please use addAutoload().');
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
			
		// If the class name is like Package_ClassName, we search the class file "classname" in the "package" directory in libs/.
		} else {
			$classExp = explode('_', $bFile, 2);
			if( count($classExp) > 1 && existsPathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php') ) {
				require_once pathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php');
				return;
			}
			// NOT FOUND
			//Some libs could add their own autoload function.
			//throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		log_error("$e", 'loading_class_'.$className);
// 		die('A fatal error occured loading libraries.');
	}
}, true, true );// End of spl_autoload_register()

$AUTOLOADS = array();
$Module = $Page = '';// Useful for initializing errors.

$coreAction = 'initializing_core';

try {
	ob_start();
// 	defifn('CORELIB',		'core');
// 	defifn('CONFIGLIB',		'config');
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
		debug('Try to load library '.$lib.' with path '.LIBSDIR.$lib.'/_loader.php');
		if( existsPathOf(LIBSDIR.$lib.'/_loader.php', $path) ) { continue; }
		require_once $path;
		debug('...Loaded !');
	}
	
	defifn('VENDORPATH', APPLICATIONPATH.'vendor/');
	if( file_exists(VENDORPATH.'autoload.php') ) {
		$PackageLoader	= include VENDORPATH.'autoload.php';
	}
	
	Config::build('engine');// Some libs should require to get some configuration.
	
	$RENDERING	= Config::get('default_rendering');
	
// 	$_SERVER['PHP_AUTH_PW']	= '******';
// 	debug('$_SERVER', $_SERVER);
	
	
// 	includePath(LIBSDIR);// Require some hooks.
	
	// Here starts Hooks and Session too.
	Hook::trigger(HOOK_STARTSESSION);

	if( !defined('TERMINAL') ) {

		defifn('SESSION_COOKIE_LIFETIME',	86400*7);
		// Set session cookie parameters, HTTPS session is only HTTPS
		session_set_cookie_params(SESSION_COOKIE_LIFETIME, PATH, HOST, HTTPS, true);

		//PHP is unable to manage exception thrown during session_start()
		$ERROR_ACTION	= ERROR_DISPLAY_RAW;
// 		$NO_EXCEPTION	= 1;
		session_start();
		$ERROR_ACTION	= ERROR_THROW_EXCEPTION;
// 		$NO_EXCEPTION	= 0;
		
// 		text('clientIP() => '.clientIP());
		
// 		debug('$_SESSION start', $_SESSION);
		$initSession	= function() {
// 			die('Init session');
			$_SESSION	= array('ORPHEUS' => array('LAST_REGENERATEID'=>TIME, 'CLIENT_IP'=>clientIP()));
			if( defined('SESSION_VERSION') ) {
				$_SESSION['ORPHEUS']['SESSION_VERSION']	= SESSION_VERSION;
			}
		};
		if( !isset($_SESSION['ORPHEUS']) ) {
			$initSession();
		} else // Outdated session version
		if( defined('SESSION_VERSION') && (!isset($_SESSION['ORPHEUS']['SESSION_VERSION']) || floor($_SESSION['ORPHEUS']['SESSION_VERSION']) != floor(SESSION_VERSION)) ) {
			$initSession();
			throw new UserException('outdatedSession');
		} else // Old session (Will be removed)
		if( !isset($_SESSION['ORPHEUS']['CLIENT_IP']) ) {
			$_SESSION['ORPHEUS']['CLIENT_IP']	= clientIP();
		} else // Hack Attemp' - Session stolen
		if( $_SESSION['ORPHEUS']['CLIENT_IP'] != clientIP() ) {
			$initSession();
			throw new UserException('movedSession');
		}
// 		if( version_compare(PHP_VERSION, '4.3.3', '>=') ) {
// 			// Only version >= 4.3.3 can regenerate session id without losing data
// 			// http://php.net/manual/fr/function.session-regenerate-id.php
// 			if( TIME-$_SESSION['ORPHEUS']['LAST_REGENERATEID'] > 600 ) {
// 				$_SESSION['ORPHEUS']['LAST_REGENERATEID']	= TIME;
// 				session_regenerate_id();
// 			}
// 		}
		unset($initSession);
	
	}
	ob_end_clean();
	
	// Handle current request
	$REQUEST_HANDLER::handleCurrentRequest();
	
	
} catch( Exception $e ) {
	log_error($e, $coreAction, true);
}
