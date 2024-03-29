<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

use Orpheus\Controller\DelayedPageController;

const ERROR_DEBUG_LEVEL = E_ALL;//Development
const ERROR_PROD_LEVEL = E_CORE_ERROR;//Production

/**
 * Define an undefined constant.
 *
 * @param string $name The name of the constant.
 * @param string|int|bool $value The value of the constant.
 * @return True if the constant was defined successfully, else False.
 */
function defifn(string $name, string|int|bool $value): bool {
	if( !defined($name) ) {
		define($name, $value);
		
		return true;
	}
	
	return false;
}

/**
 * Get the nullable value of the constant
 * @return mixed|null The value of constant or null if undefined
 */
function safeConstant(string $name): mixed {
	return defined($name) ? constant($name) : null;
}

/**
 * Gets the directory path
 *
 * @param string $path The path get parent directory
 * @return string The secured path
 * @see dirname()
 */
function getParentPath(string $path): string {
	$dirName = dirname($path);
	
	return $dirName === '/' ? '' : $dirName;
}

/**
 * Gets the path of a file/directory.
 * This function uses global variable $APP_SOURCE_PATHS to get the known paths.
 * It allows developers to get a dynamic path to a file.
 *
 * @param string $relativePath The common path
 * @param bool $silent Do not throw exception if path does not exist
 * @return string|null The first valid path or null if there is no valid one.
 * @see addSrcPath()
 */
function pathOf(string $relativePath, bool $silent = false): ?string {
	global $APP_SOURCE_PATHS;
	$APP_SOURCE_PATHS ??= [];
	for( $i = count($APP_SOURCE_PATHS) - 1; $i >= 0; $i-- ) {
		$path = $APP_SOURCE_PATHS[$i] . $relativePath;
		if( file_exists($path) ) {
			return $path;
		}
	}
	if( $silent ) {
		return null;
	}
	throw new RuntimeException(sprintf('Path not found: "%s"', $relativePath));
}

const DEFAULT_PACKAGES = ['@application'];

/**
 * Checks if the path exists.
 * This function uses pathOf() to determine possible path of $commonPath and checks if there is any file with this path in file system.
 *
 * @param string $commonPath The common path
 * @param string|null $path The output parameter to get the first valid path
 * @throws Exception
 * @see pathOf()
 */
function existsPathOf(string $commonPath, ?string &$path = null): bool {
	return ($path = pathOf($commonPath, true)) !== null;
}

/**
 * Add the path to the known paths
 *
 * @param string $path The source path to add.
 * @return boolean True if the path was added.
 * @see pathOf()
 */
function addSrcPath(string $path): bool {
	global $APP_SOURCE_PATHS;
	$APP_SOURCE_PATHS ??= [];
	if( in_array($path, $APP_SOURCE_PATHS) ) {
		return false;
	}
	$APP_SOURCE_PATHS[] = $path;
	
	return true;
}

/**
 * List all source paths
 *
 * @return string[]
 */
function listSrcPath(): array {
	global $APP_SOURCE_PATHS;
	$APP_SOURCE_PATHS ??= [];
	
	return $APP_SOURCE_PATHS;
}

/**
 * Include a directory
 *
 * @param string $folder The directory to include.
 * @param array $importants The files in that are importants to load first.
 * @return int The number of files included.
 *
 * Include all files with a name beginning by '_' in the directory $dir.
 * It browses recursively through sub folders.
 */
function includeFolder(string $folder, array $importants = []): int {
	//Require to be immediately available.
	$files = array_unique(array_merge($importants, scandir($folder)));
	
	$i = 0;
	foreach( $files as $file ) {
		// If file is not readable or hidden, we pass.
		if( !is_readable($folder . $file) || $file[0] == '.' ) {
			continue;
		}
		//We don't check infinite file system loops.
		if( is_dir($folder . $file) ) {
			$i += includeFolder($folder . $file . '/');
		} else {
			if( $file[0] == '_' ) {
				require_once $folder . $file;
				$i++;
			}
		}
	}
	
	return $i;
}

/**
 * Escape the text $str from special characters
 *
 * @param string $str The string to escape
 * @param int $flags The flags of htmlentities()
 * @return string The escaped string
 */
function escapeText(string $str, int $flags = ENT_NOQUOTES): string {
	return htmlentities(str_replace("\'", "'", $str), $flags, 'UTF-8', false);
}

