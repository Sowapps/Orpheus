<?php
/*!
 * \brief The validators
 * 
 * PHP File containing all basic validators for a website.
 */

//! Checks if the input is an email address.
/*!
 * \param $email The email address to check.
 * \return True if $email si a valid email address.
 */
function is_email($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

//! Checks if the input is a name.
/*!
 * \param $name The name to check.
 * \param $charnb_max The maximum length of the given name. Default value is 50.
 * \param $charnb_min The minimum length of the given name. Default value is 3.
 * \return True if $name si a name.
 * 
 * The name is a slug with no special characters.
 */
function is_name($name, $charnb_max=50, $charnb_min=3) {
	return preg_match('#^[a-z0-9\-\_]{'.$charnb_min.','.$charnb_max.'}$#i', $name);
}

//! Checks if the input is an ID Number.
/*!
 * \param $Number The number to check.
 * \return True if $Number si a valid integer.
 * 
 * The ID number is an integer.
 */
function is_ID($Number) {
	return ctype_digit("$Number");
}

//! Checks if the input is a date.
/*!
 * \param $date The date to check.
 * \param $separators The separator to use for date format. Regex special characters should be escaped.  Optional, default value is \-\/:\;,|\#
 * \return True if $date si a valid date.
 * 
 * The date have to be well formatted and valid.
 * The format is DD/MM/YYYY and default separators can be '/', '-', ':', ';', ',', '|' or '#' 
 */
function is_date($date, $separators='\-\/:\;,|\#') {
	$DateFor = preg_replace('#^([0-9]{1,2})[{$separators}]([0-9]{1,2})[{$separators}]([0-9]{4})$#', '$1#$2#$3', $date, -1, $Count);
	if( !$Count ) {
		return false;
	}
	list($Day, $Month, $Year) = explode("#", $DateFor);
	return checkdate($Month, $Day, $Year);
}

//! Checks if the input is an url.
/*!
 * \param $Url The url to check.
 * \param $protocol Not used yet. Default to SCHEME constant.
 * \return True if $Url si a valid url.
 */
function is_url($Url, $protocol=null) {
	return filter_var($Url, FILTER_VALIDATE_URL);
}

//! Checks if the input is an ip address.
/*!
 * \param $ip The url to check.
 * \return True if $ip si a valid ip address.
 */
function is_ip($ip) {
	return filter_var($ip, FILTER_VALIDATE_IP);
}

//! Checks if the input is a phone number.
/*!
 * \param $number The phone number to check.
 * \param $length The limit of length of the number. Default value is unlimited.
 * \return True if $number si a valid phone number.
 * 
 * It can only validate french phone number.
 * The separator can be '.', ' ' or '-', it can be ommitted.
 * e.g: +336.12.34.56.78, 01-12-34-56-78
 */
function is_phone_number($number, $length=null) {
	$number = str_replace(array('.', ' ', '-'), '', $number);
	$length = ( isset($length) && $length > 1 ) ? '{'.($length-1).'}' : '+';
	return preg_match("#^(?:\+[0-9]{1,3}|0)[0-9]{$length}$#", $number);
}