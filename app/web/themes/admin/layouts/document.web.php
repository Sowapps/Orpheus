<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var TranslationService $translator
 *
 * @var string $CONTROLLER_OUTPUT
 * @var string $content
 * @var array $breadcrumb
 */

use App\Controller\Admin\AbstractAdminController;
use App\Entity\User;
use Orpheus\Initernationalization\TranslationService;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;
use Orpheus\Service\SecurityService;

/** @var User $user */
$user = SecurityService::get()->getActiveUser();
/** @var User $authenticatedUser */
$authenticatedUser = SecurityService::get()->getAuthenticatedUser();

$pageTitle = $controller->getOption(AbstractAdminController::OPTION_PAGE_TITLE, $pageTitle ?? null);

$invertedStyle = $controller->getOption('invertedStyle', 1);
?>
<!DOCTYPE html>
<html lang="<?php echo $translator->getHttpLocale(); ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $pageTitle ?? t('app_name'); ?></title>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHOR_NAME; ?>"/>
	<meta name="application-name" content="<?php echo t('app_name'); ?>"/>
	<meta name="msapplication-starturl" content="<?php echo WEB_ROOT; ?>"/>
	<meta name="Keywords" content="projet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
	<link rel="icon" type="image/png" href="<?php echo STATIC_ASSETS_URL . '/images/logo-32.png'; ?>"/>
	<?php
	foreach( $rendering->listMetaProperties() as $property => $content ) {
		?>
		<meta name="<?php echo $property; ?>" content="<?php echo $content; ?>"/>
		<?php
	}
	?>
	
	<link rel="stylesheet" href="<?php echo VENDOR_URL; ?>/sb-admin/sb-admin-7.0.4/css/styles.css" media="screen"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/css/tom-select.bootstrap5.min.css" media="screen"/>
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
<body class="sb-nav-fixed">

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
	
	<a class="navbar-brand ps-3" href="<?php echo u(DEFAULT_ROUTE); ?>"><?php echo t($controller->getOption('main_title', 'app_name')); ?></a>
	
	<button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
		<i class="fa-solid fa-bars"></i>
	</button>
	
	<ul class="navbar-nav ms-auto me-3 me-lg-4">
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
				<i class="fa-solid fa-user fa-fw"></i>
			</a>
			<ul class="dropdown-menu dropdown-menu-end">
				<li>
					<a class="dropdown-item" href="<?php echo u(ROUTE_ADM_MY_SETTINGS); ?>">
						<i class="fa fa-gear me-1"></i>
						<?php echo t(ROUTE_ADM_MY_SETTINGS); ?>
					</a>
				</li>
				<li>
					<hr class="dropdown-divider">
				</li>
				<li>
					<a class="dropdown-item" href="<?php echo u(ROUTE_USER_LOGOUT); ?>">
						<i class="fa fa-power-off me-1"></i>
						<?php echo t(ROUTE_USER_LOGOUT); ?>
					</a>
				</li>
			</ul>
		</li>
	</ul>
</nav>

<div id="layoutSidenav">
	
	<div id="layoutSidenav_nav">
		<nav class="sb-sidenav accordion <?php echo $invertedStyle ? 'sb-sidenav-dark' : 'sb-sidenav-light'; ?>" id="sidenavAccordion">
			<div class="sb-sidenav-menu">
				<div class="nav">
					
					<?php
					$menus = [
						'user'      => true,
						'admin'     => $authenticatedUser->hasRoleAccessLevel('administrator'),
						'developer' => $authenticatedUser->hasRoleAccessLevel('developer'),
					];
					
					foreach( $menus as $menu => $allowedMenu ) {
						if( !$allowedMenu ) {
							continue;
						}
						$rendering->showMenu($menu, 'menu.sidebar');
					}
					?>
				</div>
			</div>
			<div class="sb-sidenav-footer">
				<?php
				if( $authenticatedUser ) {
					?>
					<div class="small"><?php echo t('logged_as'); ?></div>
					<?php echo $authenticatedUser; ?>
					<div class="small"><?php echo t('ip_address'); ?></div>
					<?php echo clientIP();
				}
				?>
			</div>
		</nav>
	</div>
	
	<div id="layoutSidenav_content">
		<main>
			<?php echo $content; ?>
		</main>
		<footer class="py-4 bg-light mt-auto">
			<div class="container-fluid px-4">
				<div class="d-flex align-items-center justify-content-between small">
					<div class="text-muted">Orpheus Framework, as this website, is free and open source. They are both developed by Florent HAZARD (Sowapps).</div>
				</div>
			</div>
		</footer>
	</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/js/tom-select.complete.js"></script>
<script src="<?php echo VENDOR_URL; ?>/sb-admin/sb-admin-7.0.4/js/scripts.js"></script>
<?php
foreach( $rendering->listJsUrls(HtmlRendering::LINK_TYPE_PLUGIN) as $url ) {
	?>
	<script src="<?php echo $url; ?>"></script>
	<?php
}
?>

<script src="<?php echo VENDOR_URL; ?>/orpheus/js/orpheus.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/services/dom.service.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/dialogs/confirm.dialog.js"></script>
<script src="<?php echo $rendering->getJsUrl(); ?>/script.js"></script>

<?php
foreach( $rendering->listJsUrls() as $url ) {
	?>
	<script src="<?php echo $url; ?>"></script>
	<?php
}
?>
</body>
</html>
