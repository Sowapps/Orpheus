<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var TranslationService $translator
 *
 * @var string $CONTROLLER_OUTPUT
 * @var string $content
 * @var User $user
 */

use App\Entity\User;
use Orpheus\Initernationalization\TranslationService;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

?>
<!DOCTYPE html>
<html lang="<?php echo $translator->getHttpLocale(); ?>" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo !empty($pageTitle) ? $pageTitle : t('app_name'); ?></title>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHOR_NAME; ?>"/>
	<meta name="Robots" content="noindex, nofollow"/>
	<link rel="icon" type="image/png" href="<?php echo STATIC_ASSETS_URL . '/images/logo-32.png'; ?>"/>
	<?php
	foreach( $rendering->listMetaProperties() as $property => $content ) {
		echo '
	<meta property="' . $property . '" content="' . $content . '"/>';
	}
	?>
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="screen"/>
	
	<?php
	foreach( $rendering->listCssUrls(HtmlRendering::LINK_TYPE_PLUGIN) as $url ) {
		echo '
	<link rel="stylesheet" href="' . $url . '" media="screen" />';
	}
	?>
	
	<link rel="stylesheet" href="<?php echo $rendering->getCssUrl(); ?>/style.css" media="screen"/>
	<?php
	foreach( $rendering->listCssUrls() as $url ) {
		echo '
	<link rel="stylesheet" href="' . $url . '" media="screen" />';
	}
	?>
	
	<?php /* Allow view to provide inline scripts with generated contents */ ?>
	<script src="<?php echo VENDOR_URL; ?>/orpheus/js/bootstrap.js"></script>
</head>
<body class="d-flex flex-column h-100">

<nav class="navbar navbar-expand-md sticky-top bg-light border-bottom">
	<div class="container-md">
		<a class="navbar-brand" href="<?php echo u(ROUTE_HOME); ?>">
			<?php /*
			<img src="<?php echo STATIC_ASSETS_URL; ?>/images/logo-256.png" alt="<?php echo t('app_name'); ?>" style="height: 32px;">
 		*/ ?>
			<?php echo t('app_name'); ?>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#MenuTop" aria-controls="MenuTop" aria-expanded="false"
				aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="MenuTop">
			<?php
			$rendering->showMenu('main');
			if( !empty($TOPBAR_CONTENTS) ) {
				echo $TOPBAR_CONTENTS;
			}
			?>
		</div>
	</div>
</nav>

<?php
echo $content;
// If report was not be reported
$this->display('reports');
?>

<!-- JS libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/js/bootstrap.min.js"></script>
<?php
foreach( $rendering->listJsUrls(HtmlRendering::LINK_TYPE_PLUGIN) as $url ) {
	echo '
	<script src="' . $url . '"></script>';
}
/*<script src="<?php echo VENDOR_URL; ?>/sb-admin/sb-admin-7.0.4/js/scripts.js"></script>*/
?>

<!-- Our JS scripts -->
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/orpheus.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/services/dom.service.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/dialogs/confirm.dialog.js"></script>

<?php
foreach( $rendering->listJsUrls() as $url ) {
	echo '
	<script src="' . $url . '"></script>';
}
?>
</body>
</html>
