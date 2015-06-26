<?php
/** \file
 * Declare the libraries you want to use.
 *
 * @page librariesLoader Libraries Loader
 * 
 */

// Libs to call loaders, you could use not included libs but you will have to call using()
// Order is important
$Librairies	= array(
	// Core
	'core',
	'config',
	'hooks',
	// Config
	'initernationalization',
	'yaml',
	// App specific
	'inputcontroller',
	'publisher',
	'rendering',
	// Your sources
	'src',
);