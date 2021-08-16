<?php
/**
 * @var HTMLRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var boolean $allowCreate
 * @var boolean $allowUpdate
 * @var SQLSelectRequest $query
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HTMLRendering;
use Orpheus\SQLRequest\SQLSelectRequest;


$rendering->useLayout('page_skeleton');

function displayByteRow($label, $value) {
	displayRow($label, formatInt($value) . ' bytes');
}

function displayRow($label, $value) {
	?>
	<div class="form-horizontal">
		<div class="form-group row">
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

?>
<h2>PHP Information <a class="btn btn-link" href="<?php echo u('dev_phpinfo'); ?>" target="_blank"><i class="fas fa-external-link-alt"></i></a></h2>
<iframe width="100%" height="600" src="<?php echo u('dev_phpinfo'); ?>"></iframe>
