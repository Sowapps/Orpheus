<?php
/**
 * @var HtmlRendering $rendering
 * @var DevLogListController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var array $logs
 */

use App\Controller\Developer\DevLogListController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');

foreach( $logs as $logID => $log ) {
	?>
	<div class="card border-default mb-3">
		<div class="card-header"><?php echo $log->label; ?></div>
		<div class="card-body">
			<div class="list-group">
				<?php
				$c = 0;
				foreach( $controller->listLogsOfFile($log->file) as $file ) {
					$filePath = (object) pathinfo($file);
					?>
					<a class="list-group-item log_file" href="<?php echo u(ROUTE_DEV_LOG_VIEW) . '?file=' . $filePath->basename; ?>">
						<i class="fa fa-fw log_nothover"></i>
						<i class="fa fa-fw fa-eye log_hover"></i>
						<?php echo t('logItemSummary', DOMAIN_LOGS, ['<strong>' . $filePath->basename . '</strong>', formatNumber(filesize($file)), dt(filemtime($file))]); ?>
					</a>
					<?php
					$c++;
				}
				if( !$c ) {
					?>
					<div class="list-group-item"><?php echo t('no_logs', DOMAIN_LOGS); ?></div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
}
?>
<style>
.log_hover {
	display: none;
}

.log_file:hover .log_hover {
	display: inline-block;
}

.log_file:hover .log_nothover {
	display: none;
}
</style>
