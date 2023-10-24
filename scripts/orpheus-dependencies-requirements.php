#!/usr/bin/env php
<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

require_once 'includes/console.php';

$path = getcwd();
$excluded = ['orpheus/orpheus-doc', 'orpheus/orpheus-framework', 'orpheus/orpheus-setup', 'orpheus/orpheus-website'];
//$only = ['orpheus/orpheus-core'];// Test only one
$only = [];// Normal way

$commandOptions = getopt('hvy', ['help', 'verbose', 'dry-run', 'version:', 'restore:', 'remove-saves:']);
$help = isset($commandOptions['h']) || isset($commandOptions['help']);
$verbose = isset($commandOptions['v']) || isset($commandOptions['verbose']);
$dryRun = isset($commandOptions['dry-run']);
$forceYes = isset($commandOptions['y']);
$version = $commandOptions['version'] ?? '*';
$restore = $commandOptions['restore'] ?? null;
$removeSaves = $commandOptions['remove-saves'] ?? null;

if( $restore && $removeSaves ) {
	writeError('Invalid argument usage : Can not mix --restore and --remove-saves options');
	exit(1);
}

if( $help ) {
	printf(<<<OUT
./%s
Run into your Orpheus parent folder, it will scan the current folder for orpheus packages.
We actively recommend using the verbose option and to try operation previously using dry-run.
Script always saves the current composer.json of dependencies before applying changes into a composer.[DATE].json file.
Options:
 - -v / --verbose : Verbose mode
 - --dry-run : Dry run, do not apply any change
 - --version : Set version of all requirements of packages
 - -y : Force confirm to "Yes"
 - --restore="latest|REVISION" : Restore a previous version for all versions, Empty or "latest" to remove the latest of each package (could be different).
 - --remove-saves="latest|all|REVISION" : Remove a save revision for all packages, by revision name, you could also use "latest" and "all".
 - -h --help : Show this help
OUT, basename(__FILE__));
	exit;
}

function parseYesNoValue(string|false $value, string $default = 'y'): bool {
	return match (strtolower($value ?: $default)) {
		'y' => true,
		'n' => false,
		default => throw new InvalidArgumentException('Invalid value'),
	};
}

function getComposerRevisions(string $path): array {
	$revisions = [];
	foreach( scandir($path, SCANDIR_SORT_DESCENDING) as $file ) {
		$filePath = $path . '/' . $file;
		if( is_file($filePath) && preg_match('#composer.(\d+).json#i', $file, $matches) ) {
			$revisions[$matches[1]] = $filePath;
			$revisions['latest'] ??= $matches[1];
		}
	}
	
	return $revisions;
}

$saving = !$removeSaves;
$ln = "\n";

$packages = [];
foreach( scandir($path) as $file ) {
	$packagePath = $path . '/' . $file;
	if( is_dir($packagePath) && $file[0] !== '.' ) {
		$packageComposerPath = $packagePath . '/composer.json';
		if( !is_readable($packageComposerPath) ) {
			continue;
		}
		$package = json_decode(file_get_contents($packageComposerPath), true);
		if( empty($package['name']) ) {
			if( $verbose ) {
				printf('Exclude package "%s" with empty name' . $ln, $file);
			}
			continue;
		}
		if( $only && !in_array($package['name'], $only) ) {
			if( $verbose ) {
				printf('Exclude package "%s", not in white list' . $ln, $package['name']);
			}
			continue;
		}
		if( in_array($package['name'], $excluded) ) {
			if( $verbose ) {
				printf('Exclude package "%s" in exclusion list' . $ln, $package['name']);
			}
			continue;
		}
		$packages[] = [$file, $packagePath, $package['name'], $packageComposerPath];
	}
}

$revision = date('YmdHis');

if( $restore ) {
	// Restore mode (latest save by default)
	writeInfo(sprintf('Script will restore previous version (%s) of composer file as the active composer.json.', $restore));
} else if( $removeSaves ) {
	// Remove saves
	writeInfo(sprintf('Script will remove revision (%s) of composer file.', $removeSaves));
} else {
	// Editor mode
	writeInfo(sprintf('Script will look into path "%s" and replace all orpheus packages version requirements by "%s".', $path, $version));
}

if( !$packages ) {
	writeError('Interrupting : No Orpheus packages found in this folder.');
	exit(1);
}

printf('Here are all the packages script will apply :%s%s', $ln, implode(', ', array_map(function ($package) {
		return $package[2];
	}, $packages)) . $ln);
if( $saving ) {
	printf('Current composer.json file of any dependency will be saved into the "composer.%s.json" file.' . $ln, $revision);
}

