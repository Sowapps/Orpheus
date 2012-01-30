<!DOCTYPE html>
<html lang="fr">
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

<link rel="stylesheet" href="<?php echo CSSPATH; ?>style.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php echo CSSPATH; ?>booklet.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?php echo JSPATH; ?>jquery.js"></script>
<script type="text/javascript" src="<?php echo JSPATH; ?>jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo JSPATH; ?>jquery.easing.js"></script>
<script type="text/javascript" src="<?php echo JSPATH; ?>jquery.booklet.js"></script>
<script type="text/javascript" src="<?php echo JSPATH; ?>jquery.tools.js"></script>
<script type="text/javascript" src="<?php echo JSPATH; ?>script.js"></script>
</head>

<body class="<?php echo $Module.' '.$BODYCLASSES;?>">

</body>
</html>