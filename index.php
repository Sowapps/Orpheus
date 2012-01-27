<?php
/* index.php
 * PHP File for the Index: The WebSite Core.
 *
 * Auteur: Florent Hazard.
 */

//Edit it according system context (OS, directory tree ...).
require_once 'config/constants.php';

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

function includeDir($dir) {
	$files = scandir($dir);
	$i=0;
	foreach($files as $file) {
		if( $file[0] == '_' ) {
			require_once $dir.DS.$file;
			$i++;
		}
	}
	return $i;
}

function __autoload($className) {
	$bFile = strtolower($className);
	if( is_readable(LIBSPATH.$bFile.'_class.php') ) {
		require_once LIBSPATH.$bFile.'_class.php';
	} else if( is_readable(LIBSPATH.$bFile.DS.$bFile.'_class.php') ) {
		require_once LIBSPATH.$bFile.DS.$bFile.'_class.php';
	} else {
		throw new Exception("Unable to load lib \"{$className}\"");
	}
}

Config::build('engine');

includeDir(CONFPATH);
includeDir(LIBSPATH);

//Here start Hooks and Session too.
Hook::trigger('startSession');

session_start();

//On récupère l'action s'il y a en vérifiant qu'elle soit valide.
$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;

$Page = '';

Hook::trigger('checkModule');

if( !empty($_GET['module']) && is_name($_GET['module']) && file_exists(MODPATH.$_GET['module'].'.php') ) {
	$Module = $_GET['module'];
} else {
	$Module = DEFAULTMOD;
}

$Module = Hook::trigger('runModule', $Module);

try {
	ob_start();
	require_once MODPATH.$Module.'.php';
	$Page = ob_get_contents();
	ob_end_clean();
} catch(Exception $e) {
	@ob_end_clean();
	sys_error("$e", "running_".$Module);
	$Page = '
<span class="error">Une erreur fatale est survenue et ne peut pas être prise en charge, <a href="'.DEFAULTLINK.'">il est impossible de continuer dans cette situation.</a</span>';
}

Hook::trigger('showRendering');
Rendering::doShow();//Generic final display.
?>