<?php

use Demo\User;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;

/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HTTPController $Controller
 * @var HTTPRequest $Request
 * @var HTTPRoute $Route
 * @var User $user
 * @var string $Content
 */

global $APP_LANG;

$libExtension = DEV_VERSION ? '' : '.min';

?>
<!DOCTYPE html>
<html lang="<?php echo $APP_LANG; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo !empty($PageTitle) ? $PageTitle : t('app_name'); ?></title>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHORNAME; ?>"/>
	<meta name="application-name" content="<?php _t('app_name'); ?>"/>
	<meta name="msapplication-starturl" content="<?php echo DEFAULTLINK; ?>"/>
	<meta name="Keywords" content="projet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
	<?php
	foreach( $rendering->listMetaProperties() as $property => $content ) {
		echo '
	<meta property="' . $property . '" content="' . $content . '"/>';
	}
	?>
	
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap<?php echo $libExtension; ?>.css" media="screen"/>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all<?php echo $libExtension; ?>.css" media="screen"/>
	<?php
	foreach( $rendering->listCssUrls(HTMLRendering::LINK_TYPE_PLUGIN) as $url ) {
		echo '
	<link rel="stylesheet" href="' . $url . '" type="text/css" media="screen" />';
	}
	?>
	
	<link rel="stylesheet" href="<?php echo $rendering->getThemeUrl(); ?>libs/sb-admin/css/styles.css" type="text/css" media="screen"/>
	<link rel="stylesheet" href="<?php echo STATIC_ASSETS_URL; ?>/style/base.css" type="text/css" media="screen"/>
	<link rel="stylesheet" href="<?php echo $rendering->getCssUrl(); ?>style.css" type="text/css" media="screen"/>
	<?php
	foreach( $rendering->listCssUrls() as $url ) {
		echo '
	<link rel="stylesheet" type="text/css" href="' . $url . '" media="screen" />';
	}
	?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery<?php echo $libExtension; ?>.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<div class="container">
		<a class="navbar-brand" href="<?php echo SITEROOT; ?>">
			<?php echo t('app_name'); ?>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#MenuTop" aria-controls="MenuTop" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="MenuTop">
			<?php
			$rendering->showMenu('topmenu');
			if( !empty($TOPBAR_CONTENTS) ) {
				echo $TOPBAR_CONTENTS;
			}
			?>
		
		</div>
	</div>
</nav>

<main role="main">
	<?php
	echo $Content;
	// If report was not be reported
	$this->display('reports');
	?>
</main>

<!-- JS libraries -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui<?php echo $libExtension; ?>.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper<?php echo $libExtension; ?>.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap<?php echo $libExtension; ?>.js"></script>
<?php
foreach( $this->listJSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url ) {
	echo '
	<script type="text/javascript" src="' . $url . '"></script>';
}
?>

<!-- Our JS scripts -->
<script src="<?php echo $rendering->getThemeUrl(); ?>libs/sb-admin/js/scripts.js"></script>
<script src="<?php echo JS_URL; ?>orpheus.js"></script>
<script src="<?php echo JS_URL; ?>orpheus-confirmdialog.js"></script>

<?php
foreach( $rendering->listJsUrls() as $url ) {
	echo '
	<script type="text/javascript" src="' . $url . '"></script>';
}
?>

</
>
</html>
