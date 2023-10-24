<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $menu
 * @var MenuItem[] $items
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;
use Orpheus\Rendering\Menu\MenuItem;


?>
<ul class="navbar-nav me-auto menu <?php echo $menu; ?>">
	<?php
	foreach( $items as $item ) {
		?>
		<li class="nav-item menu-item<?php echo $item->getRoute() ?? ''; ?>">
			<a class="nav-link<?php echo $item->isActive() ? ' active' : ''; ?>" href="<?php echo $item->getLink(); ?>">
				<?php echo $item->getLabel(); ?>
			</a>
		</li>
		<?php
	}
	?>
</ul>
