<?php
/* Loader File for the twig sources
 * 
 * Twig is a template engine for PHP developed by SensioLabs.
 */

if( function_exists('log_debug') ) {
	log_debug("TwigRendering loader: Checkin if we should load it.");
}
if( strtolower(Config::get('default_rendering')) != 'twigrendering' ) {
	return;// We don't want to load a not used library.
}

addAutoload('TwigRendering', '_twigrenderer/twigrendering_class.php');
text($AUTOLOADS);
text('TwigRendering loader: TwigRendering loaded.');
if( function_exists('log_debug') ) {
	log_debug("TwigRendering loader: TwigRendering loaded.");
}

require_once dirname(__FILE__).'/Twig/lib/Twig/Autoloader.php';

Twig_Autoloader::register();

TwigRendering::init();