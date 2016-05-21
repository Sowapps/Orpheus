<div class="fatalerror report report_global error">
An error has occurred, here is more informations:<br />
Action: <?php echo $action; ?><br />
Date: <?php echo $date; ?><br />
Report: <?php echo $report; ?><br />
<?php
if( !empty($message) ) { echo 'Message: '.$message.'<br />'; }
?>
Set the ERROR_LEVEL constant value to PROD_LEVEL in your constant file if you don't want to display errors.<br />
Error reports are saved in logs in both cases.<br />
Page: <button type="button" onclick="this.nextSibling.style.display = this.nextSibling.style.display === 'none' ? 'block' : 'none'; return 0;">Display</button><div style="display:none;"><?php echo $page; ?></div><br />
<br />
</div>