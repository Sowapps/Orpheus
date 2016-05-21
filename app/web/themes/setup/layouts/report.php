<?php

if( $Type === 'error' ) {
	$Type	= 'danger';
}
?>
<div class="alert alert-<?php echo $Type; ?> alert-dismissable">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<?php echo $Report; ?>
</div>
