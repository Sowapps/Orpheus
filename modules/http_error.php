<?php
if( !isset($_SERVER['REDIRECT_STATUS']) ) {
	redirectToHome();
}
if( !isset($CODE) ) {
	$CODE	= $_SERVER['REDIRECT_STATUS'];
}
$TCODE	= $CODE==404 ? $CODE : 'other';

log_report('HTTP Error : '.$CODE.' for URI "'.$_SERVER['REQUEST_URI'].'" 
[ IP: '.$_SERVER['REMOTE_ADDR'].'; agent: '.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A').'; referer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A').' ]', SERVLOGFILENAME, 'HTTP Error', null);

?>

<h3>Error : <?php echo t('error_'.$TCODE.'_title', 'http_errors'); ?></h3>
<hr class="tight"/>

<?php echo t('error_'.$TCODE.'_text', 'http_errors'); ?><br />
You could return to <a href="<?php echo DEFAULTLINK; ?>">the home page</a>.
