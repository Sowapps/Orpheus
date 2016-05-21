<?php
HTMLRendering::useLayout('page_skeleton');

?>
<form method="POST">
<div class="row">

	<div class="col-lg-10 col-lg-offset-1">

<div class="jumbotron">
	<h1><i class="fa fa-check-square-o"></i> <?php _t('end_title', DOMAIN_SETUP, t('app_name')); ?></h1>
	<p class="lead"><?php echo text2HTML(t('end_description', DOMAIN_SETUP, array('APP_NAME'=>t('app_name')))); ?></p>
	<p><a class="btn btn-lg btn-primary" href="<?php echo DEFAULTLINK; ?>" role="button"><i class="fa fa-home"></i> <?php _t('goToHome', DOMAIN_SETUP); ?></a></p>
</div>

	</div>
	
</div>
</form>