/**
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
const HTTP_OK = 200;
const HTTP_MOVED_PERMANENTLY = 301;
const HTTP_FOUND = 302;
const HTTP_MOVED_TEMPORARILY = HTTP_FOUND;
const HTTP_BAD_REQUEST = 400;
const HTTP_UNAUTHORIZED = 401;
const HTTP_FORBIDDEN = 403;
const HTTP_NOT_FOUND = 404;
const HTTP_INTERNAL_SERVER_ERROR = 500;

function http_response_code_text(?int $code = null) {
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
	
	return $codeTexts[$code] ?? 'Unknown';
}

function displayException(Throwable $Exception): never {
	if( IS_CONSOLE ) {
		displayExceptionAsText($Exception);
	} else {
		displayExceptionAsHTML($Exception);
	}
}

function displayExceptionAsHTML(Throwable $Exception): never {
	$code = $Exception->getCode();
	if( $code < 100 ) {
		$code = HTTP_INTERNAL_SERVER_ERROR;
	}
	http_response_code($code);
	die(convertExceptionAsHTMLPage($Exception, $code));
}

function displayExceptionAsText(Throwable $Exception): never {
	$code = $Exception->getCode();
	if( $code < 100 ) {
		$code = HTTP_INTERNAL_SERVER_ERROR;
	}
	die(convertExceptionAsText($Exception, $code));
}

function typeOf($var): string {
	$type = gettype($var);
	if( $type === 'object' ) {
		return get_class($var);
	}
	
	return $type;
}

function displayRawException(Throwable $Exception): void {
	?>
	<h3><?php echo get_class($Exception); ?></h3>
	<blockquote class="exception_message">
		<?php echo $Exception->getMessage(); ?>
		<footer>In <cite><?php echo $Exception->getFile(); ?></cite> at line <?php echo $Exception->getLine(); ?></footer>
	</blockquote>
	<?php
	displayExceptionStackTrace($Exception);
}

function displayStackTraceAsHtml(array $backtrace): void {
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
					} else if( $arg === null ) {
						$argTxt = '';
					} else {
						$argTxt = ' "<span class="arg_value">' . escapeText((is_object($arg) && !method_exists($arg, '__toString')) ? get_class($arg) : $arg . '') . '</span>"';
					}
					$args .= ($i ? ', ' : '') . '
		<span class="arg"><span class="arg_type">' . typeOf($arg) . '</span> ' . $argTxt . '</span>';
				}
			}
			?>
			<li class="trace">
				Call <?php echo $trace['class'] . $trace['type'] . $trace['function'] . '(' . $args . ($args ? ' ' : '') . ')' ?><br/>
				<address>In <?php echo isset($trace['file']) ? $trace['file'] . ' at line ' . $trace['line'] : 'an unknown file'; ?></address>
			</li>
			<?php
		}
		?>
	</ol>
	<?php
}

function displayExceptionStackTrace(Throwable $Exception): void {
	$backtrace = $Exception->getTrace();
	if( $Exception->getCode() == 1 && is_array($GLOBALS['DEBUG_BACKTRACE']) ) {
		$backtrace = $GLOBALS['DEBUG_BACKTRACE'];
	}
	displayStackTraceAsHtml($backtrace);
}

function getClassName(object|string $var): string {
	$class = is_object($var) ? get_class($var) : $var;
	$hierarchy = explode('\\', $class);
	
	return array_pop($hierarchy);
}

function processException(Throwable $exception, $log = null): void {
	if( $log !== false && function_exists('log_error') ) {
		log_error($exception, $log);
	}
	if( defined('DEBUG_ENABLED') && DEBUG_ENABLED ) {
		displayException($exception);
	} else {
		die('A fatal error occurred.');
	}
}

function getErrorException(array $error): ErrorException {
	$class = null;
	$severity = E_ERROR;
	if( $error['type'] === E_COMPILE_ERROR || $error['type'] === E_COMPILE_WARNING ) {
		$class = 'Orpheus\Exception\CompilerException';
		$severity = $error['type'] === E_COMPILE_WARNING ? E_WARNING : E_ERROR;
	}
	
	if( $class ) {
		$exception = new $class($error['message'], $error['type'], $severity, $error['file'], $error['line']);
	} else {
		$exception = new ErrorException($error['message'], $error['type'], $severity, $error['file'], $error['line']);
	}
	
	return $exception;
}

function convertExceptionAsHTMLPage(Throwable $exception, int $code): string {
	// TODO: Add resubmit button
	// TODO: Display already sent headers and contents
	// TODO: Externalize this and allow developers to override it
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
		
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	</head>
	<body>
	
	<div class="container">
		
		<header class="align-items-center d-flex mt-2 py-2">
			<h3 class="me-auto text-muted">Orpheus</h3>
			<nav class="my-2 my-md-0">
				<a class="p-2 text-dark" href="<?php echo WEB_ROOT; ?>">Home</a>
			</nav>
		</header>
		
		<main role="main" class="mt-3">
			
			<div class="card border border-danger">
				<div class="card-header text-white bg-danger">An error occurred !</div>
				<div class="card-body exception">
					<h3 class="card-title" title="<?php echo get_class($exception); ?>">
						<?php echo $code . ' ' . http_response_code_text($code); ?>
						<small> - <?php echo getClassName($exception); ?></small>
					</h3>
					
					<figure>
						<blockquote class="blockquote exception_message">
							<p><?php echo $exception->getMessage(); ?></p>
						</blockquote>
						<figcaption class="blockquote-footer">
							In <cite><?php echo $exception->getFile(); ?></cite> at line <?php echo $exception->getLine(); ?>
						</figcaption>
					</figure>
					
					<div class="sourcecode">
						<ul class="sourcecode_lines px-1">
							<?php
							$excLine = $exception->getLine();
							$fileContent = file_get_contents($exception->getFile());
							$lines = substr_count($fileContent, PHP_EOL);
							for( $i = 0; $i <= $lines; $i++ ) {
								?>
								<li <?php if($excLine === $i + 1) { ?>class="active"<?php } ?>>
									<?php echo $i + 1; ?>
								</li>
								<?php
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
			if( trim($buffer) && class_exists('DelayedPageController') ) {
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
	<?php /** @noinspection HtmlUnknownTag */ ?>
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
		height: 17rem; /* 10+1 lines * line-height */
		resize: vertical;
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
		white-space: nowrap;
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
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.min.js"></script>
	<script>
		<?php
		// Line height * (exception line - (1 + Lines before)
		$scrollTop = 16 * ($excLine - 6);
		?>
		window.addEventListener("DOMContentLoaded", () => {
			document.querySelectorAll(".arg_value")
				.forEach(($element) => {
					$element.addEventListener("click", () => {
						$element.classList.toggle("nolimit");
					});
				});
			const $source = document.querySelector(".sourcecode");
			const scrollTop = <?php echo $scrollTop; ?>;
			$source.scrollTo(0, scrollTop);
	});
	</script>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

