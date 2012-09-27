<?php
$logs = array(
	'pdo' => array('label' => t('PDO'), 'file' => LOGSPATH.PDOLOGFILENAME),
	'sys' => array('label' => t('System'), 'file' => LOGSPATH.SYSLOGFILENAME),
	'debug' => array('label' => t('Debug'), 'file' => LOGSPATH.DEBUGFILENAME),
);

if( isPOST('submitEraseLogs') ) {
	$logID = key($_POST['submitEraseLogs']);
	
	if( file_put_contents($logs[$logID]['file'], '') !== false ) {
		reportSuccess('successFileErased');

	} else {
		reportError('unableToEraseFile');
	}
}

foreach( $logs as $logID => $logData ) {
	try {
		if( !is_readable($logData['file']) ) {
			?>No entry in this log.<?php
		}
		$logLines = file($logData['file'], FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		?>
	<h1><?php echo $logData['label']; ?></h1><?php
		
		if( !empty($logLines) )  {
			?>
			<form method="POST">
			<input type="submit" name="submitEraseLogs[<?php echo $logID; ?>]" value="Erase all" /><br />
			<ul><?php
			foreach( $logLines as $Line ) {
				$entryData = json_decode($Line, 1);
				echo "
				<li style=\"list-style-type: none; margin-bottom: 30px;\">
					Date: {$entryData['date']}<br />
					Action: {$entryData['action']}<br />
					Rapport: {$entryData['report']}
				</li>";
			}
			?>
			</ul>
			<input type="submit" name="submitEraseLogs[<?php echo $logID; ?>]" value="Erase all" /><br />
			</form><?php
		} else {
			?>No entry in this log.<?php
		}
	} catch(Exception $e) {
		echo t($e->__toString());
	}
}