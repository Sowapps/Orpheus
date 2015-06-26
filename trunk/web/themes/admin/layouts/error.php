<div class="panel panel-danger">
	<div class="panel-heading">Fatal Error</div>
	<div class="panel-body">
<p>A fatal error occurred, here is more informations.</p>
Action : <?php echo $action; ?><br />
Date : <?php echo $date; ?><br />
Report :<br />
<div class="well"><?php echo $report; ?></div>
<?php
if( !empty($message) ) {
	echo '
Message :<br/>
<div class="well">'.$message.'</div>';
}
?>
<p>
	Set the ERROR_LEVEL constant value to PROD_LEVEL in your constant file if you don't want to display errors.<br />
	Error reports are saved in logs in both cases.
</p>
<?php
if( !empty($page) ) {
	?>
Page: <button type="button" onclick="this.nextSibling.style.display = this.nextSibling.style.display === 'none' ? 'block' : 'none'; return 0;">Display</button><div style="display:none;"><?php echo $page; ?></div><br />
<?php
}
?>
	</div>
</div>
