<?php

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;
use Orpheus\Rendering\Menu\MenuItem;

/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
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
