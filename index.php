<?php
/*!
	\file index.php
	\brief The WebSite Core
	\author Florent Hazard
	\copyright The MIT License, see LICENSE.txt
	
	PHP File for the website core.
 */

if( !defined('ORPHEUSPATH') ) {
	define('ORPHEUSPATH', './');
}

// Edit the constant file according to the system context (OS, directory tree ...).
require_once ORPHEUSPATH.'configs/constants.php';

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

//! Error Handler
/*!
	System function to handle PHP errors and convert it into exceptions.
*/
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

//! Include a directory
/*!
	\param $dir The directory to include.
	\return The number of files included.
	
	Include all files with a name beginning by '_' in the directory $dir.
*/
function includeDir($dir) {
	//Require to be immediatly available.
	$files = scandir($dir);
	$i=0;
	echo "Scanning $dir<br />";
	foreach($files as $file) {
		if( $file[0] == '_' ) {
			//We don't check infinite file system loops.
			echo "Importing {$dir}{$file}.<br />";
			if( !is_dir($dir.$file) ) {
				require_once $dir.$file;
				$i++;
			} else if( is_readable($dir.$file) ) {
				$i += includeDir($dir.$file.'/');
			}
		}
	}
	echo "Imported $i files.<br />";
	return $i;
}


//! Class autoload function
/*!
	\param $className The classname not loaded yet.
	\todo Add Array mapping
	
	Include the file according to the classname in lowercase and suffixed by '_class.php'.
	First, search in the libs directory, then, always in subdirectory, in the eponym directory.
	And finally, in the parent directory (replace first '_' by '/')
	e.g For Parent_MyClass, it searches 'libs/parent_myclass_class.php', 'libs/parent_myclass/parent_myclass_class.php', 'libs/parent/myclass_class.php'
	The script stops if the class file is not found.
*/
function __autoload($className) {
	try {
		global $AUTOLOADS, $AUTOLOADSFROMCONF;
		// In the first __autoload() call, we try to load the autoload config from file.
		if( !isset($AUTOLOADSFROMCONF) && class_exists('Config') ) {
			$alConf = Config::build('autoload', true);
			$AUTOLOADS = array_merge($AUTOLOADS, $alConf->all);
			$AUTOLOADSFROMCONF = true;
		}
		// PHP's class name are not case sensitive.
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
			
		// If the class name is like Prefix_ClassName, we search the class file "classname" in the "prefix" directory in libs/.
		} else {
			$classExp = explode('_', $bFile, 2);
			if( count($classExp) > 1 && is_readable(LIBSPATH.$classExp[0].'/'.$classExp[1].'_class.php') ) {
				require_once LIBSPATH.$classExp[0].'/'.$classExp[1].'_class.php';
				return;
			}
			throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		@sys_error("$e", 'loading_class_'.$className);
		die('A fatal error occured loading libraries.');
	}
}
$AUTOLOADS = array();
$Module = '';// Useful for initializing errors.

$coreAction = 'initializing_core';
try {
	echo "Init<br />";
	
	includeDir(CONFPATH);//Require to be loaded before libraries to get hooks. 
	echo "CONFPATH loaded<br />";
	
	includeDir(LIBSPATH);//Require some hooks.
	echo "LIBSPATH loaded<br />";
	
	Config::build('engine');
	echo "Enfinge config builded.<br />";
	
	//Here start Hooks and Session too.
	Hook::trigger('startSession');
	
	session_start();
	
	//Check and Get Action.
	$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;
	
	$Page = '';
	
	Hook::trigger('checkModule');
	
	if( empty($_GET['module']) ) {
		$Module = DEFAULTMOD;
	} else {
		$Module = $_GET['module'];
	}
	if( !is_name($Module) ) {
		throw new Exception('invalidModuleName');
	}
	if( !is_readable(MODPATH.$Module.'.php') ) {
		throw new Exception('inexistantModule');
	}

	$coreAction = 'running_'.$Module;
	$Module = Hook::trigger('runModule', $Module);
	define('OBLEVEL_INIT', ob_get_level());
	ob_start();
	require_once MODPATH.$Module.'.php';
	$Page = ob_get_contents();
	ob_end_clean();
	
} catch(Exception $e) {
	if( defined('OBLEVEL_INIT') && ob_get_level() > OBLEVEL_INIT ) {
		ob_end_clean();
	}
	sys_error("$e", $coreAction);
	$Page = '
<div class="error">A fatal error occured and can not be supported, <a href="'.DEFAULTLINK.'">unable to continue.</a></div>
<div class="logs" style="display: none;">
Error is \''.$e->getMessage().'\'.
</div>';
}

try {
	$coreAction = 'displaying_'.$Module;
	Hook::trigger('showRendering');
	Rendering::doShow();//Generic final display.
	
} catch(Exception $e) {
	@sys_error("$e", $coreAction);
	die('A fatal display error occured.');
}
?>