<?php
/**
 * @var string $CONTROLLER_OUTPUT
 * @var HtmlRendering $rendering
 * @var AbstractAdminController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var string $id
 * @var string $title
 * @var string $body
 * @var string $content Alternative to body when using useLayout()
 * @var string|bool $form Optional argument to set form in dialog (true for post)
 */

use App\Controller\Admin\AbstractAdminController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$form ??= false;

?>
<div id="<?php echo $id; ?>" class="modal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<?php
			if( $form ) {
				echo '<form method="' . ($form === true ? 'post' : $form) . '">';
			}
			?>
			<div class="modal-header">
				<h5 class="modal-title">
					<?php
					if( !empty($titleIcon) ) {
						?><i class="<?php echo $titleIcon; ?>"></i><?php
					}
					echo $title;
					?>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo t('close'); ?>"></button>
			</div>
			<div class="modal-body">
				<?php echo $body ?? $content; ?>
			</div>
			<?php
			if( !empty($footer) ) {
				?>
				<div class="modal-footer">
					<?php echo $footer; ?>
				</div>
				<?php
			}
			if( $form ) {
				echo '</form>';
			}
			
			?>
		</div>
	</div>
</div>
