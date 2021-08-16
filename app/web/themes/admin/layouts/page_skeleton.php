<?php
/**
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $CONTROLLER_OUTPUT
 * @var string $content
 * @var User $user
 */

use Demo\User;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;


/* @var array $Breadcrumb */
/* @var string $PageTitle */
/* @var boolean $NoContentTitle */
/* @var string $ContentTitle */
/* @var string $titleRoute */

global $APP_LANG;

$routeName = $controller->getRouteName();
$user = User::getLoggedUser();

$invertedStyle = $controller->getOption('invertedStyle', 1);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="<?php echo $APP_LANG; ?>" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?php echo $APP_LANG; ?>">
<!--<![endif]-->
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
	<link rel="icon" type="image/png" href="<?php echo STATIC_ASSETS_URL . 'images/icon.png'; ?>"/>
	<?php
	foreach( $this->listMetaProperties() as $property => $content ) {
		echo '
	<meta property="' . $property . '" content="' . $content . '"/>';
	}
	?>
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" type="text/css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" type="text/css"/>
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" type="text/css" media="screen"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css" type="text/css" media="screen"/>
	<?php
	
	foreach( $this->listCSSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url ) {
		echo '
	<link rel="stylesheet" href="' . $url . '" type="text/css" media="screen" />';
	}
	?>
	
	<link rel="stylesheet" href="<?php echo VENDOR_URL; ?>/sb-admin/sb-admin-6.0.2/css/styles.css" type="text/css" media="screen"/>
	<link rel="stylesheet" href="<?php echo STATIC_ASSETS_URL . 'style/base.css'; ?>" type="text/css" media="screen"/>
	<link rel="stylesheet" href="<?php echo $rendering->getCssUrl(); ?>style.css" type="text/css" media="screen"/>
	<?php
	foreach( $this->listCSSURLs() as $url ) {
		echo '
	<link rel="stylesheet" href="' . $url . '" type="text/css" media="screen" />';
	}
	?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
</head>
<body class="<?php echo $invertedStyle ? 'body-inverse' : 'body-default'; ?>">

<div id="wrapper">
	
	<!-- Sidebar -->
	<nav class="navbar <?php echo $invertedStyle ? 'navbar-inverse' : 'navbar-default'; ?> navbar-fixed-top" role="navigation">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php _u(DEFAULT_ROUTE); ?>"><?php _t($controller->getOption('main_title', 'adminpanel_title')); ?></a>
		</div>
		
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<?php
			$this->showMenu($controller->getOption('mainmenu', 'adminmenu'), 'menu-sidebar');
			?>
			
			<ul class="nav navbar-nav navbar-right navbar-user">
				<?php
				if( $user ) {
					?>
					<li class="dropdown user-dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $user; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="<?php _u(ROUTE_ADM_MYSETTINGS); ?>"><i class="fa fa-gear"></i> <?php _t(ROUTE_ADM_MYSETTINGS); ?></a></li>
							<li><a href="<?php _u(ROUTE_LOGOUT); ?>"><i class="fa fa-power-off"></i> <?php _t(ROUTE_LOGOUT); ?></a></li>
						</ul>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
	</nav>
	
	<div id="page-wrapper">
		
		<div class="container-fluid">
			
			<div class="row">
				<div class="col-lg-12">
					<?php
					// 					debug('$ContentTitle', $ContentTitle);
					if( empty($NoContentTitle) ) {
						?>
						<h1 class="page-header"><?php echo isset($ContentTitle) ? $ContentTitle : t(isset($titleRoute) ? $titleRoute : $routeName); ?>
							<small><?php _t((isset($titleRoute) ? $titleRoute : $routeName) . '_legend'); ?></small></h1>
						<?php
					}
					if( !empty($Breadcrumb) ) {
						?>
						<ol class="breadcrumb">
							<?php
							$bcLast = count($Breadcrumb) - 1;
							foreach( $Breadcrumb as $index => $page ) {
								if( $index >= $bcLast || empty($page->link) ) {
									echo '
						<li class="active">' . $page->label . '</li>';
								} else {
									echo '
						<li><a href="' . $page->link . '">' . $page->label . '</a></li>';
								}
							}
							?>
						</ol>
						<?php
					}
					$this->display('reports-bootstrap3');
					?>
				</div>
			</div>
			
			<?php
			echo $content;
			?>
		
		</div>
	</div>

</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/i18n/fr.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.5/js/jquery.tablesorter.js"></script>

<?php
foreach( $this->listJSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url ) {
	echo '
	<script type="text/javascript" src="' . $url . '"></script>';
}
?>

<script type="text/javascript" src="<?php echo VENDOR_URL; ?>/sb-admin/sb-admin-6.0.2/js/scripts.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/orpheus.js"></script>
<script src="<?php echo VENDOR_URL; ?>/orpheus/js/orpheus-confirmdialog.js"></script>
<script src="<?php echo JS_URL; ?>script.js"></script>
<script src="<?php echo $rendering->getJsUrl(); ?>orpheus.js"></script>
<script src="<?php echo $rendering->getJsUrl(); ?>script.js"></script>

<?php
foreach( $this->listJSURLs() as $url ) {
	echo '
	<script type="text/javascript" src="' . $url . '"></script>';
}
?>
</body>
</html>
