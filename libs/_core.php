<?php
/* ./core.php
 * PHP File for included functions: Core
 * [EN] Library of system functions.
 *
 * Author: Florent Hazard.
 * Revision: 8
 * Creation date: 19/08/2011
 */
if( !defined("INSIDE") ) {
	return;
}

function redirectTo($Destination='') {
	if( empty($Destination) ) {
		$Destination = $_SERVER['SCRIPT_NAME'];
	}
	header('Location: '.$Destination);
	die();
}

function permanentRedirectTo($Destination='') {
	header('HTTP/1.1 301 Moved Permanently', false, 301);
	redirectTo($Destination);
}

function htmlRedirectTo($dest, $time=3, $die=0) {
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"{$time} ; URL={$dest}\">";
	if( !empty($die) ) {
		exit();
	}
}

function text($message = '', $br = 1) {
	$message = ( is_array($message) ) ? '<pre>'.print_r($message, 1).'</pre>' : $message;
	echo $message.(($br && !defined("TERMINAL")) ? "<br />" : "" )."\n";
}

function bintest($Variable, $Comparateur) {
	return ( ($Variable & $Comparateur) == $Comparateur);
}

function lang($k) {
	return $k;
}

function sendResponse($Code, $Other='', $lang=null) {
	$lang = ( isset($lang) ) ? $lang : ( ( !empty($GLOBALS['AJAXRESP']) ) ? $GLOBALS['AJAXRESP'] : array() );
	die( json_encode(
		array(
			'code'			=> $Code,
			'description'	=> ( ( !empty($lang[$Code]) ) ? $lang[$Code] : $Code ),
			'other'			=> $Other
		) )
	);
}

function ssh2_run($command, $SSH2S=null) {
	if( !isset($SSH2S) ) {
		global $SSH2S;
	}
	$session = ssh2_connect($SSH2S['host']);
	if( $session === false ) {
		throw new Exception('SSH2_unableToConnect');
	}
	if( !ssh2_auth_password( $session , $SSH2S['user'] , $SSH2S['passwd'] ) ) {
		throw new Exception('SSH2_unableToIdentify');
	}
	$stream = ssh2_exec( $session, $command);
	if( $stream === false ) {
		throw new Exception('SSH2_execError');
	}
	return $stream;
}

/*
Nom: cleanscandir
Données: (String) Chemin du répertoire à scanner[, (Boolean) si le résultat doit être inversé].
Résultat: Le tableau listant les fichiers du répertoire donné selon l'ordre donné.
*/
function cleanscandir($dir, $sorting_order = 0) {
	$result = scandir($dir);
	unset($result[0]);
	unset($result[1]);
	if( $sorting_order ) {
		rsort($result);
	}
	return $result;
}

/*
function error($e, $domain=null) {
	global $ERRORS;
	$msg = ($e instanceof Exception) ? $e->getMessage() : "$e";
	if( !empty($domain) && !empty($ERRORS[$domain]) && !empty($ERRORS[$domain][$msg]) ) {
		return $ERRORS[$domain][$msg];
	}
	if( !empty($ERRORS['global'][$msg]) ) {
		return $ERRORS['global'][$msg];
	}
	return $msg;
}
*/

