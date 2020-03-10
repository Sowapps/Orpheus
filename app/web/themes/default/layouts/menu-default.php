<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPRoute;
use Orpheus\Rendering\HTMLRendering;
use Orpheus\Rendering\Menu\MenuItem;

/**
 * @var HTMLRendering $rendering
 * @var HTTPController $Controller
 * @var HTTPRequest $Request
 * @var HTTPRoute $Route
 *
 * @var string $menu
 * @var MenuItem[] $items
 */

?>
<ul class="navbar-nav mr-auto menu <?php echo $menu; ?>">
	<?php
	foreach( $items as $item ) {
		?>
		<li class="nav-item menu-item<?php echo ($item->route ? ' ' . $item->route : '') . ($item->current ? ' active' : ''); ?>">
			<a class="nav-link" href="<?php echo $item->link; ?>">
				<?php echo $item->label; ?>
			</a>
		</li>
		<?php
	}
	?>
</ul>
