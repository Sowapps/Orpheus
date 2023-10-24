#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

$commands = [
	'update-index --chmod=+x FILE',
];

echo "\nGit common commands\n\n";

foreach( $commands as $subCommand ) {
	printf('git %s' . "\n", $subCommand);
}
