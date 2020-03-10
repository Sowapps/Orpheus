<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;

/**
 * @var string $CONTROLLER_OUTPUT
 * @var HTMLRendering $rendering
 * @var HTTPController $Controller
 * @var HTTPRequest $Request
 * @var HTTPRoute $Route
 */

$rendering->useLayout('page_skeleton');
?>

<div class="card border border-warning">
	<div class="card-header text-white bg-warning">Error</div>
	<div class="card-body">
		<p>An error occurred, preventing the application to continue normally.</p>
		<?php
		$this->display('reports-bootstrap3');
		?>
	</div>
</div>
