#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

# Change working directory to this file's folder
chdir(__DIR__ . '/..');

//$phpBin = '/usr/bin/php7.4';
$phpBin = 'php';

passthru($phpBin . ' app/console/run.php upgrade-database');
