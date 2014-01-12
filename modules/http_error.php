<?php
if( !isset($_SERVER['REDIRECT_STATUS']) ) {
	redirectToHome();
}
$CODE = $_SERVER['REDIRECT_STATUS'];
$TCODE = $CODE==404 ? $CODE : 'other';

log_report('HTTP Error : '.$CODE, SERVLOGFILENAME, 'accessing page', null);

?>

<h3>Error : <?php echo t('error_'.$TCODE.'_title', 'http_errors'); ?></h3>
<hr class="tight"/>

<?php echo t('error_'.$TCODE.'_text', 'http_errors'); ?><br />
You could return to <a href="<?php echo DEFAULTLINK; ?>">the home page</a>.