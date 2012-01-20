<?php
/* index.php
 * PHP File for the Index: The WebSite Core.
 * Index, moteur, coeur principal du site
 *
 * Auteur: Florent Hazard.
 * Revision: 1
 * Dernière édition: 20/01/2012
 */

error_reporting(E_ALL | E_STRICT);//Development
// error_reporting(0);//Production

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	//Récupère les erreurs et le renvoie en exceptions.
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler('exception_error_handler');

require_once 'config'.DIRECTORY_SEPARATOR.'constants.php';

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

require_once CONFIGPATH."global.php";

includeDir(CONFPATH);
includeDir(LIBSPATH);

session_start();

//On récupère l'action s'il y a en vérifiant qu'elle soit valide.
$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;

$Page = '';

// if( !empty($_GET['module']) && is_name($_GET['module']) && file_exists(MODPATH.$_GET['module'].'.php') ) {
// 	if( user_access($_GET['module']) ) {
// 		$Module = $_GET['module'];
// 	} else {
// 		$Module = 'access_denied';
// 	}
// } else {
// 	$Module = DEFAULTMOD;
// }

if( !empty($_GET['module']) && is_name($_GET['module']) && file_exists(MODPATH.$_GET['module'].'.php') ) {
	$Module = $_GET['module'];
} else {
	$Module = DEFAULTMOD;
}

//L'utilisateur ne passe pas par la réécriture d'URL et la page demandée n'est pas la racine.
if( empty($_SERVER['REDIRECT_rewritten']) && $_SERVER['REQUEST_URI'] != '/' && $Module != 'remote' ) {
	header('HTTP/1.1 301 Moved Permanently', false, 301);
	header('Location: '.$Module.'.html');
	exit();
}

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
HTMLRendering::show();
?>