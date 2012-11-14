<?php
/*!
 * \brief The core functions
 * 
 * PHP File containing all system functions.
 */

//! Redirects the client to a destination by HTTP
/*!
 * \param $destination The destination to go. Default value is SCRIPT_NAME.
 * \sa permanentRedirectTo()

 * Redirects the client to a $destination using HTTP headers.
 * Stops the running script.
*/
function redirectTo($destination=null) {
	if( !isset($destination) ) {
		$destination = $_SERVER['SCRIPT_NAME'];
	}
	header('Location: '.$destination);
	die();
}

//! Redirects permanently the client to a destination by HTTP
/*!
 * \param $destination The destination to go. Default value is SCRIPT_NAME.
 * \sa redirectTo()

 * Redirects permanently the client to a $destination using the HTTP headers.
 * The only difference with redirectTo() is the status code sent to the client.
*/
function permanentRedirectTo($destination=null) {
	header('HTTP/1.1 301 Moved Permanently', false, 301);
	redirectTo($destination);
}

//! Redirects the client to a destination by HTML
/*!
 * \param $destination The destination to go.
 * \param $time The time in seconds to wait before refresh.
 * \param $die True to stop the script.

 * Redirects the client to a $destination using the HTML meta tag.
 * Does not stop the running script, it only displays.
*/
function htmlRedirectTo($destination, $time=3, $die=0) {
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"{$time} ; URL={$destination}\">";
	if( $die ) {
		exit();
	}
}

//! Displays a variable as HTML
/*!
 * \param $message The data to display. Default value is an empty string.
 * \param $html True to add html tags. Default value is True.
 * \warning Use it only for debugs.

 * Displays a variable as HTML.
 * If the constant TERMINAL is defined, parameter $html is forced to False.
*/
function text($message = '', $html = true) {
	if( defined("TERMINAL") ) {
		$html = false;
	}
	if( is_array($message) ) {
		$message = print_r($message, 1);
		if( $html ) {
			$message = '<pre>'.$message.'</pre>';
		}
	}
	echo $message.(($html) ? '<br />' : '')."\n";
}

//! Do a binary test
/*!
 * \param $value The value to compare.
 * \param $reference The reference for the comparison.
 * \return True if $value is binary included in $reference.

 * Do a binary test, compare $value with $reference.
 * This function is very useful to do binary comparison for rights and inclusion in a value.
*/
function bintest($value, $reference) {
	return ( ($value & $reference) == $reference);
}

//! Sends a packaged response to the client.
/*!
 * \param $code The response code.
 * \param $other Other data to send to the client. Default value is an empty string.
 * \param $domain The translation domain. Default value is 'global'.

 * The response code is a status code, commonly a string.
 * User $Other to send arrays and objects to the client.
 * The packaged reponse is a json string that very useful for AJAX request.
 * This function stops the running script.
*/
function sendResponse($code, $other='', $domain='global') {
	die( json_encode( array(
			'code'			=> $code,
			'description'	=> t($code, $domain),
			'other'			=> $other
	) ) );
}

//! Runs a SSH2 command.
/*!
 * \param $command The command to execute.
 * \param $SSH2S Local settings for the connection.
 * \return The stream from ssh2_exec()

 * Runs a command on a SSH2 connection.
 * You can pass the connection settings array in argument but you can declare a global variable named $SSH2S too.
*/
function ssh2_run($command, $SSH2S=null) {
	if( !isset($SSH2S) ) {
		global $SSH2S;
	}
	$session = ssh2_connect($SSH2S['host']);
	if( $session === false ) {
		throw new Exception('SSH2_unableToConnect');
	}
	if( !ssh2_auth_password( $session , $SSH2S['users'] , $SSH2S['passwd'] ) ) {
		throw new Exception('SSH2_unableToIdentify');
	}
	$stream = ssh2_exec( $session, $command);
	if( $stream === false ) {
		throw new Exception('SSH2_execError');
	}
	return $stream;
}

