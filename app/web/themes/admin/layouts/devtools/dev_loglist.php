<?php
use Orpheus\Rendering\HTMLRendering;

/* @var HTMLRendering $this */
/* @var DevLogListController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */

HTMLRendering::useLayout('page_skeleton');

?>
<div class="panel-group">
	<?php

	foreach( $logs as $logID => $log ) {
		?>
	<div class="panel panel-default">
		<div class="panel-heading"><?php echo $log->label; ?></div>
		<div class="list-group">
		<?php
// 		try {
// 		if( is_readable(LOGSPATH.$log->file) ) {
// 			debug('$Controller->listLogsOfFile()', $Controller->listLogsOfFile($log->file));
		$c = 0;
		foreach( $Controller->listLogsOfFile($log->file) as $file ) {
			$filePath = (object) pathinfo($file);
			?>
			<a class="list-group-item log_file" href="<?php echo u(ROUTE_DEV_LOG_VIEW).'?file='.$filePath->basename; ?>">
				<i class="fa fa-fw log_nothover"></i>
				<i class="fa fa-fw fa-eye log_hover"></i>
				<strong><?php echo $filePath->basename; ?></strong> of size <?php echo formatInt(filesize($file)); ?> bytes and modified at <?php echo dt(filemtime($file)); ?>.
			</a>
			<?php
			$c++;
		}
		if( !$c ) {
			?>
			<div class="list-group-item"><?php _t('no_logs', DOMAIN_LOGS); ?></div>
			<?php
		}

// 			$logLines = file($logData['file'], FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
// 		} catch(Exception $e) {
// // 			echo t("$e");
// 	// 		echo t($e->__toString());
// 		}
		?>
		</div>
	</div>
	<?php
	}
?>
</div>
<style>
<!--
/*
.log_file a {
	color: inherit;
	text-decoration: none;
	display: block;
}
.log_hover {
	display: none;
}
.log_file a:hover .log_hover {
	display: inline-block;
}
.log_file a:hover .log_nothover {
	display: none;
}
*/
.log_hover {
	display: none;
}
.log_file:hover .log_hover {
	display: inline-block;
}
.log_file:hover .log_nothover {
	display: none;
}
-->
</style>
