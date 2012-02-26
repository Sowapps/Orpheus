<?php
/* libs/validators.php
 * PHP File for included functions: Checkers
 * [EN] Library of checkers functions.
 * [FR] Bibliothèque de fonctions de vérification/validation.
 *
 * Auteur: Florent Hazard.
 * Version: 11
 * Derniere edition: 19/08/2011
 */
if( !defined("INSIDE") ) {
	return;
}

function is_email($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
/*
	$atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // Avant l'arobase (préfixe)
	$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // Après l'arobase (nom de domaine)
	$regex = '/^' . $atom . '+(\.' . $atom . '+)*@(' . $domain . '{1,63}\.)+' . $domain . '{2,63}$/i';
	return preg_match($regex, $email);
*/
}

function is_name($name, $charnb_max=50, $charnb_min=3) {
	return preg_match('#^[a-z0-9\-\_]{'.$charnb_min.','.$charnb_max.'}$#i', $name);
}

function is_ID($Number) {
	//return ( ctype_digit($Number) || preg_match("#^[0-9]+$#i", $Number) );
	return ( ctype_digit("$Number") );
}

function is_date($Date) {
	$DateFor = preg_replace('#^([0-9]{1,2})[\-\/:\;,|\#]([0-9]{1,2})[\-\/:\;,|\#]([0-9]{4})$#', '$1#$2#$3', $Date, -1, $Count);
	if( !$Count ) {
		return false;
	}
	list($Day, $Month, $Year) = explode("#", $DateFor);
	return checkdate($Month, $Day, $Year);
}

function is_url($Url, $protocol='http') {
	return filter_var($email, FILTER_VALIDATE_URL);
/*
	return preg_match("#^{$protocol}://[0-9a-z\.\-]+/[0-9a-z\\\/\?\=\%\_\.\,\;\!\+\(\)\#\&]*$#i", $Url);
*/
/*
	if( $protocol == 'http' ) {
		return preg_match("#^http://[0-9a-z\.\-]+/[0-9a-z\\\/\?\=\%\_\.\,\;\!\+\(\)\#\&]*$#i", $Url);
	}
*/
/*
	 else {
		trigger_error(__FUNCTION__.'() Unknown protocol "'.$protocol.'"', E_USER_WARNING);
		return false;
	}
*/
}

function is_date_format($format) {
	return ( date($format) != $format );
}

function is_host($host) {
	return preg_match("#^[0-9a-z\.\-]+$#i", $host);
}

function is_ip($ip) {
	return filter_var($ip, FILTER_VALIDATE_IP);
}

function is_phone_number($number, $length=null) {
	$number = str_replace(array('.', ' ', '-'), '', $number);
	$length = ( isset($length) && $length > 1 ) ? '{'.($length-1).'}' : '+';
	return preg_match("#^(?:\+[0-9]{1,3}|0)[0-9]{$length}$#", $number);
}