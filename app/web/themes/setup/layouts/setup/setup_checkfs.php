<?php
/* @var Orpheus\Rendering\HTMLRendering $this */
use Orpheus\Rendering\HTMLRendering;

HTMLRendering::useLayout('page_skeleton');

function collapsiblePanelHTML($id, $title, $description, $panelClass, $open=0) {
	?>
	<div class="panel <?php echo $panelClass; ?>">
		<div class="panel-heading" role="tab">
			<h4 class="panel-title">
				<a role="button" class="ib wf" data-toggle="collapse" data-parent="#CheckFSAccordion" href="#<?php echo $id; ?>" aria-expanded="true" aria-controls="<?php echo $id; ?>">
					<?php echo ($panelClass===PANEL_SUCCESS ? '<i class="fa fa-fw fa-check"></i> ' : '<i class="fa fa-fw fa-times"></i> ').$title; ?>
				</a>
			</h4>
		</div>
		<div id="<?php echo $id; ?>" class="panel-collapse collapse<?php echo $open ? ' in' : ''; ?>" role="tabpanel" aria-labelledby="headingOne">
			<div class="panel-body">
				<?php echo text2HTML($description); ?>
			</div>
		</div>
	</div>
	<?php
}
?>
<form method="POST">
<div class="row">

	<div class="col-lg-8 col-lg-offset-2">

		<h1><?php _t('checkfs_title', DOMAIN_SETUP, t('app_name')); ?></h1>
		<p class="lead"><?php echo text2HTML(t('checkfs_description', DOMAIN_SETUP, array('APP_NAME'=>t('app_name')))); ?></p>
	
<!-- 		<ul class="list-group"> -->
<!-- 			<li class="list-group-item"> -->
<!-- 				<span class="badge">14</span> -->
<!-- 				Cras justo odio -->
<!-- 			</li> -->
<!-- 		</ul> -->

		<div class="form-horizontal">
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('pathToFolder', DOMAIN_SETUP, t('folder_application', DOMAIN_SETUP)); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo INSTANCEPATH; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('pathToFolder', DOMAIN_SETUP, t('folder_webaccess', DOMAIN_SETUP)); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo ACCESSPATH; ?></p>
				</div>
			</div>
		</div>

		<div class="panel-group" id="CheckFSAccordion" role="tablist" aria-multiselectable="true">
			<?php
			foreach( $folders as $folder => $fi ) {
				collapsiblePanelHTML($folder, $fi->title, $fi->description,  $fi->panel,  $fi->open);
			}
			?>
		</div>
		
		<?php
		if( $allowContinue ) {
			?>
		<p><a class="btn btn-lg btn-primary" href="<?php _u('setup_checkdb'); ?>" role="button"><?php _t('continue', DOMAIN_SETUP); ?></a></p>
			<?php
		}
		?>
		
	</div>
	
</div>
</form>