function convertExceptionAsText(Throwable $Exception, int $code): string {
	// Clean all buffers
	while( ob_get_level() ) {
		ob_end_clean();
	}
	ob_start();
	http_response_code($code);
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
		$trace['class'] ??= null;
		$trace['type'] ??= null;
		$trace['args'] ??= null;
		$args = '';
		if( $trace['args'] ) {
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

function formatSourceAsText(mixed $file, int $activeLineNumber, int $linesBefore, int $linesAfter): string {
	$from = max($activeLineNumber - $linesBefore, 0);
	$to = $activeLineNumber + $linesAfter;
	$count = 0;
	$lines = getFileLines($file, $from, $to, $count, true);
	$lineLen = strlen($to);
	$result = '';
	foreach( $lines as $lineNumber => $line ) {
		$result .=
			'| ' . str_pad($lineNumber, $lineLen) . ($lineNumber === $activeLineNumber ? ' >' : '  ') . ' | ' . $line;
	}
	
	return $result;
}

function getFileLines(mixed $file, int $from, int $to, int &$count = 0, bool $asArray = false): array|string {
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

/**
 * Limits the length of a string and append $end.
 * This function do it cleanly, it tries to cut before a word.
 *
 * @param string $string The string to limit length.
 * @param int $max The maximum length of the string.
 * @param string $end A string to append to the shortened string.
 * @return string The shortened string.
 */
function str_limit(string $string, int $max, string $end = '...'): string {
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
	
	return $subStr . $end;
}
