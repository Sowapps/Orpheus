<?php
/*!
 * \file loader.php
 * \brief The Orpheus Loader
 * \author Florent Hazard
 * \copyright The MIT License, see LICENSE.txt
 * 
 * PHP File for the website core.
 */

//! Defines an undefined constant.
/*!
 * \param $name		The name of the constant.
 * \param $value	The value of the constant.
 * \return True if the constant was defined successfully, else False.
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

//! Gets the directory path
/*!
 * \param $path The path get parent directory
 * \return The secured path
 * 
 * Gets the parent directory path of $path
 */
function dirpath($path) {
	$dirname = dirname($path);
	return ( $dirname == '/' ) ? '/' : $dirname.'/';
}

//! Gets the path of a file/directory using a preferred directory.
/*!
 * \param $prefDir The preferred directory path to use.
 * \param $altDir The alternative directory path.
 * \param $commonPath The common path.
 * 
 *  This function tries to return the path to the file $commonPath searching first
 *  in the preferred directory $prefDir and if not found in this one, it searches
 *  into the alternative directory $altDir.
 *  This function does not check if $commonPath file really exists in $altDir directory.
 */
function getPath($prefDir, $altDir, $commonPath) {
	return ( file_exists($prefDir.$commonPath) ) ? $prefDir.$commonPath : $altDir.$commonPath;
}

//! Gets the path of a file/directory.
/*!
 * \param $commonPath The common path.
 *  \see getPath()
 * 
 *  This function uses getPath() with INSTANCEPATH and ORPHEUSPATH as parameters.
 *  It allows developers to get a dynamic path to a file.
 */
function pathOf($commonPath) {
	return getPath(INSTANCEPATH, ORPHEUSPATH, $commonPath);
}