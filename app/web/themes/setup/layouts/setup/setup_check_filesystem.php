<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var array $folders
 * @var boolean $allowContinue
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

require_once 'components.php';

$rendering->useLayout('layout.setup');

function collapsiblePanelHTML(string $id, string $title, string $description, string $panelClass, bool $open = false): void {
	?>
	<div class="accordion-item <?php echo $panelClass; ?>">
		<h4 class="accordion-header">
			<button class="accordion-button<?php echo $panelClass === PANEL_SUCCESS ? ' text-success' : ' text-danger'; ?><?php echo $open ? '' : ' collapsed'; ?>" type="button"
					data-bs-toggle="collapse" data-bs-target="#<?php echo $id; ?>"
					aria-expanded="<?php echo b($open); ?>"
					aria-controls="<?php echo $id; ?>">
				<?php
				if( $panelClass === PANEL_SUCCESS ) {
					?><i class="fa fa-fw fa-check me-1"></i> <?php
				} else {
					?><i class="fa fa-fw fa-times me-1"></i><?php
				}
				echo $title;
				?>
			</button>
		</h4>
		<div id="<?php echo $id; ?>" class="accordion-collapse collapse<?php echo $open ? ' show' : ''; ?>" data-bs-parent="#AccordionCheckFS">
			<div class="accordion-body">
				<?php echo html($description); ?>
			</div>
		</div>
	</div>
	<?php
}

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-8 offset-lg-2">
			
			<h1><?php echo t('check_filesystem_title', DOMAIN_SETUP, [t('app_name')]); ?></h1>
			<p class="lead"><?php echo html(t('check_filesystem_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
			
			<?php
			rowStaticInput('InputWebAccessPath', t('pathToFolder', DOMAIN_SETUP, [t('folder_application', DOMAIN_SETUP)]), INSTANCE_PATH);
			rowStaticInput('InputWebAccessPath', t('pathToFolder', DOMAIN_SETUP, [t('folder_web', DOMAIN_SETUP)]), ACCESS_PATH);
			?>
			
			<div class="accordion" id="AccordionCheckFS" role="tablist" aria-multiselectable="true">
				<?php
				foreach( $folders as $folder => $fi ) {
					collapsiblePanelHTML($folder, $fi->title, $fi->description, $fi->panel, $fi->open);
				}
				?>
			</div>
			
			<?php
			if( $allowContinue ) {
				?>
				<div class="text-end mt-3">
					<a class="btn btn-lg btn-primary" href="<?php echo u('setup_check_database'); ?>" role="button"><?php echo t('continue', DOMAIN_SETUP); ?></a>
				</div>
				<?php
			}
			?>
		
		</div>
	
	</div>
</form>
