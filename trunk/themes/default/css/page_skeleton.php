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

	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>pepper-grinder/jquery-ui.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSPath(); ?>style.css" type="text/css" media="screen" />
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
</head>
<body class="<?php echo $Module; ?>">

	<div class="header">
		<div class="headercontent">
			<a class="logo" href="<?php echo DEFAULTLINK; ?>" <?php
			if( !empty($_SESSION['LICENSE']) ) {
				try {
					$license = License::load($_SESSION['LICENSE']);
					echo "style=\"background-image:url('{$license->getLogoUrl()}');\"";
				} catch( UserException $e ) {
					unset($_SESSION['LICENSE']);
				}
			}
			?>></a><?php
			if( !empty($HEADERINFOS) ) {
				?>
			<div class="headerinfos"><?php echo $HEADERINFOS; ?></div><?php
			}
			?>
		</div>
	</div>
	
	<div class="content">

		<?php echo $MENUS['leftmenu']; ?>

		<div class="page <?php echo $Module; ?>">
		<div class="pageContents">
			<?php echo $Page; ?>
		</div>
		</div>
	</div>
	
	<div class="footer">
		<ul class="menu">
			<li>Copyright 2012 - 2013  Courtage &amp; Co</li>
			<li>/ <a href="http://cartman34.fr/" title="Aller au site web du développeur">Développé par Florent HAZARD</a></li>
		</ul>
	</div>
	
	<!-- Our JS scripts -->
	<script type="text/javascript" src="js/script.js"></script>
<?php
if( !empty($JSSCRIPTS) ) {
	foreach($JSSCRIPTS as $file) {
		echo "
	<script type=\"text/javascript\" src=\"{$file}\"></script>";
	}
}
?>

</body>
</html>