//! Scans a directory cleanly.
/*!
 * \param $dir The directory to scan.
 * \param $sorting_order True to reverse results order. Default value is False.
 * \return An array of the files in this directory.

 * Scans a directory and returns a clean result.
*/
function cleanscandir($dir, $sorting_order=0) {
	try {
		$result = scandir($dir);
	} catch(Exception $e) {
		return array();
	}
	unset($result[0]);
	unset($result[1]);
	if( $sorting_order ) {
		rsort($result);
	}
	return $result;
}

//! Logs an error in a file.
/*!
 * \param $report The report to log.
 * \param $file The log file path.
 * \param $action The action associated to the report. Default value is an empty string.
 * \param $silent If True, it won't display the report.
 * \warning This function require a writable log file.

 * Logs an error in a file serializing data to JSON.
 * Each line of the file is a JSON string of the reports.
 * The log folder is the constant LOGSPATH or, if undefined, the current one.
 * If the ERROR_LEVEL is setted to DEV_LEVEL, the error will be displayed. 
*/
function log_error($report, $file, $action='', $silent=false) {
	$Error = array('date' => date('c'), 'report' => $report, 'action' => $action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').$file;
	@file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
	if( !$silent && ERROR_LEVEL == DEV_LEVEL ) {
		Rendering::doDisplay('report', $Error);
	}
}

//! Logs a debug.
/*!
 * \param $report The debug report to log.
 * \param $action The action associated to the report. Default value is an empty string.
 * \sa log_error()

 * Logs a system error.
 * The log file is the constant DEBUGFILENAME or, if undefined, '.debug'.
*/
function log_debug($report, $action='') {
	log_error($report, (defined("DEBUGFILENAME")) ? DEBUGFILENAME : '.debug', $action, true);
}

//! Logs a system error.
/*!
 * \param $report The report to log.
 * \param $action The action associated to the report. Default value is an empty string.
 * \sa log_error()

 * Logs a system error.
 * The log file is the constant SYSLOGFILENAME or, if undefined, '.sys_error'.
*/
function sys_error($report, $action='') {
	log_error($report, (defined("SYSLOGFILENAME")) ? SYSLOGFILENAME : '.sys_error', $action);
}

//! Limits the length of a string
/*!
 * \param $string The string to limit length.
 * \param $max The maximum length of the string.
 * \param $strend A string to append to the shortened string.
 * \return The shortened string.

 * Limits the length of a string and append $strend.
 * This function do it cleanly, it tries to cut before a word.
*/
function str_limit($string, $max, $strend='...') {
	$max = (int) $max;
	if( $max <= 0 ) {
		return '';
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

//! Escape a text
/*!
 * \param $str The string to escape.
 * \return The escaped string.

 * Escape the text $str from special characters.
 * This function as the overall framework is made for UTF-8.
*/
function escapeText($str) {
	return htmlentities(str_replace("\'", "'", $str), ENT_NOQUOTES, 'UTF-8', false); 	
}

//! Encodes to an internal URL
/*!
 * \param $u The URL to encode.
 * \return The encoded URL
 * 
 * Encodes to URL and secures some more special characters.
*/
function iURLEncode($u) {
	return str_replace(array(".", '%2F'), array(":46", ''), urlencode($u));
}

//! Decodes from an internal URL
/*!
 * \param $u The URL to decode.
 * \return The decoded URL
 * 
 * Decodes from URL.
*/
function iURLDecode($u) {
	return urldecode(str_replace(":46", ".", $u));
}

//! Parse Fields array to string
/*!
 * \param $fields The fields array.
 * \return A string as fields list.
 * 
 * It parses a field array to a fields list for queries.
*/
function parseFields(array $fields) {
	$list = '';
	foreach($fields as $key => $value) {
		$list .= (!empty($list) ? ', ' : '').$key.'='.$value;
	}
	return $list;
}

//! Gets value from an Array Path
/*!
 * \param $array The array to look in.
 * \param $apath The array path to follow.
 * \return The resulting value or null if not found.
 *
 * Gets value from an Array Path using / as separator.
*/
function apath_get($array, $apath) {
	if( empty($array) || !is_array($array) || empty($apath) ) {
		return null;
	}
	$rpaths = explode('/', $apath, 2);
	if( !isset($array[$rpaths[0]]) ) {
		return null;
	}
	if( !isset($rpaths[1]) ) {
		return $array[$rpaths[0]];
	}
	return apath_get($array[$rpaths[0]], $rpaths[1]);
}

//! Imports the required class(es).
/*!
 * \param $pkgPath The package path.
 * \warning You should only use lowercase for package names.
 * 
 * Includes the package from the libs directory.
 * e.g: "package.myclass", "package.other.*"
*/
function using($pkgPath) {
	$pkgPath = LIBSPATH.str_replace('.', '/',strtolower($pkgPath));
	if( substr($pkgPath, -2) == '.*' ) {
		$dir = substr($pkgPath, 0, -2);
		$files = scandir($dir);
		foreach($files as $file) {
			if( preg_match("#^[^\.].*_class.php$#", $file) ) {
				require_once $dir.'/'.$file;
			}
		}
	}
	require_once $pkgPath.'_class.php';
}

//! Adds a class to the autoload.
/*!
 * \param $className The class name.
 * \param $classPath The class path. (cf. description)
 * 
 * Adds the class to the autoload list, associated with its file.
 * The semi relative path syntax has priority over the full relative path syntax.
 * e.g: ("MyClass", "mylib/myClass") => libs/mylib/myClass_class.php
 * or ("MyClass2", "mylib/myClass2.php") => libs/mylib/myClass.php
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

//! Gets the url of a module
/*!
 * \param $module The module.
 * \param $action The action to use for this url.
 * \param $queryStr The query string to add to the url, can be an array.
 * \return The url of $module.

 * Gets url of a module, using default link for module defaut module.
*/
function u($module, $action='', $queryStr='') {
	if( $module == DEFAULTMOD && empty($action) ) {
		return DEFAULTLINK;
	}
	if( !empty($queryStr) ) {
		if( is_array($queryStr) ) {
			unset($queryStr['module'], $queryStr['action']);
			$queryStr = http_build_query($queryStr, '', '&amp;');
		} else {
			$queryStr = str_replace('&', '&amp;');
		}
	}
	return $module.((!empty($action)) ? '-'.$action : '').((!empty($queryStr)) ? '-'.$queryStr : '').'.html';
}

//! Adds a report
/*!
 * \param $message The message to report.
 * \param $type The type of the message.
 * \param $domain The domain fo the message. Not used for translation. Default value is global.
 * \sa reportSuccess(), reportError()

 * Adds the report $message to the list of reports for this $type.
 * The type of the message is commonly 'success' or 'error'.
*/
function addReport($message, $type, $domain='global') {
	global $REPORTS;
	if( !isset($REPORTS[$domain]) ) {
		$REPORTS[$domain] = array('error'=>array(), 'success'=>array());
	}
	$REPORTS[$domain][$type][] = $message;
}

//! Reports a success
/*!
 * \param $message The message to report.
 * \param $domain The domain fo the message. Not used for translation. Default value is global.
 * \sa addReport()

 * Adds the report $message to the list of reports for this type 'success'.
*/
function reportSuccess($message, $domain='global') {
	return addReport($message, 'success', $domain);
}

//! Reports an error
/*!
 * \param $message The message to report.
 * \param $domain The domain fo the message. Not used for translation. Default value is global.
 * \sa addReport()

 * Adds the report $message to the list of reports for this type 'error'.
*/
function reportError($message, $domain='global') {
	$message = ($message instanceof Exception) ? $message->getMessage() : "$message";
	return addReport($message, 'error', $domain);
}

//! Gets reports as HTML
/*!
 * \param $domain The translation domain and the domain of the report. Default value is 'global'.
 * \param $rejected An array of rejected messages.
 * \param $delete True to delete entries from the list.
 * \sa displayReportsHTML()

 * Gets all reports from the list of $domain and generates the HTML source to display.
*/
function getReportsHTML($domain='all', $rejected=array(), $delete=1) {
	global $REPORTS;
	if( empty($REPORTS) ) {
		return '';
	}
	$report = '';
	if( $domain == 'all' ) {
		foreach( array_keys($REPORTS) as $domain ) {
			$report .= getReportsHTML($domain, $rejected, $delete);
		}
		return $report;
	}
	if( empty($REPORTS[$domain]) ) {
		return '';
	}
	foreach( $REPORTS[$domain] as $type => &$reports ) {
		foreach( $reports as $message) {
			if( !in_array($message, $rejected) ) {
				$report .= '
		<div class="report report_'.$domain.' '.$type.'">'.t($message, $domain).'</div>';
			}
		}
		if( $delete ) {
			$reports = array();
		}
	}
	return $report;
}

//! Displays reports as HTML
/*!
 * \param $domain The translation domain and the domain of the report. Default value is 'all'.
 * \param $rejected An array of rejected messages. Can be the first parameter.
 * \param $delete True to delete entries from the list.
 * \sa getReportsHTML()

 * Displays all reports from the list of $domain and displays generated HTML source.
*/
function displayReportsHTML($domain='all', $rejected=array(), $delete=1) {
	if( is_array($domain) && empty($rejected) ) {
		$rejected = $domain;
		$domain = 'all';
	}
	echo '
	<div class="reports '.$domain.'">
	'.getReportsHTML($domain, $rejected, $delete).'
	</div>';
}

//! Gets POST data
/*!
 * \param $key The key to retrieve. The default value is null (retrieves all data).
 * \return Data using the key or all data from POST array.
 * \sa isPOST()

 * Gets data from a POST request using the $key.
 * With no parameter or parameter null, all data are returned.
*/
function POST($key=null) {
	return ( isset($key) ) ? ( (isset($_POST[$key])) ? $_POST[$key] : false) : $_POST ;
}

//! Checks the POST status
/*!
 * \param $key The name of the button submitting the request.
 * \return True if the request is a POST one. Compares also the $key if not null.
 * 
 * Check the POST status to retrieve data from a form.
 * You can specify the name of your submit button as first parameter.
 * We advise to use the name of your submit button, but you can also use another important field of your form.
*/
function isPOST($key=null) {
	return isset($_POST) && (is_null($key) || isset($_POST[$key]));
}

//! Gets the HTML value
/*!
 * \param $name The name of the field
 * \param $data The array of data where to look for. Default value is $formData (if exist) or $_POST
 * \return A HTML source with the "value" attribute.
 * 
 * Gets the HTML value attribut from an array of data if this $name exists.
*/
function htmlValue($name, $data=null) {
	if( is_null($data) ) {
		global $formData;
		$data = (isset($formData)) ? $formData : POST();
	}
	return (!empty($data[$name])) ? " value=\"{$data[$name]}\"" : '';
}

//! Generates the HTML source for a SELECT
/*!
 * \param $name The name of the field.
 * \param $data The data to to build the dropdown.
 * \param $prefix The prefix to use for the text name of values. Default value is an empty string.
 * \param $domain The domain to apply the Key. Default value is 'global'.
 * \param $selected The selected value from the data. Default value is null (no selection).
 * \param $selectAttr Additional attributes for the SELECT tag.
 * \return A HTML source for the built SELECT tag.
 * \warning This function is not complete, it requires more functionalities.
 * 
 * Generates the HTML source for a SELECT from the $data.
*/
function htmlSelect($name, $data, $prefix='', $domain='global', $selected=null, $selectAttr='') {
	global $formData;
	$opts = '';
	$namePath = explode(':', $name);
	$name = $namePath[count($namePath)-1];
	$htmlName = '';
	foreach( $namePath as $index => $path ) {
		$htmlName .= ( $index ) ? "[{$path}]" : $path;
	}
	$selectAttr .= ' name="'.$htmlName.'"';
	if( is_null($selected) && isset($formData[$name]) ) {
		$selected = $formData[$name];
	}
	foreach( $data as $dataKey => $dataValue ) {
		$key = (is_int($dataKey)) ? $dataValue : $dataKey;// If this is an associative array, we use the key, else the value.
		$opts .= '
	<option value="'.$dataValue.'" '.( ($dataValue == $selected) ? 'selected="selected"' : '').'>'.t($prefix.$key, $domain).'</option>';
	}
	return "
<select {$selectAttr}>{$opts}
</select>";
}