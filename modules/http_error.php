<?php
if( !isset($_SERVER['REDIRECT_STATUS']) ) {
	redirectToHome();
}
$CODE = $_SERVER['REDIRECT_STATUS'];
$TCODE = $CODE==404 ? $CODE : 'other';

log_error('HTTP Error : '.$CODE, SERVLOGFILENAME, 'accessing page', null);

?>

<h3>Erreur : <?php echo t('error_'.$TCODE.'_title', 'http_errors'); ?></h3>
<hr class="tight"/>

<?php echo t('error_'.$TCODE.'_text', 'http_errors'); ?><br />
Vous pouvez nous <a href="contact.html">contacter</a> pour nous transmettre cette erreur ou retourner à  <a href="<?php echo DEFAULTLINK; ?>">la page d'accueil</a>.