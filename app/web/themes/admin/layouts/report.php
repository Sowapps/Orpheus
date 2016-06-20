<?php
$icons	= array(
	'success'	=> 'fa-check-circle-o',
	'error'		=> 'fa-exclamation-triangle'
);
?>
<div class="report report_<?php echo $Stream; ?> <?php echo $Type; ?>">
	<?php echo isset($icons[$Type]) ? '<i class="fa '.$icons[$Type].'"></i>' : ''; ?>
	<div class="report-text"><?php echo $Report; ?></div>
</div>
