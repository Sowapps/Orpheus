#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

# Change working directory to this file's folder
chdir(__DIR__);

$projectPath = "../";
$scriptPath = __DIR__;
$composerPath = "$scriptPath/composer.phar";
$currentUser = isset($_SERVER['USER']) ? $_SERVER['USER'] : null;

function writeError($text) {
	fwrite(STDERR, $text . PHP_EOL);
}

if( $currentUser === 'root' ) {
	writeError("Please, don't use root to update the project, use your own project user !");
	exit(1);
}

chdir($projectPath);

if( !file_exists($composerPath) ) {
	copy('https://getcomposer.org/installer', 'composer-setup.php');
	`php composer-setup.php --install-dir="$scriptPath"`;
	unlink('composer-setup.php');
} else {
	`php $composerPath self-update`;
}

`php $composerPath update`;
