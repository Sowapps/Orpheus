<?php
/**
 * @file Orpheus/loader.php
 * @brief The Orpheus Loader
 * @author Florent Hazard
 * @copyright The MIT License, see LICENSE.txt
 *
 * PHP File for the website core.
 */

use Orpheus\Controller\DelayedPageController;

if( !isset($SRCPATHS) ) {
	$SRCPATHS = [];
}

define('DEV_LEVEL', E_ALL | E_STRICT);//Development
define('PROD_LEVEL', 0);//Production

/** Defines an undefined constant.
 *
 * @param string $name The name of the constant.
 * @param int|string $value The value of the constant.
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
 *
 * @param string $path The path get parent directory
 * @return string The secured path
 * @see dirname()
 *
 * Gets the parent directory path of $path
 */
function dirpath($path) {
	$dirName = dirname($path);
	return $dirName === '/' ? '/' : $dirName . '/';
}

/** Gets the path of a file/directory.
 *
 * @param string $commonPath The common path
 * @param boolean $silent Do not throw exception if path does not exist
 * @return string The first valid path or null if there is no valid one.
 * @see addSrcPath()
 *
 * This function uses global variable $SRCPATHS to get the known paths.
 * It allows developers to get a dynamic path to a file.
 */
function pathOf($commonPath, $silent = false) {
	global $SRCPATHS;
	for( $i = count($SRCPATHS) - 1; $i >= 0; $i-- ) {
		if( file_exists($SRCPATHS[$i] . $commonPath) ) {
			return $SRCPATHS[$i] . $commonPath;
		}
	}
	if( $silent ) {
		return null;
	}
	throw new Exception('Path not found: ' . $commonPath);
}

/**
 * Checks if the path exists.
 * This function uses pathOf() to determine possible path of $commonPath and checks if there is any file with this path in file system.
 *
 * @param string $commonPath The common path.
 * @param string $path The output parameter to get the first valid path.
 * @return bool
 * @see pathOf()
 */
function existsPathOf($commonPath, &$path = null) {
	return ($path = pathOf($commonPath, true)) !== null;
}

/**
 * Add the path to the known paths
 *
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

/**
 * List all source paths
 *
 * @return string[]
 */
function listSrcPath() {
	global $SRCPATHS;
	return $SRCPATHS;
}

/**
 * Include a directory
 *
 * @param string $dir The directory to include.
 * @param array $importants The files in that are importants to load first.
 * @return int The number of files included.
 *
 * Include all files with a name beginning by '_' in the directory $dir.
 * It browses recursively through sub-directories.
 */
function includeDir($dir, $importants = []) {
	//Require to be immediatly available.
	$files = array_unique(array_merge($importants, scandir($dir)));
	
	$i = 0;
	foreach( $files as $file ) {
		// If file is not readable or hidden, we pass.
		if( !is_readable($dir . $file) || $file[0] == '.' ) {
			continue;
		}
		//We don't check infinite file system loops.
		if( is_dir($dir . $file) ) {
			$i += includeDir($dir . $file . '/');
		} else {
			if( $file[0] == '_' ) {
				require_once $dir . $file;
				$i++;
			}
		}
	}
	return $i;
}

/**
 * Include a directory by source path
 *
 * @param string $dir The directory to include.
 * @param array $importants The files in that are importants to load first.
 * @return int The number of files included.
 * @see includeDir()
 *
 * Include all files with a name beginning by '_' in the directory $dir.
 * It browses recursively through sub-directories.
 */
function includePath($path, $importants = []) {
	return includeDir(pathOf($path), $importants);
}

/**
 * Escape a text
 *
 * @param string $str The string to escape
 * @param int $flags The flags of htmlentities()
 * @return string The escaped string
 * Escape the text $str from special characters.
 */
function escapeText($str, $flags = ENT_NOQUOTES) {
	return htmlentities(str_replace("\'", "'", $str), $flags, 'UTF-8', false);
}

// Experimental
function ob_end_to($min) {
	$min = max($min, 0);
	while( ob_get_level() > $min ) {
		ob_end_flush();
	}
}

