<!DOCTYPE html>
<html>
<head>
	<title><?php echo ( (!empty($MODTITLE)) ? $MODTITLE.' :: ' : '' ).SITENAME ?></title>
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

	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>bootstrap.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>bootstrap-theme.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>font-awesome.css" type="text/css" media="screen" />
<!-- 	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>bootstrap-responsive.css" type="text/css" media="screen" /> -->
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>style.css" type="text/css" media="screen" />
<?php
foreach(HTMLRendering::$cssFiles as $file) {
	echo '
	<link rel="stylesheet" type="text/css" href="'.HTMLRendering::getCSSURL().$file.'" media="screen" />';
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="/js/jquery.js"></script>
</head>
<body class="<?php echo $Module; ?>">

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<a class="navbar-brand" href="<?php echo SITEROOT; ?>"><?php echo SITENAME ?></a>
			<div class="collapse navbar-collapse">
<?php SiteUser::is_login() ? $this->showMenu('topmenu') :  $this->showMenu('topmenu_member') ; ?>
			</div>
		</div>
	</div>

<div class="container">

<?php echo $Page; ?>

</div>
	<!-- JS libraries -->
	<script type="text/javascript" src="/js/jquery-ui.js"></script>
	<script type="text/javascript" src="/js/bootstrap.js"></script>
	
	<!-- Our JS scripts -->
	<script type="text/javascript" src="/js/script.js"></script>
<?php
foreach(HTMLRendering::$jsFiles as $file) {
	echo '
	<script type="text/javascript" src="/js/'.$file.'"></script>';
}
?>

</body>
</html>