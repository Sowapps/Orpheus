<?php
/*!
	\file index.php
	\brief The WebSite Core
	\author Florent Hazard
	\copyright The MIT License, see LICENSE.txt
	
	PHP File for the website core.
 */

//! Defines an undefined constant.
/*!
 * \param $name		The name of the constant.
* \param $value	The value of the constant.
* \return True if the constant was defined successfully.

* Defines a constant if this one is not defined yet.
*/
function defifn($name, $value) {
	if( defined($name) ) {
		return false;
	}
	define($name, $value);
	return true;
}

defifn('ORPHEUSPATH', getcwd().'/');
defifn('INSTANCEPATH', ORPHEUSPATH);// Used for logs
defifn('CONSTANTSPATH', ORPHEUSPATH.'configs/constants.php');

// Edit the constant file according to the system context (OS, directory tree ...).
require_once CONSTANTSPATH;

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

//! Error Handler
/*!
	System function to handle PHP errors and convert it into exceptions.
*/
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

//! Includes a directory
/*!
	\param $dir The directory to include.
	\param $importants The files in that are importants to load first.
	\return The number of files included.
	
	Includes all files with a name beginning by '_' in the directory $dir.
	It browses recursively through sub-directories.
*/
function includeDir($dir, $importants=array()) {
	echo "Including $dir<br />\n";
	//Require to be immediatly available.
	$files = array_unique(array_merge($importants, scandir($dir)));
	
	$i=0;
	foreach($files as $file) {
		// If file is not readable or hidden, we pass.
		if( !is_readable($dir.$file) || $file[0] == '.' ) {
			continue;
		}
		//We don't check infinite file system loops.
		echo "$dir$file<br />\n";
		if( is_dir($dir.$file) ) {
			$i += includeDir($dir.$file.'/');
		} else if( $file[0] == '_' ) {
			require_once $dir.$file;
			$i++;
		}
	}
	return $i;
}


// Class autoload function
/*
	\param $className The classname not loaded yet.
	\sa The \ref libraries documentation
	
	Includes the file according to the classname in lowercase and suffixed by '_class.php'.\n
	The script stops if the class file is not found.\n
*/
spl_autoload_register( function($className) {
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
echo __FILE__.' : '.__LINE__."<br />\n";

$AUTOLOADS = array();
$Module = '';// Useful for initializing errors.

$coreAction = 'initializing_core';
try {
	
	includeDir(LIBSPATH.'core/');// Load engine Core
	
	echo __FILE__.' : '.__LINE__."<br />\n";
	includeDir(CONFPATH);// Require to be loaded before libraries to get hooks.
	
	Config::build('engine');// Some libs should require to get some configuration.
	echo __FILE__.' : '.__LINE__."<br />\n";
	
	includeDir(LIBSPATH);// Require some hooks.
	
	echo __FILE__.' : '.__LINE__."<br />\n";
	//Here start Hooks and Session too.
	Hook::trigger('startSession');
	
	session_start();
	
	//Check and Get Action.
	$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;
	$Format = ( !empty($_GET['format']) && is_name($_GET['format'], 50, 2) ) ? $_GET['format'] : 'html';
	
	$Page = '';
	
	Hook::trigger('checkModule');
	
	if( empty($_GET['module']) ) {
		$Module = DEFAULTMOD;
	} else {
		$Module = $_GET['module'];
	}
	if( empty($Module) || !is_name($Module) ) {
		throw new Exception('invalidModuleName');
	}
	if( !is_readable(MODPATH.$Module.'.php') ) {
		throw new Exception('inexistantModule');
	}
	$coreAction = 'running_'.$Module;
	$Module = Hook::trigger('runModule', false, $Module);
	define('OBLEVEL_INIT', ob_get_level());
	ob_start();
	require_once MODPATH.$Module.'.php';
	$Page = ob_get_contents();
	ob_end_clean();
	
} catch(Exception $e) {
	if( defined('OBLEVEL_INIT') && ob_get_level() > OBLEVEL_INIT ) {
		ob_end_clean();
	}
	if( !function_exists('sys_error') ) {
		die($e->getMessage()."<br />\n".$e->getTraceAsString());
	}
	ob_start();
	sys_error($e->getMessage()."<br />\n".$e->getTraceAsString(), $coreAction);
	$Page = ob_get_contents();
	ob_end_clean();
}

try {
	$coreAction = 'displaying_'.$Module;
	Hook::trigger('showRendering', true);
	Rendering::doShow();//Generic final display.
	
} catch(Exception $e) {
	@sys_error("$e", $coreAction);
	die('A fatal display error occured.');
}
?>