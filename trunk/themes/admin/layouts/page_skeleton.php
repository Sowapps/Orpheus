<!DOCTYPE html>
<html lang="<?php echo LANGBASE; ?>">
<head>
	<title><?php echo ( !empty($MODTITLE) ? $MODTITLE.' :: ' : '' ).'ADM '.SITENAME ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHORNAME; ?>"/>
	<meta name="application-name" content="<?php echo SITENAME;?>" />
	<meta name="msapplication-starturl" content="<?php echo DEFAULTLINK; ?>" />
	<meta name="Keywords" content="projet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
<?php
foreach(HTMLRendering::$metaprop as $property => $content) {
	echo '
	<meta property="'.$property.'" content="'.$content.'"/>';
}
?>

	<link rel="stylesheet" href="//shared.sowapps.com/bootstrap/bootstrap-3.3.2/css/bootstrap.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="//shared.sowapps.com/font-awesome/font-awesome-4.3.0/css/font-awesome.min.css" type="text/css" media="screen">
	<link rel="stylesheet" href="//shared.sowapps.com/select2/select2-3.5.2/select2.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="//shared.sowapps.com/select2-bootstrap-css/select2-3.5.2/select2-bootstrap.css" type="text/css" media="screen" />
	
<!--	 <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css"> -->
	
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>sb-admin.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo THEMESURL.'default/css/base.css'; ?>" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>style.css" type="text/css" media="screen" />
<?php
/*
if( !empty($CSS_FILES) ) {
	foreach($CSS_FILES as $file) {
		?>
	
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL().$file; ?>" type="text/css" media="screen" />
	<?php
	}
}
*/
foreach(HTMLRendering::$cssURLs as $url) {
	echo '
	<link rel="stylesheet" type="text/css" href="'.$url.'" media="screen" />';
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="//shared.sowapps.com/jquery/jquery-1.11.2/jquery-1.11.2.min.js"></script>
</head>
<body class="<?php echo $Module; ?>">

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
			<a class="navbar-brand" href="/"><?php _t('adminpanel_title'); ?></a>
		</div>
	
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<?php
			$this->showMenu('adminmenu', 'menu-sidebar');
			/*
			<ul class="nav navbar-nav side-nav">
				<li class="active"><a href="index.html"><i class="fa fa-dashboard"></i> Dashboard</a></li>
				<li><a href="charts.html"><i class="fa fa-bar-chart-o"></i> Charts</a></li>
				<li><a href="tables.html"><i class="fa fa-table"></i> Tables</a></li>
				<li><a href="forms.html"><i class="fa fa-edit"></i> Forms</a></li>
				<li><a href="typography.html"><i class="fa fa-font"></i> Typography</a></li>
				<li><a href="bootstrap-elements.html"><i class="fa fa-desktop"></i> Bootstrap Elements</a></li>
				<li><a href="bootstrap-grid.html"><i class="fa fa-wrench"></i> Bootstrap Grid</a></li>
				<li><a href="blank-page.html"><i class="fa fa-file"></i> Blank Page</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-square-o-down"></i> Dropdown <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#">Dropdown Item</a></li>
						<li><a href="#">Another Item</a></li>
						<li><a href="#">Third Item</a></li>
						<li><a href="#">Last Item</a></li>
					</ul>
				</li>
			</ul>
			*/
			?>
			<ul class="nav navbar-nav navbar-right navbar-user">
			<?php /*
				<li class="dropdown messages-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> Messages <span class="badge">7</span> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li class="dropdown-header">7 New Messages</li>
						<li class="message-preview">
							<a href="#">
							<span class="avatar"><img src="http://placehold.it/50x50"></span>
							<span class="name">John Smith:</span>
							<span class="message">Hey there, I wanted to ask you something...</span>
							<span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
							</a>
						</li>
						<li class="divider"></li>
						<li class="message-preview">
							<a href="#">
							<span class="avatar"><img src="http://placehold.it/50x50"></span>
							<span class="name">John Smith:</span>
							<span class="message">Hey there, I wanted to ask you something...</span>
							<span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
							</a>
						</li>
						<li class="divider"></li>
						<li class="message-preview">
							<a href="#">
							<span class="avatar"><img src="http://placehold.it/50x50"></span>
							<span class="name">John Smith:</span>
							<span class="message">Hey there, I wanted to ask you something...</span>
							<span class="time"><i class="fa fa-clock-o"></i> 4:34 PM</span>
							</a>
						</li>
						<li class="divider"></li>
						<li><a href="#">View Inbox <span class="badge">7</span></a></li>
					</ul>
				</li>
				<li class="dropdown alerts-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> Alerts <span class="badge">3</span> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#">Default <span class="label label-default">Default</span></a></li>
						<li><a href="#">Primary <span class="label label-primary">Primary</span></a></li>
						<li><a href="#">Success <span class="label label-success">Success</span></a></li>
						<li><a href="#">Info <span class="label label-info">Info</span></a></li>
						<li><a href="#">Warning <span class="label label-warning">Warning</span></a></li>
						<li><a href="#">Danger <span class="label label-danger">Danger</span></a></li>
						<li class="divider"></li>
						<li><a href="#">View All</a></li>
					</ul>
				</li>
			*/
			if( $USER_CLASS::isLogged() ) {
				?>
				<li class="dropdown user-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $USER; ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
					<?php /*
						<li><a href="#"><i class="fa fa-user"></i> Profile</a></li>
						<li><a href="#"><i class="fa fa-envelope"></i> Inbox <span class="badge">7</span></a></li>
						<li><a href="#"><i class="fa fa-gear"></i> Settings</a></li>
						<li class="divider"></li>
					*/ ?>
						<li><a href="settings.html"><i class="fa fa-gear"></i> Settings</a></li>
						<li><a href="logout.html"><i class="fa fa-power-off"></i> Log Out</a></li>
					</ul>
				</li>
			<?php
			}
			?>
			</ul>
		</div>
	</nav>

	<div id="page-wrapper">

<div class="row">
	<div class="col-lg-12">
		<?php
		if( empty($NO_MODULE_TITLE) ) {
			?>
		<h1><?php echo isset($ModuleTitle) ? $ModuleTitle : t($Module); ?> <small><?php _t($Module.'_legend'); ?></small></h1>
		<?php
		}
		/*
		<ol class="breadcrumb">
			<li><a href="index.html"><i class="fa fa-dashboard"></i> Dashboard</a></li>
			<li class="active"><i class="fa fa-table"></i> Tables</li>
		</ol>
		*/
		foreach( getReports() as $type => $typeReports ) {
			$type = ($type == 'error') ? 'danger' : $type;
			foreach( $typeReports as $report ) {
				echo '
		<div class="alert alert-'.$type.' '.$report['d'].' alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			'.$report['r'].'
		</div>';
			}
		}
		?>
	</div>
</div>

<?php echo $Page; ?>
	
	</div>

</div>

	<script type="text/javascript" src="//shared.sowapps.com/jquery-ui/jquery-ui-1.11.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="//shared.sowapps.com/bootstrap/bootstrap-3.3.2/js/bootstrap.min.js"></script>
	
	<?php /*
	<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>
	<script src="<?php echo HTMLRendering::getThemeURL(); ?>js/morris/chart-data-morris.js"></script>
	<script src="//shared.sowapps.com/morris.js/morris.js-0.5.1/morris.min.js"></script>
	*/?>
	<script src="//shared.sowapps.com/tablesorter/tablesorter-2.0.5/jquery.tablesorter.min.js"></script>
	
	<script src="<?php echo JSURL; ?>orpheus.js"></script>
	<script src="<?php echo JSURL; ?>script.js"></script>
	<script src="<?php echo JSURL; ?>form.js"></script>
	<script src="<?php echo HTMLRendering::getThemeURL(); ?>js/orpheus.js"></script>
	<script src="<?php echo HTMLRendering::getThemeURL(); ?>js/script.js"></script>
	
<?php
foreach(HTMLRendering::$jsURLs as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
?>
</body>
</html>