/**
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
define('HTTP_OK', 200);
define('HTTP_MOVED_PERMANENTLY', 301);
define('HTTP_FOUND', 302);
define('HTTP_MOVED_TEMPORARILY', HTTP_FOUND);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_SERVER_ERROR', 500);

function http_response_codetext($code = null) {
	if( $code === null ) {
		$code = http_response_code();
	}
	static $codeTexts;
	if( !isset($codeTexts) ) {
		$codeTexts = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
		];
	}
	return isset($codeTexts[$code]) ? $codeTexts[$code] : 'Unknown';
}

function displayException(Throwable $Exception, $action) {
	if( IS_CONSOLE ) {
		displayExceptionAsText($Exception, $action);
	} else {
		displayExceptionAsHTML($Exception, $action);
	}
}

function displayExceptionAsHTML(Throwable $Exception, $action) {
	$code = $Exception->getCode();
	if( $code < 100 ) {
		$code = HTTP_INTERNAL_SERVER_ERROR;
	}
	http_response_code($code);
	die(convertExceptionAsHTMLPage($Exception, $code, $action));
}

function displayExceptionAsText(Throwable $Exception, $action) {
	$code = $Exception->getCode();
	if( $code < 100 ) {
		$code = HTTP_INTERNAL_SERVER_ERROR;
	}
	die(convertExceptionAsText($Exception, $code, $action));
}

function typeOf($var) {
	$type = gettype($var);
	if( $type === 'object' ) {
		return get_class($var);
	}
	return $type;
}

function findFileInTree($filename, $from = null) {
	$from = realpath($from ?: APPLICATIONPATH);
	while( $from && $from !== '/' && is_readable($from) ) {
		$filePath = $from . '/' . $filename;
		if( is_readable($filePath) ) {
			return $filePath;
		}
		$from = dirname($from);
	}
	return null;
}

function displayRawException(Throwable $Exception) {
	?>
	<h3><?php echo get_class($Exception); ?></h3>
	<blockquote class="exception_message">
		<?php echo $Exception->getMessage(); ?>
		<footer>In <cite><?php echo $Exception->getFile(); ?></cite> at line <?php echo $Exception->getLine(); ?></footer>
	</blockquote>
	<?php
	displayExceptionStackTrace($Exception);
}

function displayStackTrace($backtrace) {
	?>
	<ol>
		<?php
		foreach( $backtrace as $trace ) {
			// file, line, function, args
			if( !isset($trace['class']) ) {
				$trace['class'] = null;
			}
			if( !isset($trace['type']) ) {
				$trace['type'] = null;
			}
			$args = '';
			if( isset($trace['args']) ) {
				foreach( $trace['args'] as $i => $arg ) {
					if( is_array($arg) ) {
						$argTxt = '[' . count($arg) . ']';
					} elseif( is_callable($arg) ) {
						$argTxt = '{callable}';
					} else {
						$argTxt = ' "<span class="arg_value">' . escapeText((is_object($arg) && !method_exists($arg, '__toString')) ? get_class($arg) : $arg . '') . '</span>"';
					}
					$args .= ($i ? ', ' : '') . '
		<span class="arg"><span class="arg_type">' . typeOf($arg) . '</span> ' . $argTxt . '</span>';
				}
			}
			?>
			<li class="trace">
				Call <?php echo $trace['class'] . $trace['type'] . $trace['function'] . '(' . $args . ')' ?><br/>
				<address>In <?php echo isset($trace['file']) ? $trace['file'] . ' at line ' . $trace['line'] : 'an unknown file'; ?></address>
			</li>
			<?php
		}
		?>
	</ol>
	<?php
}

function displayExceptionStackTrace(Throwable $Exception) {
	$backtrace = $Exception->getTrace();
	if( $Exception->getCode() == 1 && is_array($GLOBALS['DEBUG_BACKTRACE']) ) {
		$backtrace = $GLOBALS['DEBUG_BACKTRACE'];
	}
	displayStackTrace($backtrace);
}

function getClassName($var) {
	$class = is_object($var) ? get_class($var) : $var;
	$hierarchy = explode('\\', $class);
	
	return array_pop($hierarchy);
}

/**
 * @param Throwable $exception
 * @param $code
 * @return false|string
 */
