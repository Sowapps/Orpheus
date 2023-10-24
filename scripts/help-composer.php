#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

$commands = [
	'self-update',
	'update',
	'install',
];

echo "\nComposer common commands\n\n";

foreach( $commands as $subCommand ) {
	printf('composer %s' . "\n", $subCommand);
}
