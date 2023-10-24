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

$pageTitle = $controller->getOption(HttpController::OPTION_PAGE_TITLE, $pageTitle ?? t('app_name'));
$pageDescription = $controller->getOption(HttpController::OPTION_PAGE_DESCRIPTION, $pageDescription ?? null);

?>
<!DOCTYPE html>
<html lang="<?php echo $translator->getHttpLocale(); ?>" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $pageTitle; ?></title>
	<?php
	if( $pageDescription ) {
		?>
		<meta name="Description" content="<?php echo $pageDescription; ?>"/>
		<?php
	}
	?>
	<meta name="Author" content="<?php echo AUTHOR_NAME; ?>"/>
	<link rel="icon" type="image/png" href="<?php echo STATIC_ASSETS_URL . '/images/logo-32.png'; ?>"/>
	<?php
	foreach( $rendering->listMetaProperties() as $property => $content ) {
		?>
		<meta name="<?php echo $property; ?>" content="<?php echo $content; ?>"/>
		<?php
	}
	?>
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.2/css/bootstrap.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="screen"/>
	
	<?php
	foreach( $rendering->listCssUrls(HtmlRendering::LINK_TYPE_PLUGIN) as $url ) {
		?>
		<link rel="stylesheet" href="<?php echo $url; ?>" media="screen"/>
		<?php
	}
	?>
	
	<link rel="stylesheet" href="<?php echo $rendering->getCssUrl(); ?>/style.css" media="screen"/>
	<?php
	foreach( $rendering->listCssUrls() as $url ) {
		?>
		<link rel="stylesheet" href="<?php echo $url; ?>" media="screen"/>
		<?php
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
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#MenuTop" aria-controls="MenuTop" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="MenuTop">
			<?php $rendering->showMenu('main'); ?>
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
	?>
	<script src="<?php echo $url; ?>"></script>
	<?php
}
?>

<!-- Our JS scripts -->
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/orpheus.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/services/dom.service.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/dialogs/confirm.dialog.js"></script>

<?php
foreach( $rendering->listJsUrls() as $url ) {
	?>
	<script src="<?php echo $url; ?>"></script>
	<?php
}
?>
</body>
</html>
