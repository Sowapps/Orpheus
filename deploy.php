<?php

define('PROJECT_PATH', './');
define('COMPOSER_CONFIG_PATH', PROJECT_PATH.'composer.json');
define('COMPOSER_SETUP_PATH', PROJECT_PATH.'composer-setup.php');
define('COMPOSER_PHAR_PATH', './composer.phar');
define('VENDOR_PATH', PROJECT_PATH.'vendor');

$FatalError = null;
try {
	// Fatal system error
	if( !is_writable(PROJECT_PATH) ) {
		throw new Exception('This folder should be writable to use composer.');
	}
	if( !is_readable(COMPOSER_CONFIG_PATH) ) {
		throw new Exception('Application is unable to read composer file at "'.COMPOSER_CONFIG_PATH.'".');
	}
} catch( Exception $e ) {
	$FatalError = $e;
}

$UserError = null;
try {
	// Non-Fatal system error
	if( is_readable(VENDOR_PATH) ) {
		throw new Exception('This project is already deployed !');
	}
} catch( Exception $e ) {
	$UserError = $e;
}

function execCommand($cmd, &$output=null) {
	if( !$output ) {
		$output = '';
	}
	$output .= 'Run command: '.$cmd.'<br>';
	// exec($cmd.' 2>&1', $output);
	// $output = implode("\n", $output);

	// $output .= shell_exec($cmd);

	ob_start();
	$return = null;
	system($cmd.' 2>&1', $return);
	$cmdOutput = ob_get_clean();
	// $output .= $cmdOutput;
	$output .= 'Got Result: '.$return.' ['.($return ? 'ERROR' : 'OK').']<br>';

	$output .= 'Got Output ['.strlen($cmdOutput).']: <pre class="m-b-0">'.$cmdOutput.'</pre>';
	// $output .= 'Got Output ['.strlen($cmdOutput).']: <pre>'.$cmdOutput.'</pre><br>';
	return 1;
	// return $return;
}

$Output = null;
try {
	// User Action Error

	// var_dump($_POST);echo '<br>';
	if( isset($_POST['submitStartDeployment']) ) {
		$Output = 'Start to deploy<br>';
		putenv('COMPOSER_HOME="'.PROJECT_PATH.'"');
		// TODO: Add deploy lock on server
		if( !is_readable(COMPOSER_PHAR_PATH) ) {
			copy('https://getcomposer.org/installer', COMPOSER_SETUP_PATH);
			execCommand('php '.COMPOSER_SETUP_PATH.' --install-dir="'.PROJECT_PATH.'"', $Output);
			unlink(COMPOSER_SETUP_PATH);
			if( !is_readable(COMPOSER_PHAR_PATH) ) {
				throw new Exception('Failed to get composer.phar');
			}
		}
		execCommand('cd "'.PROJECT_PATH.'"; php '.COMPOSER_PHAR_PATH.' install', $Output);
	}

} catch( Exception $e ) {
	$UserError = $e;
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Required meta tags always come first -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css" integrity="2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous">
	</head>
	<body style="padding-top: 5rem;">
		
		<nav class="navbar navbar-fixed-top navbar-dark bg-inverse">
			<a class="navbar-brand" href="deploy.php">Orpheus Deploy Tool</a>
		</nav>
		
		<div class="container text-xs-center">
			<div class="row">
				<div class="col-lg-8 offset-lg-2">
				
					<h1>Welcome to the composer's hell !</h1>
					<p>This tool is designed to help you to deploy your app on a new server or a new instance.</p>
					
					<?php
					if( $FatalError ) {
						?>
						<div class="alert alert-danger" role="alert">
							<strong title="This is a fatal error">Mayday !</strong> <?php echo $FatalError->getMessage(); ?>
						</div>
						<?php
					} else {
						if( $UserError ) {
							?>
							<div class="alert alert-warning" role="alert">
								<strong title="This is a user-action error">Oops !</strong> <?php echo $UserError->getMessage(); ?>
							</div>
							<?php
						}
						if( $Output ) {
							if( !$UserError ) {
								?>
								<div class="alert alert-success" role="alert">
									<strong title="This is a success">Yeah !</strong> We successfully deployed your application.
								</div>
								<?php
							}
							?>
							<blockquote class="blockquote text-xs-left" style="font-size: 0.9rem;">
								<?php echo $Output; ?>
							</blockquote>
							<?php
							
						}
						if( !$Output || $UserError ) {
							?>
							<h3>Deploy your App !</h3>
							<p>Orpheus is ready to start the deployment, just press this big button. ;-)</p>
							<form method="POST">
								<input type="hidden" name="submitStartDeployment" value="1" />
								<button type="submit" class="action-deploy btn btn-primary btn-lg">START</button>
							</form>
						<?php
						}
					}
					
					
					?>
				</div>
			</div>
		</div>

		<!-- jQuery first, then Tether, then Bootstrap JS. -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js" integrity="sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.2.0/js/tether.min.js" integrity="sha384-Plbmg8JY28KFelvJVai01l8WyZzrYWG825m+cZ0eDDS1f7d/js6ikvy1+X+guPIB" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js" integrity="VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"></script>
		
		<script>
			$(function() {
				$(".action-deploy").click(function() {
					$(this).prop("disabled", true).text("Deploying your app...");
					$(this).closest("form").submit();
					return true;
				});
			});
		</script>
	</body>
</html>