function convertExceptionAsHTMLPage(Throwable $exception, $code) {
	// TODO: Add resubmit button
	// TODO: Display already sent headers and contents
	// TODO: Externalize this and allow developers to ovverride it
	// Clean all buffers
	$buffer = '';
	while( ob_get_level() ) {
		$buffer = ob_get_clean() . $buffer;
	}
	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>An error occurred :: Orpheus</title>
		
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0/css/all.min.css">
	</head>
	<body>
	
	<div class="container">
		
		<header class="align-items-center d-flex mt-2 py-2">
			<h3 class="mr-auto text-muted">Orpheus</h3>
			<nav class="my-2 my-md-0">
				<a class="p-2 text-dark" href="<?php echo WEB_ROOT; ?>">Home</a>
			</nav>
		</header>
		
		<main role="main" class="mt-3">
			
			<div class="card border border-danger">
				<div class="card-header text-white bg-danger">An error occurred !</div>
				<div class="card-body exception">
					<h3 class="card-title" title="<?php echo get_class($exception); ?>">
						<?php echo $code . ' ' . http_response_codetext($code); ?>
						<small> - <?php echo getClassName($exception); ?></small>
					</h3>
					
					<blockquote class="blockquote exception_message">
						<?php echo $exception->getMessage(); ?>
						<footer class="blockquote-footer">In <cite><?php echo $exception->getFile(); ?></cite> at line <?php echo $exception->getLine(); ?></footer>
					</blockquote>
					
					<div class="sourcecode">
						<ul class="sourcecode_lines px-1">
							<?php
							$excLine = $exception->getLine();
							$fileContent = file_get_contents($exception->getFile());
							$lines = substr_count($fileContent, PHP_EOL);
							for( $i = 0; $i <= $lines; $i++ ) {
								echo '
								<li' . ($excLine == $i + 1 ? ' class="active"' : '') . '>' . ($i + 1) . '</li>';
							}
							?>
						</ul>
						<div class="sourcecode_content">
							<?php echo highlight_string($fileContent, true);
							unset($fileContent); ?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="card border border-danger mt-2">
				<div class="card-header text-white bg-danger">Here is the stacktrace...</div>
				<div class="card-body exception">
					<?php displayExceptionStackTrace($exception); ?>
				</div>
			</div>
			
			<?php
			if( trim($buffer) && class_exists('DelayedPageController', true) ) {
				try {
					$bufferSrc = DelayedPageController::store(uniqid('error'), $buffer);
					?>
					<div class="panel panel-danger">
						<div class="panel-heading">The buffer is not empty, maybe this could help you...</div>
						<div class="panel-body buffer">
							<div class="embed-responsive embed-responsive-16by9">
								<iframe class="embed-responsive-item" src="<?php echo $bufferSrc; ?>"></iframe>
							</div>
						</div>
					</div>
					<?php
				} catch( Exception $e ) {
					?>
					<div class="panel panel-danger">
						<div class="panel-heading">An exception occurred storing the delayed page...</div>
						<div class="panel-body buffer">
							<?php displayRawException($e); ?>
						</div>
					</div>
					<?php
					
				}
			}
			?>
		</main>
	</div>
	<style>
	header {
		border-bottom: 1px solid #e5e5e5;
	}
	
	.arg_type {
		font-weight: bold;
		font-size: 0.9em;
	}
	
	.arg_value {
		white-space: nowrap;
		max-width: 120px;
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
	
	.sourcecode {
		height: 176px; /* 10+1 lines * line-height */
		line-height: 16px;
		overflow-y: scroll;
		display: flex;
	}
	
	.sourcecode_lines {
		float: left;
		list-style: none;
		font-size: 12px;
		margin: 0;
		border-right: 1px solid #CCC;
		text-align: right;
	}
	
	.sourcecode_lines li:after {
		content: "\00a0";
		margin-left: 6px;
	}
	
	.sourcecode_lines li.active {
		background: #F2DEDE;
		color: #a94442;
	}
	
	.sourcecode_lines li.active:after {
		content: ">";
		margin-left: 2px;
	}
	
	.sourcecode_content {
		width: 100%;
		height: 100%;
		white-space: nowrap;
	}
	
	.sourcecode_content code {
		line-height: inherit;
		display: block;
	}
	</style>
	
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js" type="text/javascript"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(function () {
		$(".arg_value").click(function () {
			$(this).toggleClass("nolimit");
		});
		$(".sourcecode").scrollTop(<?php
			// Line height * (exception line - (1 + Lines before)
			echo 16 * ($excLine - 6);
			?>);
	});
	</script>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

