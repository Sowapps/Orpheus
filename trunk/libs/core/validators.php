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
 * \sa is_personalname()
 * 
 * The name is a slug with no special characters.
 */
function is_name($name, $charnb_max=50, $charnb_min=3) {
	return preg_match('#^[a-z0-9\-\_]{'.$charnb_min.','.$charnb_max.'}$#i', $name);
}

//! Checks if the input is a personal name.
/*!
 * \param $name The name to check.
 * \param $charnb_max The maximum length of the given name. Default value is 50.
 * \param $charnb_min The minimum length of the given name. Default value is 3.
 * \return True if $name si a name.
 * \sa is_name()
 * 
 * The name can not contain programming characters like control characters, '<', '>' or '='...
 */
function is_personalname($name, $charnb_max=50, $charnb_min=3) {
	// \'
	return preg_match('#^[^\^\<\>\*\+\(\)\[\]\{\}\"\~\&\=\:\;\`\|\#\@\%\/\\\\[:cntrl:]]{'.$charnb_min.','.$charnb_max.'}$#i', $name);
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
 * \param $withTime True to use datetime format, optional. Default value is false.
 * \param $time The output timestamp of the data, optional.
 * \param $country The country to use the date format, optional. Default and unique value is FR, not used.
 * \return True if $date si a valid date.
 * 
 * The date have to be well formatted and valid.
 * The FR date format is DD/MM/YYYY and time format is HH:MM:SS  
 */
function is_date($date, $withTime=false, &$time=false, $country='FR') {
	$DateFor = preg_replace("#^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})".($withTime ? "(?: ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?" : '')."$#", '$1#$2#$3#$4#$5#$6', $date, -1, $count);
	if( !$count ) { return false; }
	list($day, $month, $year, $hour, $min, $sec) = explodeList("#", $DateFor, 6, 0);
	$r = checkdate($month, $day, $year);
	if( $r && $time!==false ) {
		$time = mktime((int) $hour, (int) $min, (int) $sec, $month, $day, $year);
	} 
	return $r;
}

//! Checks if the input is an url.
/*!
 * \param $Url The url to check.
 * \param $protocol Not used yet. Default to SCHEME constant, not used.
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