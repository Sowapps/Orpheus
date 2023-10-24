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
<div class="menu <?php echo $menu; ?>">
	<div class="sb-sidenav-menu-heading"><?php echo t('menu_' . $menu); ?></div>
	<?php
	foreach( $items as $item ) {
		$itemClasses = '';
		if( $item->getRoute() ) {
			$itemClasses .= ' ' . $item->getRoute();
		}
		if( $item->isActive() ) {
			$itemClasses .= ' active';
		}
		?>
		<a class="nav-link<?php echo $itemClasses; ?>" href="<?php echo $item->getLink(); ?>">
			<?php echo $item->getLabel(); ?>
		</a>
		<?php
	}
	?>
</div>