function convertExceptionAsText(Throwable $Exception, $code, $action) {
	// Clean all buffers
	while( ob_get_level() ) {
		ob_end_clean();
	}
	ob_start();
	?>
	*****************************************
	************* ~  Orpheus  ~ *************
	*****************************************
	*********  Error Report System  *********
	*****************************************
	
	We caught an exception of type <?php echo get_class($Exception); ?>:
	> <?php echo $Exception->getMessage() . "\n"; ?>
	> In <?php echo $Exception->getFile(); ?> at line <?php echo $Exception->getLine(); ?>
	
	<?php echo formatSourceAsText($Exception->getFile(), $Exception->getLine(), 4, 2); ?>
	
	Stacktrace:<?php
	foreach( $Exception->getTrace() as $trace ) {
		// file, line, function, args
		if( !isset($trace['class']) ) {
			$trace['class'] = null;
		}
		if( !isset($trace['type']) ) {
			$trace['type'] = null;
		}
		$args = '';
		if( !empty($trace['args']) ) {
			foreach( $trace['args'] as $i => $arg ) {
				$args .= ($i ? ', ' : '') . typeOf($arg) . (is_array($arg) ? '[' . count($arg) . ']' : (is_string_convertible($arg) ? ' ' . $arg : ''));
			}
		}
		echo "
 - Call {$trace['class']}{$trace['type']}{$trace['function']}({$args})
   In " . (isset($trace['file']) ? $trace['file'] . ' at line ' . $trace['line'] : 'an unknown file') . "\n";
	}
	echo "\n";
	return ob_get_clean();
}

function formatSourceAsHTML($file, $lineNumber, $linesBefore, $linesAfter) {
	// Partial highlight not working, send all file
	$from = max($lineNumber - $linesBefore, 0);
	$to = $lineNumber + $linesAfter;
	$count = 0;
	$string = getFileLines($file, $from, $to, $count);
	$lines = '';
	for( $line = $from; $line < $from + $count; $line++ ) {
		$lines .= '<li>' . $line . ($lineNumber == $line ? '&nbsp;&nbsp;>' : '') . '</li>';
	}
	
	$string = highlight_source($string, true);
	return <<<EOF
<div class="sourcecode">
	<ul class="sourcecode_lines">{$lines}</ul>
	{$string}
</div>
EOF;
}

function formatSourceAsText($file, $activeLineNumber, $linesBefore, $linesAfter) {
	$from = max($activeLineNumber - $linesBefore, 0);
	$to = $activeLineNumber + $linesAfter;
	$count = 0;
	$lines = getFileLines($file, $from, $to, $count, true);
	$lineLen = strlen($to);
	$result = '';
	foreach( $lines as $lineNumber => $line ) {
		$result .=
			'| ' . str_pad($lineNumber, $lineLen, ' ', STR_PAD_RIGHT) . ($lineNumber == $activeLineNumber ? ' >' : '  ') . ' | ' . $line;
	}
	return $result;
}

function highlight_source($string, $return = false) {
	return highlight_string("<?php\n" . $string, true);
}

function getFileLines($file, $from, $to, &$count = 0, $asArray = false) {
	if( is_string($file) ) {
		$file = fopen($file, 'r');
	}
	$lines = [];
	$c = 0;
	$lineNb = $from;
	while( ($line = fgets($file)) !== false ) {
		$c++;
		if( $c >= $from ) {
			if( $c > $to ) {
				break;
			}
			$lines[$lineNb++] = $line;
		}
	}
	$count = count($lines);
	return $asArray ? $lines : implode('', $lines);
}

/** Displays a variable as HTML
 *
 * @param mixed $message The data to display. Default value is an empty string.
 * @param boolean $html True to add html tags. Default value is True.
 * @warning Use it only for debugs.
 * Displays a variable as HTML.
 * If the constant TERMINAL is defined, parameter $html is forced to False.
 */
function text($message = '', $html = true) {
	if( IS_CONSOLE ) {
		$html = false;
	}
	if( !is_scalar($message) ) {
		$message = print_r($message, 1);
		if( $html ) {
			$message = '<pre>' . $message . '</pre>';
		}
	}
	echo $message . ($html ? '<br />' : '') . "\n";
	if( IS_CONSOLE ) {
		flush();
	}
}

/** Limits the length of a string
 *
 * @param string $string The string to limit length.
 * @param int $max The maximum length of the string.
 * @param int $strend A string to append to the shortened string.
 * @return string The shortened string.
 * Limits the length of a string and append $strend.
 * This function do it cleanly, it tries to cut before a word.
 */
function str_limit($string, $max, $strend = '...'): string {
	$max = (int) $max;
	if( $max <= 0 ) {
		return '';
	}
	if( strlen($string) <= $max ) {
		return $string;
	}
	$subStr = substr($string, 0, $max);
	if( !in_array($string[$max], ["\n", "\r", "\t", " "]) ) {
		$lSpaceInd = strrpos($subStr, ' ');
		if( $max - $lSpaceInd < 10 ) {
			$subStr = substr($string, 0, $lSpaceInd);
		}
	}
	return $subStr . $strend;
}
