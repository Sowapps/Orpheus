<?php
/**
 * @file Orpheus/loader.php
 * @brief The Orpheus Loader
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 * 
 * PHP File for the website core.
 */

if( !isset($SRCPATHS) ) {
	$SRCPATHS = array();
}

define('DEV_LEVEL',			E_ALL | E_STRICT);//Development
define('PROD_LEVEL',		0);//Production

/** Defines an undefined constant.

 * @param $name		The name of the constant.
 * @param $value	The value of the constant.
 * @return True if the constant was defined successfully, else False.
 * 
 *  Defines a constant if this one is not defined yet.
 */
function defifn($name, $value) {
	if( defined($name) ) {
		return false;
	}
	define($name, $value);
	return true;
}

/** Gets the directory path

 * @param $path The path get parent directory
 * @return The secured path
 * @sa dirname()
 * 
 * Gets the parent directory path of $path
 */
function dirpath($path) {
	$dirname = dirname($path);
	return $dirname === '/' ? '/' : $dirname.'/';
}

/** Gets the path of a file/directory.

 * @param string $commonPath The common path
 * @param boolean $silent Do not throw exception if path does not exist
 * @return The first valid path or null if there is no valid one.
 * @sa addSrcPath()
 * 
 * This function uses global variable $SRCPATHS to get the known paths.
 * It allows developers to get a dynamic path to a file.
 */
function pathOf($commonPath, $silent=false) {
	global $SRCPATHS;
	for( $i=count($SRCPATHS)-1; $i>=0; $i-- ) {
		if( file_exists($SRCPATHS[$i].$commonPath) ) {
			return $SRCPATHS[$i].$commonPath;
		}
	}
	if( $silent ) { return null; }
	throw new Exception('Path not found: '.$commonPath);
}

/** Checks if the path exists.

 * @param string $commonPath The common path.
 * @param string $path The output parameter to get the first valid path.
 * @sa pathOf()
 * 
 * This function uses pathOf() to determine possible path of $commonPath and checks if there is any file with this path in file system.
 */
function existsPathOf($commonPath, &$path=null) {
	return ($path=pathOf($commonPath, true))!==NULL;
}

/** Adds the path to the known paths.

 * @param string $path The source path to add.
 * @return boolean True if the path was added.
 * @see pathOf()
 */
function addSrcPath($path) {
	global $SRCPATHS;
	if( in_array($path, $SRCPATHS) ) {
		return false;
	}
	$SRCPATHS[] = $path;
	return true;
}

/** Includes a directory

	\param $dir The directory to include.
	\param $importants The files in that are importants to load first.
	\return The number of files included.
	
	Includes all files with a name beginning by '_' in the directory $dir.
	It browses recursively through sub-directories.
*/
function includeDir($dir, $importants=array()) {
	//Require to be immediatly available.
	$files = array_unique(array_merge($importants, scandir($dir)));
	
	$i=0;
	foreach($files as $file) {
		// If file is not readable or hidden, we pass.
		if( !is_readable($dir.$file) || $file[0] == '.' ) {
			continue;
		}
		//We don't check infinite file system loops.
		if( is_dir($dir.$file) ) {
			$i += includeDir($dir.$file.'/');
		} else if( $file[0] == '_' ) {
			require_once $dir.$file;
			$i++;
		}
	}
	return $i;
}

/** Includes a directory

	\param $path The relative directory path to include.
	\param $importants The files in that are importants to load first.
	\return The number of files included.
	\sa includeDir()
	
	Includes all files with a name beginning by '_' in the directory $dir.
	It browses recursively through sub-directories.
*/
function includePath($path, $importants=array()) {
	return includeDir(pathOf($path), $importants);
}

// Experimental
function ob_end_to($min) {
	$min	= max($min, 0);
	while( ob_get_level() > $min ) {
		ob_end_flush();
	}
}

define('HTTP_OK',						200);
define('HTTP_BAD_REQUEST',				400);
define('HTTP_UNAUTHORIZED',				401);
define('HTTP_FORBIDDEN',				403);
define('HTTP_NOT_FOUND',				404);
define('HTTP_INTERNAL_SERVER_ERROR',	500);

