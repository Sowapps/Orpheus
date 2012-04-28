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

//Edit it according system context (OS, directory tree ...).
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
	text("scanning dir: $dir");
	//Require to be immediatly available.
	$files = scandir($dir);
	$i=0;
	foreach($files as $file) {
		if( $file[0] == '_' ) {
			//We don't check infinite file system loops.
			if( !is_dir($dir) ) {
				require_once $dir.'/'.$file;
				$i++;
			} else if( is_readable($dir) ) {
				$i += includeDir($dir.'/'.$file);
			}
		}
	}
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
		$bFile = strtolower($className);
		if( is_readable(LIBSPATH.$bFile.'_class.php') ) {
			require_once LIBSPATH.$bFile.'_class.php';
		} else if( is_readable(LIBSPATH.$bFile.DS.$bFile.'_class.php') ) {
			require_once LIBSPATH.$bFile.DS.$bFile.'_class.php';
		} else {
			$classExp = explode('_', $bFile, 2);
			if( count($classExp) > 1 && is_readable(LIBSPATH.$classExp[0].DS.$classExp[1].'_class.php') ) {
				require_once LIBSPATH.$classExp[0].DS.$classExp[1].'_class.php';
				return;
			}
			throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		die($e);
	}
}

Config::build('engine');

includeDir(CONFPATH);
includeDir(LIBSPATH);

//Here start Hooks and Session too.
Hook::trigger('startSession');

session_start();

//Check and Get Action.
$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;

$Page = '';

Hook::trigger('checkModule');

if( !empty($_GET['module']) && is_name($_GET['module']) && file_exists(MODPATH.$_GET['module'].'.php') ) {
	$Module = $_GET['module'];
} else {
	$Module = DEFAULTMOD;
}

$Module = Hook::trigger('runModule', $Module);
$debug = '';
$debug .= "
Module: \"{$Module}\"";
$debug .= "
ob_get_level() out: \"".ob_get_level()."\"<br />";

try {
	if( strpos($Module, DS) !== false ) {
		throw new Exception("invalidModuleName");
	}
	if( !is_readable(MODPATH.$Module.'.php') ) {
		throw new Exception("inexistantModule");
	}
	define('OBLEVEL_INIT', ob_get_level());
	ob_start();
	require_once MODPATH.$Module.'.php';
	$Page = ob_get_contents();
	ob_end_clean();
} catch(Exception $e) {
	$debug .= "
ob_get_level() exc: \"".ob_get_level()."\"<br />";
	if( ob_get_level() > OBLEVEL_INIT ) {
		ob_end_clean();
	}
	sys_error("$e", "running_".$Module);
	$Page = '
<div class="error">A fatal error occured and can not be supported, <a href="'.DEFAULTLINK.'">unable to continue.</a></div>
<div class="logs" style="display: none;">
Error is \''.$e->getMessage().'\'.
Debug is:'.$debug.'.
</div>';
}

Hook::trigger('showRendering');
Rendering::doShow();//Generic final display.
?>