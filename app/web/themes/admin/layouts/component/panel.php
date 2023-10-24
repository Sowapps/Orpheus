<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HtmlRendering $rendering
 * @var AbstractAdminController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $content
 */

use App\Controller\Admin\AbstractAdminController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$title ??= null;
$menu ??= [];
$body ??= $content; // Use display body parameter or use content from useLayout()
$footer ??= null;

?>
<div class="card mb-4 <?php echo $panelClass ?? ''; ?>">
	<?php
	if( $title || $menu ) {
		?>
		<div class="card-header <?php echo $titleClass ?? ''; ?>">
			<?php
			if( $menu ) {
				?>
				<ul class="nav nav-tabs card-header-tabs">
					<?php
					if( $title ) {
						?>
						<li class="nav-item">
							<a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">
								<?php echo $title; ?>
							</a>
						</li>
						<?php
					}
					if( !isset($menuActiveItem) ) {
						$menuActiveItem = null;
					}
					foreach( $menu as $itemKey => $item ) {
						$item = (object)$item;
						/**
						 * Item contains:
						 * - link
						 * - label
						 */
						$isActive = $itemKey === $menuActiveItem;
						?>
						<li class="nav-item <?php echo $itemKey; ?>">
							<a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $item->link; ?>"><?php echo $item->label; ?></a>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
			} else {
				if( !empty($titleIcon) ) {
					?><i class="<?php echo $titleIcon; ?>"></i><?php
				}
				echo $title;
			}
			?>
		</div>
		<?php
	}
	?>
	<div class="card-body <?php echo $bodyClass ?? ''; ?>">
		<?php
		if( !empty($bodyTitle) ) {
			?>
			<div class="mb-3 text-grey text-uppercase">
				<b><?php echo $bodyTitle; ?></b>
			</div>
			<?php
		}
		echo $body;
		?>
	</div>
	<?php
	if( $footer ) {
		?>
		<div class="card-footer <?php echo $footerClass ?? 'text-end'; ?>">
			<?php echo $footer; ?>
		</div>
		<?php
	}
	?>
</div>
