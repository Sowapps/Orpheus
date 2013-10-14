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
	if( !is_scalar($message) ) {
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
 * \param $message The message to display. Default is an empty string. See description for details.
 * \warning This function require a writable log file.

 * Logs an error in a file serializing data to JSON.
 * Each line of the file is a JSON string of the reports.
 * The log folder is the constant LOGSPATH or, if undefined, the current one.
 * If message is NULL, it won't display any report; if ERROR_LEVEL is DEV_LEVEL, displays report; if empty, throw exception else it displays the message.
*/
function log_error($report, $file, $action='', $message='') {
	if( !is_scalar($report) ) {
		$report = 'NON-SCALAR::'.print_r($report, 1);
	}
	$Error = array('date' => date('c'), 'report' => $report, 'action' => $action);
	$logFilePath = ( ( defined("LOGSPATH") && is_dir(LOGSPATH) ) ? LOGSPATH : '').$file;
	@file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
	if( !is_null($message) ) {
		if( ERROR_LEVEL == DEV_LEVEL ) {
			//$Error['message'] = (empty($message)) ? $report : $message;
			$Error['message'] = $message;
			$Error['page'] = nl2br(htmlentities($GLOBALS['Page']));
			// Display a pretty formatted error report
			if( !class_exists('Rendering') || !Rendering::doDisplay('report', $Error) ) {
				// If we fail in our display of this error, this is fatal.
				echo print_r($Error, 1);
			}
		} else if( empty($message) ) {
			throw new Exception('fatalErrorOccurred');
			
		} else {
			die($message);
		}
	}
}

//! Logs a debug.
/*!
 * \param $report The debug report to log.
 * \param $action The action associated to the report. Default value is an empty string.
 * \sa log_error()

 * Logs a debug.
 * The log file is the constant DEBUGFILENAME or, if undefined, '.debug'.
*/
function log_debug($report, $action='') {
	log_error($report, (defined("DEBUGFILENAME")) ? DEBUGFILENAME : '.debug', $action, null);
}

//! Logs a hack attemp.
/*!
 * \param $report The report to log.
 * \param $message If False, it won't display the report, else if a not empty string, it displays it, else it takes the report's value.
 * \sa log_error()

 * Logs a hack attemp.
 * The log file is the constant HACKFILENAME or, if undefined, '.hack'.
*/
function log_hack($report, $message='') {
	log_error($report, (defined("HACKLOGFILENAME")) ? HACKLOGFILENAME : '.hack', '', $message);
}

//! Logs a system error.
/*!
 * \param $report The report to log.
 * \param $action The action associated to the report. Default value is an empty string.
 * \param $silent True to not display any report. Default value is false.
 * \sa log_error()

 * Logs a system error.
 * The log file is the constant SYSLOGFILENAME or, if undefined, '.sys_error'.
*/
function sys_error($report, $action='', $silent=false) {
	log_error($report, (defined("SYSLOGFILENAME")) ? SYSLOGFILENAME : '.sys_error', $action, $silent ? null : '');
}

//! Logs a sql error.
/*!
 * \param $report The report to log.
 * \param $action The action associated to the report. Default value is an empty string.
 * \sa log_error()

 * Logs a sql error.
 * The log file is the constant PDOLOGFILENAME or, if undefined, '.pdo_error'.
*/
function sql_error($report, $action='') {
	log_error($report, (defined("PDOLOGFILENAME")) ? PDOLOGFILENAME : '.pdo_error', $action);//, t('errorOccurredWithDB'));
	throw new Exception('errorOccurredWithDB');
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
 * \param $array The array to get the value from.
 * \param $apath The path used to browse the array.
 * \param $default The default value returned if array is valid but key is not found.
 * \param $pathRequired True if the path is required. Default value is False.
 * \return The value from $apath in $array.
 * \sa build_apath()
 *
 * Gets value from an Array Path using / as separator.
 * Returns null if parameters are invalids, $default if the path is not found else the value.
 * If $default is not null and returned value is null, you can infer your parameters are invalids.
*/
function apath_get($array, $apath, $default=null, $pathRequired=false) {
	if( empty($array) || !is_array($array) || is_null($apath) ) {
		return null;
	}
	$rpaths = explode('/', $apath, 2);
	// If element does not exist in array
	if( !isset($array[$rpaths[0]]) ) {
		// If has a child, the child could not be found
		// Else container exists, but element not found.
		return ($pathRequired && isset($rpaths[1])) ? null : $default;
	}
	return isset($rpaths[1]) ? apath_get($array[$rpaths[0]], $rpaths[1]) : $array[$rpaths[0]];
}

//! Build all path to browse array
/*!
 * \param $array The array to get the value from.
 * \param $prefix The prefix to get the value, this is for an internal use only.
 * \return An array of apath to get all values.
 * \sa apath_get()
 *
 * Builds an array associating all values with their apath of the given one using / as separator.
 * e.g Array('path'=>array('to'=>array('value'=>'value'))) => Array('path/to/value'=>'value')
*/
function build_apath($array, $prefix='') {
	if( empty($array) || !is_array($array) ) {
		return array();
	}
	$r = array();
	foreach($array as $key => $value) {
		if( is_array($value) ) {
			$r += build_apath($value, $prefix.$key.'/');
		} else {
			$r[$prefix.$key] = $value; 
		}
	}
	return $r;
}

//! Imports the required class(es).
/*!
 * \param $pkgPath The package path.
 * \warning You should only use lowercase for package names.
 * 
 * Includes a class from a package in the libs directory, or calls the package loader.
 * e.g: "package.myclass", "package.other.*", "package"
 * 
 * Packages should include a _loader.php or loader.php file (it is detected in that order).
 * Class files should be named classname_class.php
*/
function using($pkgPath) {
	$pkgPath = LIBSDIR.str_replace('.', '/',strtolower($pkgPath));
	// Including all contents of a package
	if( substr($pkgPath, -2) == '.*' ) {
		$dir = pathOf(substr($pkgPath, 0, -2));
		$files = scandir($dir);
		foreach($files as $file) {
			if( preg_match("#^[^\.].*_class.php$#", $file) ) {
				require_once $dir.'/'.$file;
			}
		}
		return;
	}
	// Including loader of a package
	if( existsPathOf($pkgPath, $path) && is_dir($path) ) {
		if( file_exists($path.'/_loader.php') ) {
			require_once $path.'/_loader.php';
		} else {
			require_once $path.'/loader.php';
		}
		return;
	}
	// Including a class
	require_once pathOf($pkgPath.'_class.php');
}

//! Adds a class to the autoload.
/*!
 * \param $className The class name.
 * \param $classPath The class path.
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
	if( existsPathOf(LIBSDIR.$classPath.'_class.php') ) {
		$AUTOLOADS[$className] = $classPath.'_class.php';
		
	} else if( existsPathOf(LIBSDIR.$classPath) ) {
		$AUTOLOADS[$className] = $classPath;
		
	} else {
		throw new Exception("Class file of \"{$className}\" not found.");
	}
	return true;
}

//! Gets the full url of a module
/*!
 * \param $module The module.
 * \param $action The action to use for this url.
 * \param $queryStr The query string to add to the url, can be an array.
 * \return The url of $module.

 * Gets the full url of a module, using default link for default module.
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
			$queryStr = str_replace('&', '&amp;', $queryStr);
		}
	}
	return SITEROOT.$module.((!empty($action)) ? '-'.$action : '').((!empty($queryStr)) ? '-'.$queryStr : '').'.html';
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
 * \param $domain The domain fo the message. Default value is the domain of Exception in cas of UserException else 'global'.
 * \sa addReport()

 * Adds the report $message to the list of reports for this type 'error'.
*/
function reportError($message, $domain=null) {
	if( $message instanceof UserException && is_null($domain) ) {
		$domain = $message->getDomain();
	}
// 	$message = ($message instanceof Exception) ? $message->getMessage() : "$message";
	return addReport("$message", 'error', is_null($domain) ? 'global' : $domain);
}

//! Gets one report as HTML
/*!
 * \param $message The message to report.
 * \param $type The type of the message.
 * \param $domain The domain fo the message. Not used for translation. Default value is global.

 * Returns a valid HTML report.
 * This function is only a HTML generator.
*/
function getHTMLReport($message, $type, $domain='global') {
	return '
		<div class="report report_'.$domain.' '.$type.'">'.nl2br(t($message, $domain)).'</div>';
}

//! Gets some/all reports as HTML
/*!
 * \param $domain The translation domain and the domain of the report. Default value is 'global'.
 * \param $rejected An array of rejected messages.
 * \param $delete True to delete entries from the list.
 * \sa displayReportsHTML()
 * \sa getHTMLReport()

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
				$report .= getHTMLReport($message, $type, $domain);
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
 * \param $path The path to retrieve. The default value is null (retrieves all data).
 * \return Data using the path or all data from POST array.
 * \sa isPOST()
 * \sa extractFrom()

 * Gets data from a POST request using the $path.
 * With no parameter or parameter null, all data are returned.
*/
function POST($path=null) {
	return extractFrom($path, $_POST);
}

//! Gets GET data
/*!
 * \param $path The path to retrieve. The default value is null (retrieves all data).
 * \return Data using the path or all data from GET array.
 * \sa isGET()
 * \sa extractFrom()

 * Gets data from a GET request using the $path.
 * With no parameter or parameter null, all data are returned.
*/
function GET($path=null) {
	return extractFrom($path, $_GET);
}

//! Checks the POST status
/*!
 * \param $apath The apath to test.
 * \return True if the request is a POST one. Compares also the $key if not null.
 * \sa POST()
 * 
 * Check the POST status to retrieve data from a form.
 * You can specify the name of your submit button as first parameter.
 * We advise to use the name of your submit button, but you can also use another important field of your form.
*/
function isPOST($apath=null) {
	return isset($_POST) && (is_null($apath) || !is_null(POST($apath)));
}

//! Checks the GET status
/*!
 * \param $apath The apath to test.
 * \return True if the request is a GET one. Compares also the $key if not null.
 * \sa GET()
 * 
 * Check the GET status to retrieve data from a form.
 * You can specify the name of your submit button as first parameter.
 * We advise to use the name of your submit button, but you can also use another important field of your form.
*/
function isGET($apath=null) {
	return isset($_GET) && (is_null($apath) || !is_null(GET($apath)));
}

//! Extracts data from array using apath
/*!
 * \param $apath The apath to retrieve. null retrieves all data.
 * \param $array The array of data to browse.
 * \return Data using the apath or all data from the given array.

 * Gets data from an array using the $apath.
 * If $apath is null, all data are returned.
*/
function extractFrom($apath, $array) {
	return is_null($apath) ? $array : apath_get($array, $apath);
// 	return is_null($path) ? $array : ( (!is_null($v = apath_get($array, $path))) ? $v : false) ;
}

//! Gets the HTML value
/*!
* \param $name The name of the field
* \param $data The array of data where to look for. Default value is $formData (if exist) or $_POST
* \param $default The default value if $name is not defined in $data
* \return A HTML source with the "value" attribute.
*
* Gets the HTML value attribut from an array of data if this $name exists.
*/
function htmlValue($name, $data=null, $default='') {
	fillFormData($data);
	$v = apath_get($data, $name, $default);
	return !empty($v) ? " value=\"{$v}\"" : '';
}

//! Generates the HTML source for a SELECT
/*!
* \param $name The name of the field.
* \param $values The values to build the dropdown menu.
* \param $data The array of data where to look for. Default value is $formData (if exist) or $_POST
* \param $selected The selected value from the data. Default value is null (no selection).
* \param $prefix The prefix to use for the text name of values. Default value is an empty string.
* \param $domain The domain to apply the Key. Default value is 'global'.
* \param $tagAttr Additional attributes for the SELECT tag.
* \return A HTML source for the built SELECT tag.
* \sa htmlOptions
* \warning This function is under conflict with name attribute and last form data values, prefer htmlOptions()
*
* Generates the HTML source for a SELECT from the $data.
*/
function htmlSelect($name, $values, $data=null, $selected=null, $prefix='', $domain='global', $tagAttr='') {
	fillFormData($data);
	$namePath = explode('/', $name);
	$name = $namePath[count($namePath)-1];
	$htmlName = '';
	foreach( $namePath as $index => $path ) {
		$htmlName .= ( $index ) ? "[{$path}]" : $path;
	}
	$tagAttr .= ' name="'.$htmlName.'"';
	$v = apath_get($data, $name);
	if( !empty($v) ) {//is_null($selected) && 
		$selected = $v;
	}
	$opts = '';
	foreach( $values as $dataKey => $dataValue ) {
		$addAttr = '';
		if( is_array($dataValue) ) {
			list($dataValue, $addAttr) = array_pad($dataValue, 2, null);
		}
		$key = is_int($dataKey) ? $dataValue : $dataKey;// If this is an associative array, we use the key, else the value.
		$opts .= '
	<option value="'.$dataValue.'" '.( ($dataValue == $selected) ? 'selected="selected"' : '').' '.$addAttr.'>'.t($prefix.$key, $domain).'</option>';
	}
	return "
	<select {$tagAttr}>{$opts}
	</select>";
}

//! Generates the HTML source for options of a SELECT
/*!
* \param $fieldPath The name path to the field.
* \param $values The values to build the dropdown menu.
* \param $default The default selected value. Default value is null (no selection).
* \param $matches Define the associativity between array and option values. Default value is OPT_VALUE2LABEL (as null).
* \param $prefix The prefix to use for the text name of values. Default value is an empty string.
* \param $domain The domain to apply the Key. Default value is 'global'.
* \return A HTML source for the built SELECT tag.
* \sa htmlOption()
*
* Generates the HTML source for a SELECT from the $data.
* For associative arrays, we commonly use the value=>label model (OPT_VALUE2LABEL) but sometimes for associative arrays we could prefer the label=>value model (OPT_LABEL2VALUE).
* You can use your own combination with defined constants OPT_VALUE_IS_VALUE, OPT_VALUE_IS_KEY, OPT_LABEL_IS_VALUE and OPT_LABEL_IS_KEY.
* Common combinations are OPT_LABEL2VALUE, OPT_VALUE2LABEL and OPT_VALUE.
* The label is prefixed with $prefix and translated using t().
*/
function htmlOptions($fieldPath, $values, $default=null, $matches=null, $prefix='', $domain='global') {
	if( is_null($matches) ) { $matches = OPT_VALUE2LABEL; }
	// Value of selected/default option
	fillInputValue($selValue, $fieldPath, $default);
	$opts = '';
	foreach( $values as $dataKey => $elValue ) {
		$addAttr = '';
		if( is_array($elValue) ) {
			list($elValue, $addAttr) = array_pad($elValue, 2, null);
		}
		$optLabel = bintest($matches, OPT_LABEL_IS_KEY) ? $dataKey : $elValue;
		$optValue = bintest($matches, OPT_VALUE_IS_KEY) ? $dataKey : $elValue;
		$opts .= htmlOption($optValue, t($prefix.$optLabel, $domain), $selValue==$optValue, $addAttr);
	}
	return $opts;
}
define('OPT_VALUE_IS_VALUE'	 , 0);
define('OPT_VALUE_IS_KEY'	 , 1);
define('OPT_LABEL_IS_VALUE'	 , 0);
define('OPT_LABEL_IS_KEY'	 , 2);
define('OPT_LABEL2VALUE'	 , OPT_VALUE_IS_VALUE | OPT_LABEL_IS_KEY);
define('OPT_VALUE2LABEL'	 , OPT_VALUE_IS_KEY | OPT_LABEL_IS_VALUE);
define('OPT_VALUE'			 , OPT_VALUE_IS_VALUE | OPT_LABEL_IS_VALUE);

//! Generates a selected attribute
/*!
* \param $fieldPath The field path to use to define name.
* \param $default The default value.
* \param $addAttr additional attributes.
* \return A HTML source for the built selected attribute.
* \sa htmlSelect()
* \sa htmlOptions()
*
* Generates a HTML source as selected attribute for a SELECT.
* This function is useful for very customized select which could not use htmlSelect().
*/
// function htmlOptionValue($field, $value, $data=null, $attr='selected') {
// 	if( is_null($data) ) {
// 		$data = isset($GLOBALS['formData']) ? $GLOBALS['formData'] : POST();
// 	}
// 	return (isset($data[$field]) && $value == $data[$field]) ? 'selected="selected"' : '';
// }

function htmlText($fieldPath, $default='', $addAttr='') {
// 	$f = explode('/', $fieldPath); $fl=count($f)-1; if( $fl && isset($f[0], $f[$fl]) ) { $GLOBALS['FORM_FIELDS'][$f[0]][] = $f[$fl]; } 
	$value = fillInputValue($value, $fieldPath) ? $value : $default;
	return '<input type="text" name="'.apath_html($fieldPath).'" '.(empty($value) ? '' : 'value="'.$value.'" ').$addAttr.'/>';
}

function htmlTextArea($fieldPath, $default='', $addAttr='') {
// 	$f = explode('/', $fieldPath); $fl=count($f)-1; if( $fl && isset($f[0], $f[$fl]) ) { $GLOBALS['FORM_FIELDS'][$f[0]][] = $f[$fl]; } 
	$value = fillInputValue($value, $fieldPath) ? $value : $default;
	return '<textarea name="'.apath_html($fieldPath).'" '.$addAttr.'>'.$value.'</textarea>';
}

function htmlHidden($fieldPath, $default='', $addAttr='') {
// 	$f = explode('/', $fieldPath); $fl=count($f)-1; if( $fl && isset($f[0], $f[$fl]) ) { $GLOBALS['FORM_FIELDS'][$f[0]][] = $f[$fl]; } 
	$value = fillInputValue($value, $fieldPath) ? $value : $default;
	return '<input type="hidden" name="'.apath_html($fieldPath).'" '.(empty($value) ? '' : 'value="'.$value.'" ').$addAttr.'/>';
}

function htmlRadio($fieldPath, $elValue, $default=false, $addAttr='') {
// 	$f = explode('/', $fieldPath); $fl=count($f)-1; if( $fl && isset($f[0], $f[$fl]) ) { $GLOBALS['FORM_FIELDS'][$f[0]][] = $f[$fl]; } 
	$selected = fillInputValue($value, $fieldPath) ? $value==$elValue : $default;
	return '<input type="radio" name="'.apath_html($fieldPath).'" value="'.$elValue.'" '.($selected ? 'checked="checked"' : '').' '.$addAttr.'/>';
}

function htmlCheckBox($fieldPath, $default=false, $addAttr='') {
	// Checkbox : Null => Undefined, False => Unchecked, 'on' => Checked
// 	$selected = fillInputValue($value, $fieldPath, false) ? !empty($value) : $default;
// 	text("htmlCheckbox($fieldPath): default: $default");
	// 			If Value found,	we consider this one, else we use default
	$selected = ($r = fillInputValue($value, $fieldPath, $default, true)) ? !empty($value) : $default;
// 	text($selected ? 'SELECTED' : 'NOT SELECTED');
// 	text($r ? 'VALUE NOT NULL' : 'NULL VALUE');
// 	text('VALUE: '.$value);
	return '<input type="checkbox" name="'.apath_html($fieldPath).'" '.($selected ? 'checked="checked"' : '').' '.$addAttr.'/>';
}

function htmlOption($elValue, $label=null, $selected=false, $addAttr='') {
	if( is_null($label) ) { $label = $elValue; }
// 	$selected = fillInputValue($value, $fieldPath) ? $value==$elValue : $default;
	return '<option value="'.$elValue.'"'.($selected ? ' selected="selected"' : '').' '.$addAttr.'>'.$label.'</option>';
}

function apath_html($apath) {
	$apath = explode('/', $apath);
	$htmlName = '';
	foreach( $apath as $index => $path ) {
		$htmlName .= ( $index ) ? '['.$path.']' : $path;
	}
	return $htmlName;
}

//! Gets input form data
/*!
 * \return POST() or global $formData if set.
 *
 * Gets input form data from POST.
 * Developers can specify an array of data to use by filling global $formData.
 * This function is designed to be used internally to have compliant way to get input form data.
 */
function getFormData() {
	return isset($GLOBALS['formData']) ? $GLOBALS['formData'] : POST();
}

//! Fills the given data from input form
/*!
 * \param $data The data to fill, as pointer.
 * \return The resulting $data.
 * \sa getFormData()
 *
 * Fills the given pointer data array with input form data if null.
 * This function is designed to only offset the case where $data is null.
 */
function fillFormData(&$data) {
	return $data = is_null($data) ? getFormData() : $data;
}

//! Fills the given value from input form
/*!
 * \param $value The value to fill, as pointer.
 * \param $fieldPath The apath to the input form value.
 * \param $default The default value if not found. Default value is null (apath_get()'s default).
 * \param $pathRequired True if the path is required. Default value is False (apath_get()'s default).
 * \return True if got value is not null (found).
 * \sa getFormData()
 * \sa apath_get()
 *
 * Fills the given pointer value with input form data or uses default.
 */
function fillInputValue(&$value, $fieldPath, $default=null, $pathRequired=false) {
	$value = apath_get(getFormData(), $fieldPath, $default, $pathRequired);
	if( is_null($value) ) {
		$value = $default;
	}
	return !is_null($value);
}

//! Converts special characters to non-special ones
/*!
 * \param $string The string to convert.
 * \return The string wih no special characters.
 *
 * Replaces all special characters in $string by the non-special version of theses.
 */
function convertSpecialChars($string) {
	// Replaces all letter special characters.
	$string = str_replace(
		array(
			'À','à','Á','á','Â','â','Ã','ã','Ä','ä','Å','å','A','a','A','a',
			'C','c','C','c','Ç','ç',
			'D','d','Ð','d',
			'È','è','É','é','Ê','ê','Ë','ë','E','e','E','e',
			'G','g',
			'Ì','ì','Í','í','Î','î','Ï','ï',
			'L','l','L','l','L','l',
			'Ñ','ñ','N','n','N','n',
			'Ò','ò','Ó','ó','Ô','ô','Õ','õ','Ö','ö','Ø','ø','o',
			'R','r','R','r',
			'Š','š','S','s','S','s',
			'T','t','T','t','T','t',
			'Ù','ù','Ú','ú','Û','û','Ü','ü','U','u',
			'Ÿ','ÿ','ý','Ý',
			'Ž','ž','Z','z','Z','z',
			'Þ','þ','Ð','ð','ß','Œ','œ','Æ','æ','µ',
		' '),
		//'”','“','‘','’',"'","\n","\r",'£','$','€','¤'), //Just deleted
		array(
			'A','a','A','a','A','a','A','a','Ae','ae','A','a','A','a','A','a',
			'C','c','C','c','C','c',
			'D','d','D','d',
			'E','e','E','e','E','e','E','e','E','e','E','e',
			'G','g',
			'I','i','I','i','I','i','I','i',
			'L','l','L','l','L','l',
			'N','n','N','n','N','n',
			'O','o','O','o','O','o','O','o','Oe','oe','O','o','o',
			'R','r','R','r',
			'S','s','S','s','S','s',
			'T','t','T','t','T','t',
			'U','u','U','u','U','u','Ue','ue','U','u',
			'Y','y','Y','y',
			'Z','z','Z','z','Z','z',
			'TH','th','DH','dh','ss','OE','oe','AE','ae','u',
		'_'), $string);
		//'','','','','','',''), $string);
	// Now replaces all other special character by nothing.
	$string = preg_replace('#[^a-z0-9\-\_\.]#i', '', $string);
	return $string;
}

//! Converts the string into a slug
/*!
 * \param $string The string to convert.
 * \param $case The case style to use, values: null (default), LOWERCAMELCASE or UPPERCAMELCASE.
 * \return The slug version.
 *
 * Converts string to lower case and converts all special characters. 
*/
function toSlug($string, $case=null) {
	$string = strtolower($string);
	if( isset($case) ) {
		if( bintest($case, CAMELCASE) ) {
			$string = str_replace(' ', '', ucwords(str_replace('&', 'and', $string)));
			if( $case == LOWERCAMELCASE ) {
				$string = lcfirst($string);
			}
		}
	}
	return convertSpecialChars($string);
}
defifn('CAMELCASE',			1<<0);
defifn('LOWERCAMELCASE',	CAMELCASE);
defifn('UPPERCAMELCASE',	CAMELCASE | 1<<1);

//! Converts the boolean into a string
function bool2str($v) {
	return ($v ? 'True' : 'False');
}

//! Splits a string by string in limited values
/*!
 * \param $delimiter	The boundary string.
 * \param $string		The input string.
 * \param $limit		The limit of values exploded.
 * \param $default		The default value to use if missing.
 * \return An array of a defined number of values.
 * \sa explode()
 * 
 * Splits a string by string in a limited number of values.
 * The main difference with explode() is this function complete missing values with $default.
 * If you want $limit optional, use explode()
 */
function explodeList($delimiter, $string, $limit, $default=null) {
	return array_pad(explode($delimiter, $string, $limit), abs($limit), $default);
}

//! Gets the string of a boolean
/*!
 * \param $b The boolean.
* \return The boolean's string.
*/
function b($b) {
	return $b ? 'TRUE' : 'FALSE';
}

//! Gets the date as string
/*!
 * \param $time The UNIX timestamp.
* \return The date using 'dateFormat' translation key
*/
function d($time=TIME) {
	return strftime(t('dateFormat'), $time);
}

//! Gets the date time as string
/*!
 * \param $time The UNIX timestamp.
 * \return The date using 'timeFormat' translation key
*/
function dt($time=TIME) {
	return strftime(t('timeFormat'), $time);
}

//! Generates a new password
/*!
 * \param $length Thelength of the generated password.
 * \param $chars The characters to use to generate password.
 * \param $notlast Avoid $notlast characters at end of $chars.
 * \return The generated password.
 * 
 * This generator is made for humans, it avoids some special letters as first/last one.
 * So, place these characters at the end of $chars.
*/
function generatePassword($length=10, $chars='abcdefghijklmnopqrstuvwxyz0123456789?!$@+-', $notlast=2) {
	$max = strlen($chars)-1;
	$r = '';
	for( $i=0; $i<$length; $i++ ) {
		$c = $chars[mt_rand(0, (!$i || $i>=($length-1)) ? $max-$notlast : $max)];
		$r .= mt_rand(0, 1) ? strtoupper($c) : $c;
	}
	return $r;
}

//! Returns the day timestamp using the given integer
/*!
 * \param $time The time to get the day time. Default value is current timestamp.
 * 
 * Returns the timestamp of the current day of $time according to the midnight hour.
*/
function dayTime($time=null) {
	if( is_null($time) ) { $time = time(); }
	return $time - $time%86400 - date('Z');
}

//! Returns the timestamp of the $day of the month using the given integer
/*!
 * \param $day The day of the month to get the timestamp. Default value is 1, the first day of the month.
 * \param $time The time to get the month timestamp. Default value is current timestamp.
 * \sa dayTime()
 *
 * Returns the timestamp of the $day of current month of $time according to the midnight hour.
*/
function monthTime($day=1, $time=null) {
	if( is_null($time) ) { $time = time(); }
	return dayTime($time - (date('j', $time)-$day)*86400);
}
