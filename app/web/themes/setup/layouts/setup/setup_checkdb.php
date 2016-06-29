<?php
/* @var Orpheus\Rendering\HTMLRendering $this */
use Orpheus\Rendering\HTMLRendering;

HTMLRendering::useLayout('page_skeleton');

/*
function collapsiblePanelHTML($id, $title, $description, $panelClass='panel-default', $open=0) {
	?>
	<div class="panel <?php echo $panelClass; ?>">
		<div class="panel-heading" role="tab">
			<h4 class="panel-title">
				<a role="button" class="ib wf" data-toggle="collapse" data-parent="#CheckFSAccordion" href="#<?php echo $id; ?>" aria-expanded="true" aria-controls="<?php echo $id; ?>">
					<?php echo $title; ?>
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
*/

// $DB_SETTINGS = null;


?>
<form method="POST">
<div class="row">

	<div class="col-lg-8 col-lg-offset-2">

		<h1><?php _t('checkdb_title', DOMAIN_SETUP, t('app_name')); ?></h1>
		<p class="lead"><?php echo text2HTML(t('checkdb_description', DOMAIN_SETUP, array('APP_NAME'=>t('app_name')))); ?></p>
		
		<div class="form-horizontal">
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('db_host', DOMAIN_SETUP); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $DB_SETTINGS->host; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('db_driver', DOMAIN_SETUP); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $DB_SETTINGS->driver; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('db_database', DOMAIN_SETUP); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $DB_SETTINGS->dbname; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('db_user', DOMAIN_SETUP); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $DB_SETTINGS->user; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><?php _t('db_password', DOMAIN_SETUP); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static">**********</p>
				</div>
			</div>
		</div>
		<?php
		unset($DB_SETTINGS);
		
		$this->display('reports-bootstrap3');
// 		$this->display('reports-bootstrap3', array('reportStream'=>'checkdb'));
// 		displayReportsHTML('checkdb');
		
		if( $allowContinue ) {
			?>
		<p><a class="btn btn-lg btn-primary" href="<?php _u('setup_installdb'); ?>" role="button"><?php _t('continue', DOMAIN_SETUP); ?></a></p>
			<?php
		}
		?>
		
	</div>
	
</div>
</form>
