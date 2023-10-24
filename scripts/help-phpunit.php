#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

$commands = [
	'tests --testdox',
];

echo "\nPHPUnit common commands\n\n";

foreach( $commands as $subCommand ) {
	printf('./vendor/bin/phpunit %s' . "\n", $subCommand);
}