if( $dryRun ) {
	printf('This a dry run, no changes will be made.' . $ln, $path, $version);
	$apply = $forceYes || parseYesNoValue(readline('Do you want to continue ? [Y/n] '));
} else {
	$apply = $forceYes || parseYesNoValue(readline('Do you want to proceed now ? [Y/n] '));
}

if( !$apply ) {
	printf("Abort operation !");
	exit;
}

// For each package of Orpheus
foreach( $packages as [, $packagePath, $packageName, $packageComposerPath] ) {
	$save = function () use ($packageName, $packagePath, $packageComposerPath, $revision, $verbose, $dryRun, $ln) {
		if( $verbose ) {
			printf('In package "%s", save active composer.json into to revision composer.%s.json' . "\n", $packageName, $revision);
		}
		if( !$dryRun ) {
			//			printf('copy(%s, %s)' . $ln, $packageComposerPath, sprintf('%s/composer.%s.json', $packagePath, $revision));
			copy($packageComposerPath, sprintf('%s/composer.%s.json', $packagePath, $revision));
		}
	};
	if( $restore ) {
		// Restore a previous revision
		$packageComposerRevisions = getComposerRevisions($packagePath);
		$restoreRevision = $restore === 'latest' ? $packageComposerRevisions['latest'] ?? null : $restore;
		$restorePath = $restoreRevision ? $packageComposerRevisions[$restoreRevision] ?? null : null;
		if( $restorePath ) {
			// Path exists
			if( $verbose ) {
				printf('In package "%s", restore revision "%s" as active composer.json...', $packageName, $restoreRevision);
			}
			$save();
			if( !$dryRun ) {
				// Perform the operation - Copy revision as active composer file
				copy($restorePath, $packageComposerPath);
				if( $verbose ) {
					printf(' Done !' . $ln);
				}
			} else {
				if( $verbose ) {
					printf(' Faked (dry run) !' . $ln);
				}
			}
		} else {
			printf('In package "%s", no composer save matching revision "%s", ignoring.' . $ln, $packageName, $restore);
		}
	} else if( $removeSaves ) {
		// Remove saves/revisions of composer
		// $removeSaves must be latest, all or a revision name
		$packageComposerRevisions = getComposerRevisions($packagePath);
		$revision = null;
		$removeRevisions = null;
		if( $removeSaves === 'latest' ) {
			$revision = $packageComposerRevisions['latest'] ?? null;
		} else if( $removeSaves === 'all' ) {
			$removeRevisions = $packageComposerRevisions;
			unset($removeRevisions['latest']);
		} else {
			$revision = $removeSaves;
		}
		if( $revision ) {
			$removeRevisions = isset($packageComposerRevisions[$revision]) ? [$revision => $packageComposerRevisions[$revision]] : null;
		}
		if( !$removeRevisions ) {
			printf('In package "%s", no composer save to remove matching "%s", ignoring.' . $ln, $packageName, $removeSaves);
		} else {
			foreach( $removeRevisions as $revision => $revisionPath ) {
				if( $verbose ) {
					printf('In package "%s", remove revision "%s"...', $packageName, $revision);
				}
				if( !$dryRun ) {
					// Perform the operation - Remove revision composer file
					unlink($revisionPath);
					if( $verbose ) {
						printf(' Done !' . $ln);
					}
				} else {
					if( $verbose ) {
						printf(' Faked (dry run) !' . $ln);
					}
				}
			}
		}
	} else {
		// Set version of Orpheus' requirements
		$contents = file_get_contents($packageComposerPath);
		$contents = preg_replace_callback('#"(orpheus/[^"]+)": "[^"]+"#i', function ($matches) use ($verbose, $ln, $packageName, $version) {
			if( $verbose ) {
				printf('In package "%s", change version of requirement "%s" to "%s"' . $ln, $packageName, $matches[1], $version);
			}
			
			return sprintf('"%s": "%s"', $matches[1], $version);
		}, $contents, -1, $count);
		if( $count ) {
			// Save only if having changes
			$save();
		}
		if( $verbose ) {
			printf('Save %d changes on package "%s" to "%s"...', $count, $packageName, $packageComposerPath);
		}
		if( !$count ) {
			if( $verbose ) {
				printf(' Ignored (no changes detected) !' . $ln);
			}
		} else if( $dryRun ) {
			if( $verbose ) {
				printf(' Faked (dry run) !' . $ln);
			}
		} else {
			// Perform the operation - Update active composer file with new changes
			file_put_contents($packageComposerPath, $contents);
			if( $verbose ) {
				printf(' Done !' . $ln);
			}
		}
	}
}

if( $dryRun ) {
	writeInfo("This was a dry run, no changes was made !");
} else {
	writeSuccess("Operation done !");
}
