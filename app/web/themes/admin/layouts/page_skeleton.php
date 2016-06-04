<?php
/* @var string $CONTROLLER_OUTPUT */
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var User $USER */

/* Parameters
 * 
 * $PageTitle
 * $NoContentTitle
 * $ContentTitle
 */


$routeName = $Controller->getRouteName();
/*
	<title><?php echo ( !empty($MODTITLE) ? $MODTITLE.' :: ' : '' ).'ADM '.SITENAME ?></title>
*/
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="<?php echo LANGBASE; ?>" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="<?php echo LANGBASE; ?>">
<!--<![endif]-->
<head>
	<title><?php echo !empty($PageTitle) ? $PageTitle : SITENAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHORNAME; ?>"/>
	<meta name="application-name" content="<?php echo SITENAME;?>" />
	<meta name="msapplication-starturl" content="<?php echo DEFAULTLINK; ?>" />
	<meta name="Keywords" content="projet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
	<link rel="icon" type="image/png" href="<?php echo STATIC_URL.'images/icon.png'; ?>" />
<?php
foreach(HTMLRendering::$metaprop as $property => $content) {
	echo '
	<meta property="'.$property.'" content="'.$content.'"/>';
}
?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" type="text/css" />
	<link rel="stylesheet" href="//shared.sowapps.com/select2/select2-3.5.2/select2.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="//shared.sowapps.com/select2-bootstrap-css/select2-3.5.2/select2-bootstrap.css" type="text/css" media="screen" />
	
<!--	 <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css"> -->
<?php
foreach(HTMLRendering::listCSSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url) {
	echo '
	<link rel="stylesheet" href="'.$url.'" type="text/css" media="screen" />';
}
?>
	
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>sb-admin.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo STATIC_URL.'style/base.css'; ?>" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>style.css" type="text/css" media="screen" />
<?php
foreach(HTMLRendering::listCSSURLs() as $url) {
	echo '
	<link rel="stylesheet" type="text/css" href="'.$url.'" media="screen" />';
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
</head>
<body>

<div id="wrapper">

	<!-- Sidebar -->
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php _u(DEFAULTROUTE); ?>"><?php _t('adminpanel_title'); ?></a>
		</div>
	
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<?php
			$this->showMenu($Controller->getOption('mainmenu', 'adminmenu'), 'menu-sidebar');
			?>
			<ul class="nav navbar-nav navbar-right navbar-user">
			<?php
			if( User::isLogged() ) {
				?>
				<li class="dropdown user-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $USER; ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="<?php _u(ROUTE_ADM_MYSETTINGS); ?>"><i class="fa fa-gear"></i> Paramètres</a></li>
						<li><a href="<?php _u(ROUTE_LOGOUT); ?>"><i class="fa fa-power-off"></i> Déconnexion</a></li>
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
					<h1 class="page-header"><?php echo isset($ContentTitle) ? $ContentTitle : t(isset($titleRoute) ? $titleRoute : $routeName); ?> <small><?php _t((isset($titleRoute) ? $titleRoute : $routeName).'_legend'); ?></small></h1>
					<?php
					}
					if( !empty($Breadcrumb) ) {
						?>
					<ol class="breadcrumb">
						<?php
						$bcLast	= count($Breadcrumb)-1;
						foreach( $Breadcrumb as $index => $page ) {
							if( $index >= $bcLast || empty($page->link) ) {
								echo '
						<li class="active">'.$page->label.'</li>';
							} else {
								echo '
						<li><a href="'.$page->link.'">'.$page->label.'</a></li>';
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
			echo $CONTROLLER_OUTPUT;
			echo $Content;
			?>
	
		</div>
	</div>

</div>

	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	
	<?php /*
	<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
	<script src="<?php echo HTMLRendering::getThemeURL(); ?>js/morris/chart-data-morris.js"></script>
	<script src="//shared.sowapps.com/morris.js/morris.js-0.5.1/morris.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.0/moment-timezone.min.js"></script>
	*/?>
	<script src="//shared.sowapps.com/tablesorter/tablesorter-2.0.5/jquery.tablesorter.min.js"></script>
	<script src="//shared.sowapps.com/select2/select2-3.5.2/select2.min.js"></script>
<!-- 	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script> -->
<!-- 	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.0/moment-timezone-with-data.min.js"></script> -->
	
<?php
foreach(HTMLRendering::listJSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
?>
	
	<script src="<?php echo JSURL; ?>orpheus.js"></script>
	<script src="<?php echo JSURL; ?>script.js"></script>
	<?php /*
	<script src="<?php echo JSURL; ?>form.js"></script>
	*/ ?>
	<script src="<?php echo HTMLRendering::getJSURL(); ?>orpheus.js"></script>
	<script src="<?php echo HTMLRendering::getJSURL(); ?>script.js"></script>
	
<?php
foreach(HTMLRendering::listJSURLs() as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
?>
</body>
</html>