function http_response_codetext($code=null) {
	if( $code === null ) {
		$code	= http_response_code();
	}
	$codeTexts	= array(
		100	=> 'Continue',
		101	=> 'Switching Protocols',
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',
		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Moved Temporarily',
		303	=> 'See Other',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		402	=> 'Payment Required',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Time-out',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Large',
		415	=> 'Unsupported Media Type',
		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Time-out',
		505	=> 'HTTP Version not supported',
	);
	return isset($codeTexts[$code]) ? $codeTexts[$code] : 'Unknown';
}

function displayExceptionAsHTML(Exception $Exception, $action) {
	$code	= $Exception->getCode();
	if( !$code ) {
		$code	= 500;
	}
	http_response_code($code);
	convertExceptionAsHTMLPage($Exception, $code, $action);
	die();
}

function typeOf($var) {
	$type	= gettype($var);
	if( $type === 'object' ) {
		return get_class($var);
	}
	return $type;
}

function convertExceptionAsHTMLPage(Exception $Exception, $code, $action) {
	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>An error occurred :: Orpheus</title>
	
	<meta charset="utf-8"
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
</head>
<body>

	<div class="container">
		<div class="header clearfix">
			<h3 class="text-muted">Orpheus</h3>
		</div>
		<div class="panel panel-danger">
			<div class="panel-heading">An error occurred !</div>
			<div class="panel-body exception">
				<h3><?php echo $code.' '.http_response_codetext($code).'<small> - '.get_class($Exception).'</small>'; ?></h3>
				<blockquote class="exception_message">
					<?php echo $Exception->getMessage(); ?>
					<footer>In <cite><?php echo $Exception->getFile(); ?></cite> at line <?php echo $Exception->getLine(); ?></footer>
				</blockquote>
				<?php
// 				$sources	= getFileLineContext($Exception->getFile(), $Exception->getLine(), 4, 2);
				highlight_source(getFileLineContext($Exception->getFile(), $Exception->getLine(), 4, 2));
				?>
				<?php /*
				<div class="exception_type"><?php echo $code.' '.http_response_codetext($code).' - '.get_class($Exception); ?></div>
				<address class="exception_location">In <?php echo $Exception->getFile(); ?> at line <?php echo $Exception->getLine(); ?></address>
				*/ ?>
			</div>
		</div>
		<div class="panel panel-danger">
			<div class="panel-heading">Here is the stacktrace...</div>
			<div class="panel-body exception">
				<ol>
	<?php
	foreach( $Exception->getTrace() as $trace ) {
		// file, line, function, args
		if( !isset($trace['class']) ) {
			$trace['class']	= null;
		}
		if( !isset($trace['type']) ) {
			$trace['type']	= null;
		}
		$args	= '';
		foreach( $trace['args'] as $i => $arg ) {
			$args .= ($i ? ', ' : '').'<span class="arg"><span class="arg_type">'.typeOf($arg).'</span> "<span class="arg_value">'.$arg.'</span>"</span>';
// 			$args .= ($i ? ', ' : '').typeOf($arg).' '.str_limit($arg.'', 15);
		}
// 		var_dump($trace['args']);
		?>
					<li class="trace">
						Call <?php echo $trace['class'].$trace['type'].$trace['function'].'('.$args.')' ?><br />
						<address>In <?php echo $trace['file']; ?> at line <?php echo $trace['line']; ?></address>
					</li>
		<?php
	}
	?>
				</ol>
			</div>
		</div>
	</div>
	
	<?php /*
	<div class="content exception">
	</div>
	<div class="content stacktrace">
		<h2 class="stacktrace_title">Stacktrace</h2>
		<ol>
	<?php
	foreach( $Exception->getTrace() as $trace ) {
		// file, line, function, args
		if( !isset($trace['class']) ) {
			$trace['class']	= null;
		}
		if( !isset($trace['type']) ) {
			$trace['type']	= null;
		}
		$args	= '';
		foreach( $trace['args'] as $i => $arg ) {
			$args .= ($i ? ', ' : '').'<span class="arg"><span class="arg_type">'.typeOf($arg).'</span> <span class="arg_value">'.$arg.'</span></span>';
// 			$args .= ($i ? ', ' : '').typeOf($arg).' '.str_limit($arg.'', 15);
		}
// 		var_dump($trace['args']);
		?>
			<li class="trace">
				Call <?php echo $trace['class'].$trace['type'].$trace['function'].'()' ?><br />
				<address>In <?php echo $trace['file']; ?> at line <?php echo $trace['line']; ?></address>
			</li>
		<?php
	}
	?>
		</ol>
	</div>
	*/ ?>
<style>
.header {
	padding-bottom: 20px;
	margin-bottom: 30px;
	border-bottom: 1px solid #e5e5e5;
}
.arg_type {
	font-weight: bold;
	font-size: 0.9em;
}
.arg_value {
	white-space: nowrap;
	max-width: 120px;
	max-width: 15ch;
	text-overflow: ellipsis;
	display: inline-block;
	overflow: hidden;
	vertical-align: bottom;
	
	font-style: italic;
	cursor: pointer;
}
.arg_value.nolimit {
	max-width: none;
}
/*
body {
	background: #EEE;
}
.content {
	width: 960px;
	padding: 20px;
	margin: 40px auto;
	background: #FFF;
	border: 1px solid #DDD;
	border-radius: 10px;
}
blockquote {
	margin: 5px 10px;
}
*/
</style>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js" type="text/javascript"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(function() {
	$(".arg_value").click(function() {
		$(this).toggleClass("nolimit");
	});
});
</script>
</body>
</html>
	<?php
	$content	= ob_get_contents();
	ob_end_clean();
	return $content;
}

