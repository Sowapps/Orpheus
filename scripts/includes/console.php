<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

function writeError($text): void {
	fwrite(STDERR, $text . PHP_EOL);
}

function writeText($text): void {
	fwrite(STDOUT, $text . PHP_EOL);
}

function writeInfo($text): void {
	fwrite(STDOUT, "\e[34m" . $text . "\e[0m" . PHP_EOL);
}

function writeSuccess($text): void {
	fwrite(STDOUT, "\e[32m" . $text . "\e[0m" . PHP_EOL);
}
