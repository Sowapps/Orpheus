<?php
$AdminPage = 1;

$logs = array(
	'pdo' => array('label' => t('PDO'), 'file' => LOGSPATH.PDOLOGFILENAME),
	'sys' => array('label' => t('System'), 'file' => LOGSPATH.SYSLOGFILENAME),
	'hack' => array('label' => t('HackLogs'), 'file' => LOGSPATH.HACKLOGFILENAME),
	'server' => array('label' => t('ServerLogs'), 'file' => LOGSPATH.SERVLOGFILENAME),
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
		$logLines = null;
		?>
	<h1><?php echo $logData['label']; ?></h1><?php
		if( is_readable($logData['file']) ) {
			$logLines = file($logData['file'], FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		}
		
		if( !empty($logLines) )  {
			?>
			<form method="POST">
			<input type="submit" name="submitEraseLogs[<?php echo $logID; ?>]" value="Tout effacer" /><br />
			<ul><?php
			foreach( $logLines as $Line ) {
				try {
					$entryData = json_decode($Line, 1);
					if( !isset($entryData['date']) )	{ $entryData['date']	= 'N/A'; }
					if( !isset($entryData['action']) )	{ $entryData['action']	= 'N/A'; }
					if( !isset($entryData['report']) )	{ $entryData['report']	= 'N/A'; }
					echo "
					<li style=\"list-style-type: none; margin-bottom: 30px;\">
						Date: {$entryData['date']}<br />
						Action: {$entryData['action']}<br />
						Rapport: {$entryData['report']}
					</li>";
				} catch ( Exception $e ) {
					echo $e;
				}
			}
			?>
			</ul>
			<input type="submit" name="submitEraseLogs[<?php echo $logID; ?>]" value="Tout effacer" /><br />
			</form><?php
		} else {
			?>Aucune entrée connue dans ce journal.<?php
		}
	} catch(Exception $e) {
		echo t("$e");
// 		echo t($e->__toString());
	}
}