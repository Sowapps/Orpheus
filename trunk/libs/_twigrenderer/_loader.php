<?php
/* Loader File for the twig sources
 * 
 * Twig is a template engine for PHP developed by SensioLabs.
 */

if( strtolower(Config::get('default_rendering')) != 'twigrendering' ) {
	return;// We don't want to load a not used library.
}

addAutoload('TwigRendering', '_twigrendering/twigrendering_class.php');

require_once dirname(__FILE__).'Twig/lib/Twig/Autoloader.php';

Twig_Autoloader::register();

TwigRendering::init();