function highlight_source($string, $return=false) {
	$string	= preg_replace();
	$length	= strlen($string);
	$spaces	= 0;
	$tabSpaces	= 4;
	$result	= '';
	for( $i=0; $i<$length; $i++ ) {
		$char	= $string[$i];
		$add	= $char;
		if( $char === "\t" ) {
			$add	= str_pad('', $tabSpaces-$spaces, ' ', STR_PAD_RIGHT);
			$spaces	= 0;
			
		} else
		if( $char === "\r" || $char === "\n" ) {
// 		if( $spaces >= $tabSpaces || in_array($char, array("\t", "\r", "\n")) ) {
			$spaces	= 0;
		} else {
			$spaces++;
		}
		if( $spaces >= $tabSpaces ) {
			$spaces	= 0;
		}
		$result	.= $add;
	}
	return highlight_string($result, $return);
}

/** Displays a variable as HTML
 * @param $message The data to display. Default value is an empty string.
 * @param $html True to add html tags. Default value is True.
 * @warning Use it only for debugs.

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
	echo $message.($html ? '<br />' : '')."\n";
}

function debug($s, $d=-1) {
	if( $d !== -1 ) {
		$s .= ': '.htmlSecret($d);
	}
	text($s);
}

function htmlSecret($message) {
	if( $message===NULL ) {
		$message = '{NULL}';
	} else if( $message === false ) {
		$message = '{FALSE}';
	} else if( $message === true ) {
		$message = '{TRUE}';
	} else if( !is_scalar($message) ) {
		$message = '<pre>'.print_r($message, 1).'</pre>';
	}
	return '<button type="button" onclick="this.nextSibling.style.display = this.nextSibling.style.display === \'none\' ? \'block\' : \'none\'; return 0;">'.t('Show').'</button><div style="display: none;">'.$message.'</div>';
// 	return '<button type="button" onclick="$(this).next().toggle(); return 0;">'.t('Show').'</button><div style="display: none;">'.$message.'</div>';
}

/** Limits the length of a string
 * @param string $string The string to limit length.
 * @param int $max The maximum length of the string.
 * @param int $strend A string to append to the shortened string.
 * @return The shortened string.

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

function getFileLineContext($file, $lineNumber, $linesBefore, $linesAfter) {
	return getFileLines($file, $lineNumber-$linesBefore, $lineNumber+$linesAfter);
}

function getFileLines($file, $from, $to) {
	if( is_string($file) ) {
		$file	= fopen($file, 'r');
	}
	$lines	= '';
	$c		= 0;
	while( ($line=fgets($file)) !== false ) {
		$c++;
		if( $c >= $from ) {
			if( $c >= $to ) {
				break;
			}
			$lines	.= $line;
		}
	}
	return $lines;
}
