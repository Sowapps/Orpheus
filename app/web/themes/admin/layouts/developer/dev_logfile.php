<?php
/**
 * @var HTMLRendering $rendering
 * @var HTTPRequest $request
 * @var HTTPRoute $route
 * @var HTTPController $controller
 */

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;

/* @var string $file */
/* @var string $filePathInfo */
/* @var string $format */
/* @var AbstractFile $fileHandler */

$rendering->useLayout('page_skeleton');

// $filePathInfo = (object) pathinfo($file);
?>

<div class="row">
	<div class="col-lg-8">
		<?php $rendering->useLayout('panel-default'); ?>
		
		<div class="form-horizontal">
			<div class="form-group">
				<label class="col-sm-2 control-label"><?php _t('file_name', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo $filePathInfo->basename; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?php _t('file_path', DOMAIN_LOGS); ?></label>
					<div class="col-sm-10">
						<p class="form-control-static"><?php echo $file; ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?php _t('file_format', DOMAIN_LOGS); ?></label>
					<div class="col-sm-10">
						<p class="form-control-static"><?php echo $format; ?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label"><?php _t('file_size', DOMAIN_LOGS); ?></label>
					<div class="col-sm-10">
						<p class="form-control-static"><?php echo formatInt(filesize($file)); ?> bytes</p>
					</div>
				</div>
			<div class="form-group">
				<label class="col-sm-2 control-label"><?php _t('file_mtime', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo dt(filemtime($file)); ?></p>
				</div>
			</div>
		</div>
		
		<?php $rendering->endCurrentLayout([
			'title' => t('file_informations', DOMAIN_LOGS),
		]); ?>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<?php $rendering->useLayout('panel-default'); ?>
		
		<div class="panel-group" id="LogList" role="tablist" aria-multiselectable="true">
			<?php
			if( $hideDuplicate ) {
				$shownLogs = [];
			}
			$lineCount = 0;
			while( ($line = $fileHandler->getNextLine()) !== false ) {
				try {
					$log = (object) json_decode($line, 1);
					if( !isset($log->id) )		{ $log->id		= 'LL'.$lineCount; }
					if( !isset($log->date) )	{ $log->date	= 'N/A'; }
					if( !isset($log->action) )	{ $log->action	= 'N/A'; }
					if( !isset($log->report) )	{ $log->report	= 'N/A'; }
					if( !isset($log->crc32) )	{ $log->crc32	= crc32($log->report); }
					if( !isset($log->trace) )	{ $log->trace	= 'There is no trace'; }
					
					if( $hideDuplicate ) {
						if( isset($shownLogs[$log->crc32]) ) {
							$shownLogs[$log->crc32]++;
							continue;
						} else {
							$shownLogs[$log->crc32] = 1;
						}
					}
					$panelID = 'log_'.str_replace('.', '_', $log->id);
					?>
				<div class="panel panel-default log">
					<div class="panel-heading" role="tab" id="<?php echo $panelID; ?>_heading">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#LogList" href="#<?php echo $panelID; ?>" aria-expanded="false" aria-controls="<?php echo $panelID; ?>">
								<?php echo str_limit(strip_tags($log->report), 200); ?>
							</a>
						</h4>
					</div>
					<div id="<?php echo $panelID; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?php echo $panelID; ?>_heading">
						<div class="panel-body">
							<div class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _t('log_date', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->date; ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _t('log_action', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->action; ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _t('log_crc32', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->crc32; ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _t('log_report', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->report; ?></p>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _t('log_trace', DOMAIN_LOGS); ?></label>
									<div class="col-sm-12">
										<?php is_array($log->trace) ? displayStackTrace($log->trace) : print($log->trace); ?>
<!-- 										<p class="form-control-static"></p> -->
									</div>
								</div>
							</div>
						</div>
						<div class="panel-footer text-right">
							<button class="btn btn-primary"
								data-confirm_title="<?php _t('removeOneConfirmTitle', DOMAIN_LOGS); ?>"
								data-confirm_message="<?php _t('removeOneConfirmMessage', DOMAIN_LOGS); ?>"
								data-confirm_submit_name="submitRemoveByCRC"
								data-confirm_submit_value="<?php echo $log->crc32; ?>"
								><?php _t('removeOneButton', DOMAIN_LOGS); ?></button>
						</div>
					</div>
				</div>
					<?php
					$lineCount++;
				} catch( Exception $e ) {
					echo $e;
				}
			}
			unset($line, $log, $panelID);
			$fileHandler->ensureClosed();
			?>
		</div>
		
		<?php $rendering->endCurrentLayout(array(
			'title'  => t('file_logs', DOMAIN_LOGS) . ' <a href="#lastLog" class="pull-right">
				<i class="fa fa-arrow-circle-o-down"></i> ' . t('goToLastOneButton', DOMAIN_LOGS) . '
			</a>',
			'footer' => '
				<div class="panel-footer text-right">' .
				($fileHandler->isCompressible() ?
					'
					<button class="btn btn-primary"
						data-confirm_title="' . t('archiveFileConfirmTitle', DOMAIN_LOGS) . '"
						data-confirm_message="' . t('archiveFileConfirmMessage', DOMAIN_LOGS) . '"
						data-confirm_submit_name="submitArchive"
						>'.t('archiveFileButton', DOMAIN_LOGS).'</button>' : '').
					'
					<button class="btn btn-danger"
						data-confirm_title="'.t('removeAllConfirmTitle', DOMAIN_LOGS).'"
						data-confirm_message="'.t('removeAllConfirmMessage', DOMAIN_LOGS).'"
						data-confirm_submit_name="submitRemoveAll"
						>'.t('removeAllButton', DOMAIN_LOGS).'</button>
				</div>'
		)); ?>
	</div>
</div>

<script>
$(function() {
	var lastLog = $(".panel.log").last();
	if( lastLog.length ) {
		lastLog.attr("id", "lastLog");
	}
});
</script>



