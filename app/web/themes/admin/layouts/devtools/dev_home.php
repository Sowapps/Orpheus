<?php
use Orpheus\Rendering\HTMLRendering;

/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */

HTMLRendering::useLayout('page_skeleton');

function displayByteRow($label, $value) {
	displayRow($label, formatInt($value).' bytes');
}

function displayRow($label, $value) {
	?>
<div class="form-horizontal">
	<div class="form-group">
		<label class="col-sm-2 control-label"><?php echo $label; ?></label>
		<div class="col-sm-10">
			<p class="form-control-static"><?php echo $value; ?></p>
		</div>
	</div>
</div>
	<?php
}


displayByteRow('Memory usage', memory_get_usage());
displayByteRow('Memory real usage', memory_get_usage(true));
displayByteRow('Memory peak usage', memory_get_peak_usage(true));
displayByteRow('Memory peak real usage', memory_get_peak_usage(true));

displayRow('Process ID', getmypid());
displayRow('CPU Load', implode(' / ', sys_getloadavg()));

// debug('getrusage()', getrusage());
// debug('getrusage($who=1)', getrusage(1));
