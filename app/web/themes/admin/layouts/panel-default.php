<?php
/**
 * @var HTMLRendering $rendering
 * @var string $Content
 */

use Orpheus\Rendering\HTMLRendering;

if( !isset($title) ) {
	$title = '';
}

if( !isset($footer) ) {
	$footer = '';
}

if( !isset($bodyClass) ) {
	$bodyClass = '';
}

?>
<div class="panel panel-default">
	<?php if( !empty($title) ) { ?>
		<div class="panel-heading">
			<h4 class="panel-title"><?php echo $title; ?></h4>
		</div>
	<?php } ?>
	<div class="panel-body <?php echo $bodyClass; ?>">
		<?php
		echo $Content;
		?>
	</div>
	<?php
	echo $footer;
	?>
</div>
