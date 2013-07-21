<?php
/*!
 * \file index.php
 * \brief The Orpheus Core
 * \author Florent Hazard
 * \copyright The MIT License, see LICENSE.txt
 * 
 * PHP File for the website core.
 */

require_once 'loader.php';

// This method take care about paths through symbolic links.
defifn('ORPHEUSPATH', dirpath($_SERVER['SCRIPT_FILENAME']));
defifn('INSTANCEPATH', ORPHEUSPATH);

defifn('CONSTANTSPATH', pathOf('configs/constants.php'));

// Edit the constant file according to the system context (OS, directory tree ...).
require_once CONSTANTSPATH;

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

set_error_handler(
//! Error Handler
/*!
	System function to handle PHP errors and convert it into exceptions.
*/
function($errno, $errstr, $errfile, $errline ) {
	if( empty($GLOBALS['NO_EXCEPTION']) ) {
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	} else {
		$backtrace = '';
		foreach( debug_backtrace() as $trace ) {
			if( !isset($trace['file']) ) {
				$trace['file'] = $trace['line'] = 'N/A';
			}
			$backtrace .= '
'.$trace['file'].' ('.$trace['line'].'): '.$trace['function'].'('.print_r($trace['args'], 1).')<br />';
		}
		if( !function_exists('sys_error') ) {
			die($errstr."<br />\n{$backtrace}");
		}
		sys_error($errstr."<br />\n{$backtrace}");
		die("A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.");
	}
});

register_shutdown_function(
//! Shutdown Handler
/*!
	System function to handle PHP shutdown and catch uncaught errors.
*/
function() {
	if( $error = error_get_last() ) {
		switch($error['type']){
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR: {
				ob_end_clean();
				$message = $error['message'].' in '.$error['file'].' ('.$error['line'].')';
				if( !function_exists('sys_error') ) {
					die($message);
				}
				sys_error($message);
				die("A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.");
				break;
			}
		}
	}
});

set_exception_handler(
//! Exception Handler
/*!
	System function to handle all exceptions and stop script execution.
 */
function($e) {
	if( !function_exists('sys_error') ) {
		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	die('A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez réessayer plus tard.');
});

//! Includes a directory
/*!
	\param $dir The directory to include.
	\param $importants The files in that are importants to load first.
	\return The number of files included.
	
	Includes all files with a name beginning by '_' in the directory $dir.
	It browses recursively through sub-directories.
*/
function includeDir($dir, $importants=array()) {
	//Require to be immediatly available.
	$files = array_unique(array_merge($importants, scandir($dir)));
	
	$i=0;
	foreach($files as $file) {
		// If file is not readable or hidden, we pass.
		if( !is_readable($dir.$file) || $file[0] == '.' ) {
			continue;
		}
		//We don't check infinite file system loops.
		if( is_dir($dir.$file) ) {
			$i += includeDir($dir.$file.'/');
		} else if( $file[0] == '_' ) {
			require_once $dir.$file;
			$i++;
		}
	}
	return $i;
}

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
		global $AUTOLOADS, $AUTOLOADSFROMCONF;
		// In the first __autoload() call, we try to load the autoload config from file.
		if( !isset($AUTOLOADSFROMCONF) && class_exists('Config') ) {
			$alConf = Config::build('autoload', true);
			$AUTOLOADS = array_merge($AUTOLOADS, $alConf->all);
			$AUTOLOADSFROMCONF = true;
		}
		// PHP's class' names are not case sensitive.
		$bFile = strtolower($className);
		
		// If the class file path is known in the AUTOLOADS array
		if( !empty($AUTOLOADS[$bFile]) ) {
			if( is_readable(LIBSPATH.$AUTOLOADS[$bFile]) ) {
				// if the path is a directory, we search the class file into this directory.
				if( is_dir(LIBSPATH.$AUTOLOADS[$bFile]) ) {
					if( is_readable(LIBSPATH.$AUTOLOADS[$bFile].$bFile.'_class.php') ) {
						require_once LIBSPATH.$AUTOLOADS[$bFile].$bFile.'_class.php';
						return;
					}
				// if the path is a file, we include the class file.
				} else {
					require_once LIBSPATH.$AUTOLOADS[$bFile];
					return;
				}
			}
			throw new Exception("Bad use of Autoloads. Please use addAutoload().");
			
		// If the class file is directly in the libs directory
		} else if( is_readable(LIBSPATH.$bFile.'_class.php') ) {
			require_once LIBSPATH.$bFile.'_class.php';
			
			
		// If the class file is in a eponymous sub directory in the libs directory
		} else if( is_readable(LIBSPATH.$bFile.'/'.$bFile.'_class.php') ) {
			require_once LIBSPATH.$bFile.'/'.$bFile.'_class.php';
			
		// If the class name is like Package_ClassName, we search the class file "classname" in the "package" directory in libs/.
		} else {
			$classExp = explode('_', $bFile, 2);
			if( count($classExp) > 1 && is_readable(LIBSPATH.$classExp[0].'/'.$classExp[1].'_class.php') ) {
				require_once LIBSPATH.$classExp[0].'/'.$classExp[1].'_class.php';
				return;
			}
			// NOT FOUND
			//Some libs could add their own autoload function.
			//throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		@sys_error("$e", 'loading_class_'.$className);
		die('A fatal error occured loading libraries.');
	}
}, true, true );// End of spl_autoload_register()

