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
<div class="header">
<?php
if( !empty($USER) ) {
echo 'Bienvenue '.$USER;
}
?>
</div>

<ul class="headmenu">
<?php
if( user_access('login') ) {
echo '<li class="login'.( ($Module=='login') ? ' current' : '').'"><a href="login.html">Connexion</a></li>';
} else if( user_access('logout') ) {
echo '<li class="logout'.( ($Module=='logout') ? ' current' : '').'"><a href="logout.html">Déconnexion</a></li>';
}
if( user_access('register') ) {
echo '<li class="register'.( ($Module=='register') ? ' current' : '').'"><a href="register.html">Inscription</a></li>';
} else if( user_access('settings') ) {
echo '<li class="settings'.( ($Module=='settings') ? ' current' : '').'"><a href="settings.html">Mes paramètres</a></li>';
}
if( user_access('adm_logs') ) {
echo '<li class="adm_logs'.( ($Module=='adm_logs') ? ' current' : '').'"><a href="adm_logs.html">Journaux</a></li>';
}
if( user_access('adm_stats') ) {
echo '<li class="adm_stats'.( ($Module=='adm_stats') ? ' current' : '').'"><a href="adm_stats.html">Statistiques</a></li>';
}
if( user_access('adm_users') ) {
echo '<li class="adm_users'.( ($Module=='adm_users') ? ' current' : '').'"><a href="adm_users.html">[ADM] Utilisateurs</a></li>';
}
if( user_access('adm_anekdots') ) {
echo '<li class="adm_anekdots'.( ($Module=='adm_anekdots') ? ' current' : '').'"><a href="adm_anekdots.html">[ADM] Anekdots</a></li>';
}
if( user_access('anekdots') ) {
echo '<li class="anekdots'.( ($Module=='anekdots') ? ' current' : '').'"><a href="/">Les Dernières</a></li>';
}
if( user_access('top') ) {
echo '<li class="top'.( ($Module=='top') ? ' current' : '').'"><a href="top.html">Les meilleures</a></li>';
}
if( user_access('new') && user_access('anekdots') ) {
foreach( Anecdote::getTypes() as $type => $typeData) {
echo "
<li class=\"anekdots {$type}".( ($Action==$type) ? ' current' : '')."\"><a href=\"anekdots-{$type}.html\">{$typeData['title']}</a>&nbsp;<a href=\"new-{$type}.html\">[Poster]</a>&nbsp;<a href=\"top-{$type}.html\">[Les meilleures]</a></li>";
}
}
?>
</ul>

<div class="content">
<!-- DEBUT CONTENU DE LA PAGE -->
<?php echo $Page."\n"; ?>
<!-- / FIN CONTENU DE LA PAGE -->
</div>

<div class="debug"></div>
<div class="footer">
<div><?php
$VIEWER = getViewer();
$url = iURLEncode($_SERVER['REQUEST_URI']);
if( !empty($url) ) {
$url = '-u='.$url;
}
foreach( $VIEWERS as $vID => $vName) {
$tag = ( $vID == $VIEWER ) ? "span" : "a";
echo "
<{$tag}".(( $vID != $VIEWER ) ? " href=\"change_viewer-{$vID}{$url}.html\"" : '').">{$vName}</{$tag}>";
}
unset($url);
?></div>
&copy; 2011 <?php echo SITENAME; ?> - Tous droits réservés<br />
<a href="http://cartman34.fr/" target="_blank">Florent HAZARD</a>

<!-- Paypal Donate -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="TMUVCAH2GVK8N">
<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
<img alt="" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php
//Un module peut remplir $SCTOGEN d'un domaine entrainant la génération d'une code de sécurité
//dans la page et géré ensuite pas Javascript.
//Pour être efficace, le formulaire de classe $SCTOGEN doit contenir un input[name=s] (enfant immédiat)
if( !empty($SCTOGEN) ) {
if( empty($SCTOGENMAX) ) {
$SCTOGENMAX = 0;
}
echo '<div class="scl" id="'.$SCTOGEN.'" style="display: none; width: 0; height: 0;">'.genSecurityCode($SCTOGEN, $SCTOGENMAX).'</div>';
}
?>
</div>
</body>
</html>