<?php
use Orpheus\Rendering\HTMLRendering;

/* @var string $CONTROLLER_OUTPUT */
/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */

global $APP_LANG;

?><!DOCTYPE html>
<html lang="<?php echo $APP_LANG; ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo !empty($PageTitle) ? $PageTitle : SITENAME; ?></title>
	<meta name="Description" content=""/>
	<meta name="Author" content="<?php echo AUTHORNAME; ?>"/>
	<meta name="application-name" content="<?php echo SITENAME;?>" />
	<meta name="msapplication-starturl" content="<?php echo DEFAULTLINK; ?>" />
	<meta name="Keywords" content="projet"/>
	<meta name="Robots" content="Index, Follow"/>
	<meta name="revisit-after" content="16 days"/>
<?php
foreach($this->listMetaProperties() as $property => $content) {
	echo '
	<meta property="'.$property.'" content="'.$content.'"/>';
}
?>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap-theme.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" type="text/css" media="screen" />
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2-bootstrap.min.css" type="text/css" media="screen" />
	
<?php
foreach($this->listCSSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url) {
	echo '
	<link rel="stylesheet" href="'.$url.'" type="text/css" media="screen" />';
}
?>
	
	<link rel="stylesheet" href="<?php echo STATIC_URL; ?>style/base.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?php echo HTMLRendering::getCSSURL(); ?>style.css" type="text/css" media="screen" />
<?php
foreach($this->listCSSURLs() as $url) {
	echo '
	<link rel="stylesheet" type="text/css" href="'.$url.'" media="screen" />';
}
?>
	
	<!-- External JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo SITEROOT; ?>"><?php echo SITENAME ?></a>
		</div>
		<div class="collapse navbar-collapse">
<?php
$this->showMenu('topmenu');
if( !empty($TOPBAR_CONTENTS) ) { echo $TOPBAR_CONTENTS; }
?>

		</div>
	</div>
</div>

<div class="container">

<?php
echo $Content;
// If report was not be reported
$this->display('reports-bootstrap3');
?>

</div>
	<!-- JS libraries -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2_locale_fr.min.js"></script>
	
<?php
foreach($this->listJSURLs(HTMLRendering::LINK_TYPE_PLUGIN) as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
?>

	<!-- Our JS scripts -->
	<script type="text/javascript" src="/js/orpheus.js"></script>
	<script type="text/javascript" src="/js/script.js"></script>
<?php
foreach($this->listJSURLs() as $url) {
	echo '
	<script type="text/javascript" src="'.$url.'"></script>';
}
?>

</body>
</html>