$AUTOLOADS = array();
$Module = $Page = '';// Useful for initializing errors.

$coreAction = 'initializing_core';

try {
	includeDir(LIBSPATH.'core/');// Load engine Core
	
	includeDir(CONFPATH);// Require to be loaded before libraries to get hooks.
	
	Config::build('engine');// Some libs should require to get some configuration.
	
	includeDir(LIBSPATH);// Require some hooks.
	
	// Here starts Hooks and Session too.
	Hook::trigger('startSession');

	if( !defined('TERMINAL') ) {
		$NO_EXCEPTION = 1;
	
		//PHP is unable to manage exception thrown during session_start()
		session_start();
		if( !isset($_SESSION['ORPHEUS']) ) {
			log_debug('New session, $_SESSION[ORPHEUS] unset');
			$_SESSION['ORPHEUS'] = array('LAST_REGENERATEID' => 0);
		}
		if( version_compare(PHP_VERSION, '4.3.3', '>=') ) {
			// Only version >= 4.3.3 can regenerate session id without losing data
			//http://php.net/manual/fr/function.session-regenerate-id.php
			if( TIME-$_SESSION['ORPHEUS']['LAST_REGENERATEID'] > 600 ) {
				log_debug('Regenerating Session ID');
				$_SESSION['ORPHEUS']['LAST_REGENERATEID'] = TIME;
				session_regenerate_id();
			}
		}
	
		$NO_EXCEPTION = 0;
	}
	
	// Checks and Gets Action.
	$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;
	$Format = ( !empty($_GET['format']) && is_name($_GET['format'], 50, 2) ) ? $_GET['format'] : 'html';
	
	Hook::trigger('checkModule');
	
	if( empty($_GET['module']) ) {
		$Module = DEFAULTMOD;
	} else {
		$Module = $_GET['module'];
	}
	if( empty($Module) || !is_name($Module) ) {
		throw new UserException('invalidModuleName');
	}
	if( !is_readable(MODPATH.$Module.'.php') ) {
		throw new UserException('inexistantModule');
	}
	$coreAction = 'running_'.$Module;
	$Module = Hook::trigger('runModule', false, $Module);
	define('OBLEVEL_INIT', ob_get_level());
	ob_start();
	require_once MODPATH.$Module.'.php';
	$Page = ob_get_contents();
	ob_end_clean();
	
} catch(UserException $e) {
	reportError($e);
	$Page = getReportsHTML();
	
} catch(Exception $e) {
	if( defined('OBLEVEL_INIT') && ob_get_level() > OBLEVEL_INIT ) {
		$Page = ob_get_contents();
		ob_end_clean();
	}
	if( !function_exists('sys_error') ) {
		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	ob_start();
	sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	$Page = ob_get_contents();
	ob_end_clean();
}

try {
	$coreAction = 'displaying_'.$Module;
	if( class_exists('Hook') ) {
		Hook::trigger('showRendering', true);
	}
	Rendering::doShow();//Generic final display.
	
} catch(Exception $e) {
	@sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	die('A fatal display error occured.');
}