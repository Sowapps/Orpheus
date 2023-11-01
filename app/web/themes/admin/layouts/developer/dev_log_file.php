<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var string $logPath
 * @var string $filePathInfo
 * @var string $format
 * @var AbstractFile $logFile
 * @var bool $hideDuplicate
 */

use Orpheus\File\AbstractFile;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');

?>

<div class="row">
	<div class="col-lg-8">
		<?php $rendering->useLayout('component/panel'); ?>
		
		<div class="form-horizontal">
			<div class="mb-3 row">
				<label class="col-sm-2 control-label"><?php echo t('file_name', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo basename($logPath); ?></p>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-2 control-label"><?php echo t('file_path', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo $logPath; ?></p>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-2 control-label"><?php echo t('file_format', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo $format; ?></p>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-2 control-label"><?php echo t('file_size', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo formatNumber(filesize($logPath)); ?> bytes</p>
				</div>
			</div>
			<div class="mb-3 row">
				<label class="col-sm-2 control-label"><?php echo t('file_mtime', DOMAIN_LOGS); ?></label>
				<div class="col-sm-10">
					<p class="form-control-static"><?php echo dt(filemtime($logPath)); ?></p>
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
		<?php $rendering->useLayout('component/panel'); ?>
		
		<div class="accordion" id="LogList" role="tablist" aria-multiselectable="true">
			<?php
			if( $hideDuplicate ) {
				$shownLogs = [];
			}
			$lineCount = 0;
			while( ($line = $logFile->getNextLine()) !== false ) {
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
					$panelId = 'log_' . str_replace('.', '_', $log->id);
					?>
					<div class="accordion-item log">
						<h4 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $panelId; ?>"
									aria-expanded="false" aria-controls="<?php echo $panelId; ?>">
								<?php echo str_limit(strip_tags($log->report), 200); ?>
							</button>
						</h4>
						<div id="<?php echo $panelId; ?>" class="accordion-collapse collapse" data-bs-parent="#LogList">
							<div class="accordion-body">
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_date', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->date; ?></p>
									</div>
								</div>
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_action', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->action; ?></p>
									</div>
								</div>
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_crc32', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->crc32; ?></p>
									</div>
								</div>
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_report', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo $log->report; ?></p>
									</div>
								</div>
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_from', DOMAIN_LOGS); ?></label>
									<div class="col-sm-10">
										<p class="form-control-static"><?php echo !empty($log->file) ? sprintf('%s:%d', $log->file, $log->line) : 'Unknown'; ?></p>
									</div>
								</div>
								<div class="mb-3 row">
									<label class="col-sm-2 control-label"><?php echo t('log_trace', DOMAIN_LOGS); ?></label>
									<div class="col-sm-12">
										<?php is_array($log->trace) ? displayStackTraceAsHtml($log->trace) : print($log->trace); ?>
										<!-- 										<p class="form-control-static"></p> -->
									</div>
								</div>
							</div>
							<div class="p-3 text-end">
								<button class="btn btn-primary"
										data-toggle="confirm"
										data-confirm-title="<?php echo t('removeOneConfirmTitle', DOMAIN_LOGS); ?>"
										data-confirm-message="<?php echo t('removeOneConfirmMessage', DOMAIN_LOGS); ?>"
										data-confirm-submit-name="submitRemoveByCRC"
										data-confirm-submit-value="<?php echo $log->crc32; ?>"
								><?php echo t('removeOneButton', DOMAIN_LOGS); ?></button>
							</div>
						</div>
					</div>
					<?php
					$lineCount++;
				} catch( Exception $e ) {
					echo $e;
				}
			}
			unset($line, $log, $panelId);
			$logFile->ensureClosed();
			?>
		</div>
		
		<?php
		$rendering->startNewBlock('title');
		echo t('file_logs', DOMAIN_LOGS);
		$rendering->startNewBlock('footer');
		if( $logFile->isCompressible() ) {
			?>
			<button class="btn btn-primary" data-toggle="confirm"
					data-confirm-title="<?php echo t('archiveFileConfirmTitle', DOMAIN_LOGS); ?>"
					data-confirm-message="<?php echo t('archiveFileConfirmMessage', DOMAIN_LOGS); ?>"
					data-confirm-submit-name="submitArchive"
			><?php echo t('archiveFileButton', DOMAIN_LOGS); ?></button>
			<?php
		}
		?>
		<button class="btn btn-danger" data-toggle="confirm"
				data-confirm-title="<?php echo t('removeAllConfirmTitle', DOMAIN_LOGS); ?>"
				data-confirm-message="<?php echo t('removeAllConfirmMessage', DOMAIN_LOGS); ?>"
				data-confirm-submit-name="submitRemoveAll"
		><?php echo t('removeAllButton', DOMAIN_LOGS); ?></button>
		
		<?php $rendering->endCurrentLayout(); ?>
	</div>
</div>

<script>
	$(function () {
		const lastLog = $(".panel.log").last();
		if( lastLog.length ) {
			lastLog.attr("id", "lastLog");
		}
	});
</script>



