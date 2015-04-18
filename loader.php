<?php
/**
 * @file Orpheus/loader.php
 * @brief The Orpheus Loader
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 * 
 * PHP File for the website core.
 */

if( !isset($SRCPATHS) ) {
	$SRCPATHS = array();
}

define('DEV_LEVEL',			E_ALL | E_STRICT);//Development
define('PROD_LEVEL',		0);//Production

/** Defines an undefined constant.

 * @param $name		The name of the constant.
 * @param $value	The value of the constant.
 * @return True if the constant was defined successfully, else False.
 * 
 *  Defines a constant if this one is not defined yet.
 */
function defifn($name, $value) {
	if( defined($name) ) {
		return false;
	}
	define($name, $value);
	return true;
}

/** Gets the directory path

 * @param $path The path get parent directory
 * @return The secured path
 * @sa dirname()
 * 
 * Gets the parent directory path of $path
 */
function dirpath($path) {
	$dirname = dirname($path);
	return ( $dirname == '/' ) ? '/' : $dirname.'/';
}

/** Gets the path of a file/directory.

 * @param string $commonPath The common path
 * @param boolean $silent Do not throw exception if path does not exist
 * @return The first valid path or null if there is no valid one.
 * @sa addSrcPath()
 * 
 * This function uses global variable $SRCPATHS to get the known paths.
 * It allows developers to get a dynamic path to a file.
 */
function pathOf($commonPath, $silent=false) {
	global $SRCPATHS;
	for( $i=count($SRCPATHS)-1; $i>=0; $i-- ) {
		if( file_exists($SRCPATHS[$i].$commonPath) ) {
			return $SRCPATHS[$i].$commonPath;
		}
	}
	if( $silent ) { return null; }
	throw new Exception('Path not found: '.$commonPath);
}

/** Checks if the path exists.

 * @param string $commonPath The common path.
 * @param string $path The output parameter to get the first valid path.
 * @sa pathOf()
 * 
 * This function uses pathOf() to determine possible path of $commonPath and checks if there is any file with this path in file system.
 */
function existsPathOf($commonPath, &$path=null) {
	return ($path=pathOf($commonPath, true))!==NULL;
}

/** Adds the path to the known paths.

 * @param string $path The source path to add.
 * @return boolean True if the path was added.
 * @see pathOf()
 */
function addSrcPath($path) {
	global $SRCPATHS;
	if( in_array($path, $SRCPATHS) ) {
		return false;
	}
	$SRCPATHS[] = $path;
	return true;
}

/** Includes a directory

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

/** Includes a directory

	\param $path The relative directory path to include.
	\param $importants The files in that are importants to load first.
	\return The number of files included.
	\sa includeDir()
	
	Includes all files with a name beginning by '_' in the directory $dir.
	It browses recursively through sub-directories.
*/
function includePath($path, $importants=array()) {
	return includeDir(pathOf($path), $importants);
}

// Experimental
function ob_end_to($min) {
	$min	= max($min, 0);
	while( ob_get_level() > $min ) {
		ob_end_flush();
	}
}
