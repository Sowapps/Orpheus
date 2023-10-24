<?php

$reportStream ??= 'global';

foreach( getReports($reportStream) as $type => $typeReports ) {
	$type = $type === 'error' ? 'danger' : $type;
	foreach( $typeReports as $report ) {
		$reportType = $type;
		if( $reportType === 'danger' && !$report['severity'] ) {
			$reportType = 'warning';
		}
		?>
		<div class="alert alert-<?php echo $reportType; ?> <?php echo $report['domain']; ?> alert-dismissible fade show">
			<?php echo $report['report']; ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
		<?php
	}
}

$reportStream = null;
