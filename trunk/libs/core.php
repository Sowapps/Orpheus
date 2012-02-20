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
	$lang = ( isset($lang) ) ? $lang : $GLOBALS['AJAXRESP'];
	die( json_encode(
		array(
			'code'			=> $Code,
			'description'	=> ( ( !empty($lang[$Code]) ) ? $lang[$Code] : $Code ),
			'other'			=> $Other
		) )
	);
}

function user_can($action, $selfEditUser=null) {
	global $USER;
	return !empty($USER) && ( $USER instanceof SiteUser ) && ( $USER->checkPerm($action) || ( !empty($selfEditUser) && ( $selfEditUser instanceof SiteUser ) && $selfEditUser->equals($USER) ) );
}

function user_access($module) {
	global $USER;
	return !isset($GLOBALS['ACCESS'][$module]) || (
		( empty($USER) && $GLOBALS['ACCESS'][$module] < 0 ) ||
		( !empty($USER) && $GLOBALS['ACCESS'][$module] >= 0 && $USER instanceof SiteUser && $USER->checkAccess($module) )
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

function log_error($report, $file, $Action='') {
	$Error = array("time" => TIME, "report" => $report, "Action" => $Action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').$file;
	file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
}

function sys_error($report, $Action='') {
	log_error($report, (defined("SYSLOGFILENAME")) ? SYSLOGFILENAME : '.sys_error', $Action);
}
/* codeGen([$size])
 * Génère un code de longueur $size.
 */
function codeGen($size=10) {
	$charList = "0123456789ABCDEFGHIJKLMNOPGRSTUVWXYZ";
	$code = "";
	for($i=0; $i<=$size-1; $i++) {
		$code .= $charList[mt_rand(0, strlen($charList)-1)];
	}
	return $code;
}

/* genSecurityCode([$domain[, $max]])
 * Génère un code de sécurité, l'enregistre en session en bouclant sur le max $max et retourne le code.
 * $max représente donc le nombre de code valable simultanément, cela peut représenter le nombre maximum
 * d'onglet ouvrable simultanément.
 */
function genSecurityCode($domain="global", $max=0) {
	if( !isset($_SESSION['SECURITY']) ) {
		$_SESSION['SECURITY'] = array();
	}
	if( !isset($_SESSION['SECURITY'][$domain]) ) {
		$_SESSION['SECURITY'][$domain] = array('current'=>-1, 'list'=>array());
	}
	$_SESSION['SECURITY'][$domain]['current']++;
	if( $max > 0 ) {
		$_SESSION['SECURITY'][$domain]['current'] %= $max;
	}
	$code = codeGen();
	$_SESSION['SECURITY'][$domain]['list'][$_SESSION['SECURITY'][$domain]['current']] = hash_m02($code);
	return $code;
}

/* checkSecurityCode($code, [$domain])
 * Vérifie qu'un code a bien été généré avec genSecurityCode() et qu'il est toujours valide.
 */
function checkSecurityCode($code, $domain="global") {
	if( !isset($_SESSION['SECURITY'], $_SESSION['SECURITY'][$domain], $_SESSION['SECURITY'][$domain]['list']) ) {
		return false;
	}
//	text($code);
//	text($_SESSION['SECURITY'][$domain]['list']);
	return in_array($code, $_SESSION['SECURITY'][$domain]['list']);
}

/* deleteSecurityCode($code, [$domain])
 * Vérifie qu'un code a bien été généré avec genSecurityCode() et qu'il est toujours valide.
 */
function deleteSecurityCode($code, $domain="global") {
	if( !isset($_SESSION['SECURITY'], $_SESSION['SECURITY'][$domain], $_SESSION['SECURITY'][$domain]['list']) ) {
		return false;
	}
	if( ($k = array_search($code, $_SESSION['SECURITY'][$domain]['list'])) === false ) {
		return false;
	}
	unset($_SESSION['SECURITY'][$domain]['list'][$k]);
	return true;
}

function hash_m02($str) {
	return sha1(hash('ripemd160', $str));
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