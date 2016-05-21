<?php
/* Loader File for the twig sources
 * 
 * Twig is a template engine for PHP developed by SensioLabs.
 */

addAutoload('TwigRendering', 'twigrenderer/TwigRendering');

require_once dirname(__FILE__).'/Twig/Autoloader.php';
// require_once dirname(__FILE__).'/Twig/lib/Twig/Autoloader.php';

Twig_Autoloader::register();

if( IS_WEB ) {
	TwigRendering::init();
	
	$GLOBALS['TWIG_CSSFOLDERURL']	= TwigRendering::getCSSURL();
}