function log_error($report, $file, $Action='') {
	$Error = array('date' => date('c'), 'report' => $report, 'Action' => $Action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').$file;
	file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
}

function sys_error($report, $Action='') {
	log_error($report, (defined("SYSLOGFILENAME")) ? SYSLOGFILENAME : '.sys_error', $Action);
}

function addUserError($e) {
	global $USERERRORS;
	if( !isset($USERERRORS) ) {
		initUserErrors();
	}
	$USERERRORS[] = $e;
}

function getUserErrors() {
	global $USERERRORS;
	if( !isset($USERERRORS) ) {
		initUserErrors();
	}
	return $USERERRORS;
}

function initUserErrors() {
	global $USERERRORS;
	$USERERRORS = array();
}

/* str_limit($string, $max[, $strend])
 * Author: Florent HAZARD
 * 
 * Cette fonction raccourci la chaine de caractère $string en ajoutant
 * $strend si elle dépasse $max caractères.
 * Elle cherche à couper avant le dernier mot présent mais si celui-ci
 * est trop long, elle le coupe net.
 * $strend vaut par défaut "...".
 */
function str_limit($string, $max, $strend = "...") {
	$max = (int) $max;
	if( $max <= 0 ) {
		return "";
	}
	if( strlen($string) <= $max ) {
		return $string;
	}
	$subStr = substr($string, 0, $max);
	if( !in_array($string[$max], array("\n", "\r", "\t", " ")) ) {
		$lSpaceInd = strrpos($subStr, ' ');
		if( $max-$lSpaceInd < 10 ) {
			$subStr = substr($string, 0, $lSpaceInd);
		}
	}
	return $subStr.$strend;
}

function escapeText($s) {
	return htmlentities(str_replace("\'", "'", $s), ENT_NOQUOTES, 'UTF-8', false); 	
}

function iURLEncode($u) {
	return str_replace(array(".", '%2F'), array(":46", ''), urlencode($u));
}

function iURLDecode($u) {
	return urldecode(str_replace(":46", ".", $u));
}

//! Parse Fields array to string
/*!
	\param $fields The fields array.
	\return A string as fields list.
	
	It parses a field array to a fields list for queries.
*/
function parseFields(array $fields) {
	$list = '';
	foreach($fields as $key => $value) {
		$list .= (!empty($list) ? ', ' : '').$key.'='.$value;
	}
	return $list;
}

//! Imports the required class(es).
/*!
	\param $pkgPath A package path.
	\warning You should only use lowercase for package names.

	Includes the package page from the libs directory.
	e.g: "package.myclass", "package.other.*"
*/
function using($pkgPath) {
	$pkgPath = LIBSPATH.str_replace('.', '/',strtolower($pkgPath));
	if( substr($pkgPath, -2) == '.*' ) {
		$dir = substr($pkgPath, 0, -2);
		$files = scandir($dir);
		foreach($files as $file) {
			//$file[0] != '.' 
			if( preg_match("#^[^\.].*_class.php$#", $file) ) {
				require_once $dir.'/'.$file;
			}
		}
	}
	require_once $pkgPath.'_class.php';
}

//! Adds a class to the autoload.
/*!
	\param $className The class name.
	\param $classPath The class path. (cf. description)

	Adds the class to the autoload list, associated with its file.
	The semi relative path syntax has priority over the full relative path syntax.
	e.g: ("MyClass", "mylib/myClass") => libs/mylib/myClass_class.php
	or ("MyClass2", "mylib/myClass2.php") => libs/mylib/myClass.php
*/
function addAutoload($className, $classPath) {
	global $AUTOLOADS;
	$className = strtolower($className);
	if( !empty($AUTOLOADS[$className]) ) {
		return false;
	}
	if( is_readable(LIBSPATH.$classPath.'_class.php') ) {
		$AUTOLOADS[$className] = $classPath.'_class.php';
		
	} else if( is_readable(LIBSPATH.$classPath) ) {
		$AUTOLOADS[$className] = $classPath;
		
	} else {
		throw new Exception("Class file of \"{$className}\" not found.");
	}
	return true;
}

function addReport($message, $type, $domain='global') {
	global $REPORTS;
	if( !isset($REPORTS[$domain]) ) {
		$REPORTS[$domain] = array('error'=>array(), 'success'=>array());
	}
	// Require use of a translator system.
	$REPORTS[$domain][$type][] = $message;
}

function reportSuccess($message, $domain='global') {
	return addReport($message, 'success', $domain);
}

function reportError($message, $domain='global') {
	$message = ($message instanceof Exception) ? $message->getMessage() : "$message";
	return addReport($message, 'error', $domain);
}

function getReportsHTML($domain='global', $delete=1) {
	global $REPORTS;
	if( empty($REPORTS[$domain]) ) {
		return '';
	}
	$report = '';
	foreach( $REPORTS[$domain] as $type => &$reports ) {
		foreach( $reports as $message) {
			$report .= '
		<div class="report '.$type.'">'.$message.'</div>';
		}
		if( $delete ) {
			$reports = array();
		}
	}
	return $report;
}

function displayReportsHTML($domain='global', $delete=1) {
	echo '
	<div class="reports '.$domain.'">
	'.getReportsHTML($domain, $delete).'
	</div>';
}