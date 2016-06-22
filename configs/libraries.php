<?php
/** \file
 * Declare the libraries you want to use.
 *
 * @page librariesLoader Libraries Loader
 * 
 */

// Libs to call loaders, you could use not included libs but you will have to call using()
// Order is important
$Libraries	= array(
	// Core
// 	'core',
// 	'config',
	'hooks',
// 	'cache',
	// Config
// 	'initernationalization',
	'yaml',
	// App specific
	'sqladapter',
	'inputcontroller',
	'publisher',
	'entitydescriptor',
	'rendering',
	// Additional
	'twigrenderer',
	// Your sources
	'src',
);
