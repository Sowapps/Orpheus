<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $file
 * @var object $filePathInfo
 * @var string $format
 * @var AbstractFile $fileHandler
 * @var bool $hideDuplicate
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;


$rendering->useLayout('page_skeleton');

?>

<div class="row">
	<div class="col-lg-8">
		<?php $rendering->useLayout('panel-default'); ?>
		
		<div class="form-horizontal">
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _t('file_name', DOMAIN_LOGS); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $filePathInfo->basename; ?></p>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _t('file_path', DOMAIN_LOGS); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $file; ?></p>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _t('file_format', DOMAIN_LOGS); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo $format; ?></p>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _t('file_size', DOMAIN_LOGS); ?></label>
				<div class="col-sm-9">
					<p class="form-control-static"><?php echo formatInt(filesize($file)); ?> bytes</p>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label"><?php _t('file_mtime', DOMAIN_LOGS); ?></label>
				<div class="col-sm-9">
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
		
		<div id="LogList">
			<?php
			if( $hideDuplicate ) {
				$shownLogs = [];
			}
			$lineCount = 0;
			while( ($line = $fileHandler->getNextLine()) !== false ) {
				try {
					$log = (object) json_decode($line, 1);
					if( !isset($log->id) ) {
						$log->id = 'LL' . $lineCount;
					}
					if( !isset($log->date) ) {
						$log->date = 'N/A';
					}
					if( !isset($log->action) ) {
						$log->action = 'N/A';
					}
					if( !isset($log->report) ) {
						$log->report = 'N/A';
					}
					if( !isset($log->crc32) ) {
						$log->crc32 = crc32($log->report);
					}
					if( !isset($log->trace) ) {
						$log->trace = 'There is no trace';
					}
					
					if( $hideDuplicate ) {
						if( isset($shownLogs[$log->crc32]) ) {
							$shownLogs[$log->crc32]++;
							continue;
						} else {
							$shownLogs[$log->crc32] = 1;
						}
					}
					$panelID = 'log_' . str_replace('.', '_', $log->id);
					?>
					<div class="card border-default log mb-3">
						<div class="card-header" id="<?php echo $panelID; ?>_heading">
							<h4 class="mb-0">
								<button class="btn btn-link" data-toggle="collapse" data-target="#<?php echo $panelID; ?>" aria-expanded="false" aria-controls="<?php echo $panelID; ?>">
									<?php echo str_limit(strip_tags($log->report), 200); ?>
								</button>
							</h4>
						</div>
						<div id="<?php echo $panelID; ?>" class="collapse" aria-labelledby="<?php echo $panelID; ?>_heading" data-parent="#LogList">
							<div class="card-body">
								<div class="form-horizontal">
									<div class="form-group row">
										<label class="col-sm-2 control-label"><?php _t('log_date', DOMAIN_LOGS); ?></label>
										<div class="col-sm-10">
											<p class="form-control-static"><?php echo $log->date; ?></p>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 control-label"><?php _t('log_action', DOMAIN_LOGS); ?></label>
										<div class="col-sm-10">
											<p class="form-control-static"><?php echo $log->action; ?></p>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 control-label"><?php _t('log_crc32', DOMAIN_LOGS); ?></label>
										<div class="col-sm-10">
											<p class="form-control-static"><?php echo $log->crc32; ?></p>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 control-label"><?php _t('log_report', DOMAIN_LOGS); ?></label>
										<div class="col-sm-10">
											<p class="form-control-static"><?php echo $log->report; ?></p>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-sm-2 control-label"><?php _t('log_trace', DOMAIN_LOGS); ?></label>
										<div class="col-sm-12">
											<?php is_array($log->trace) ? displayStackTrace($log->trace) : print($log->trace); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="card-footer text-right">
								<button class="btn btn-primary"
										data-confirm_title="<?php _t('removeOneConfirmTitle', DOMAIN_LOGS); ?>"
										data-confirm_message="<?php _t('removeOneConfirmMessage', DOMAIN_LOGS); ?>"
										data-confirm_submit_name="submitRemoveByCRC"
										data-confirm_submit_value="<?php echo $log->crc32; ?>">
									<?php _t('removeOneButton', DOMAIN_LOGS); ?>
								</button>
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
		<?php $rendering->startNewBlock('title'); ?>
		<?php echo t('file_logs', DOMAIN_LOGS); ?>
		<a href="#lastLog" class="pull-right">
			<i class="fa fa-arrow-circle-o-down"></i> <?php echo t('goToLastOneButton', DOMAIN_LOGS); ?>
		</a>
		<?php $rendering->startNewBlock('footer'); ?>
		<div class="panel-footer text-right">
			<?php
			if( $fileHandler->isCompressible() ) {
				?>
				<button class="btn btn-primary" data-confirm_title="<?php echo t('archiveFileConfirmTitle', DOMAIN_LOGS); ?>"
						data-confirm_message="<?php echo t('archiveFileConfirmMessage', DOMAIN_LOGS); ?>" data-confirm_submit_name="submitArchive">
					<?php echo t('archiveFileButton', DOMAIN_LOGS); ?>
				</button>
				<?php
			}
			?>
			<button class="btn btn-danger" data-confirm_title="<?php echo t('removeAllConfirmTitle', DOMAIN_LOGS); ?>"
					data-confirm_message="<?php echo t('removeAllConfirmMessage', DOMAIN_LOGS); ?>" data-confirm_submit_name="submitRemoveAll">
				<?php echo t('removeAllButton', DOMAIN_LOGS); ?>
			</button>
		</div>
		
		<?php $rendering->endCurrentLayout(); ?>
	</div>
</div>

<script>
$(function () {
	var lastLog = $('.panel.log').last();
	if( lastLog.length ) {
		lastLog.attr('id', 'lastLog');
	}
});
</script>



