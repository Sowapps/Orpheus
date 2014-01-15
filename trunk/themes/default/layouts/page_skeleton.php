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
if( !empty($METAPROP) ) {
	foreach($METAPROP as $property => $content) {
		echo "
	<meta property=\"{$property}\" content=\"{$content}\"/>";
	}
}
?>

	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>bootstrap.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>bootstrap-theme.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>font-awesome.css" type="text/css" media="screen" />
<!-- 	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>bootstrap-responsive.css" type="text/css" media="screen" /> -->
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>style.css" type="text/css" media="screen" />
<?php
if( !empty($CSS_FILES) ) {
	foreach($CSS_FILES as $file) {
		?>
		
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath().$file; ?>" type="text/css" media="screen" />
	<?php
	}
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="js/jquery.js"></script>
</head>
<body class="<?php echo $Module; ?>">

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<a class="navbar-brand" href="<?php echo SITEROOT; ?>"><?php echo SITENAME ?></a>
			<div class="collapse navbar-collapse">
<?php $this->showMenu('topmenu'); ?>
			</div>
		</div>
	</div>

<div class="container">

<?php echo $Page; ?>

</div>
	<!-- JS libraries -->
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	
	<!-- Our JS scripts -->
	<script type="text/javascript" src="js/script.js"></script>
<?php
if( !empty($JS_FILES) ) {
	foreach($JS_FILES as $file) {
		echo "
	<script type=\"text/javascript\" src=\"{$file}\"></script>";
	}
}
?>

</body>
</html>