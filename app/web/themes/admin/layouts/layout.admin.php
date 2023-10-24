<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var TranslationService $translator
 *
 * @var string $CONTROLLER_OUTPUT
 * @var string $content
 * @var array $breadcrumb
 * @var User $user
 */

use App\Controller\Admin\AbstractAdminController;
use App\Entity\User;
use Orpheus\Initernationalization\TranslationService;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$routeName = $controller->getRouteName();
$contentTitle = $controller->getOption(AbstractAdminController::OPTION_CONTENT_TITLE, $contentTitle ?? null);
$contentLegend = $controller->getOption(AbstractAdminController::OPTION_CONTENT_LEGEND);

$rendering->useLayout('document.web');
?>
<div class="container-fluid px-4">
	
	<?php
	if( $contentTitle !== false ) {
		if( $contentTitle === null ) {
			$contentTitle = t($titleRoute ?? $routeName);
		}
		if( $contentLegend === null ) {
			$contentLegend = t(($titleRoute ?? $routeName) . '_legend');
		}
		?>
		<h1 class="page-header mt-4">
			<?php echo $contentTitle; ?>
			<small><?php echo $contentLegend; ?></small>
		</h1>
		<?php
	}
	if( !empty($breadcrumb) ) {
		?>
		<ol class="breadcrumb mb-4">
			<?php
			$bcLast = count($breadcrumb) - 1;
			foreach( $breadcrumb as $index => $page ) {
				if( $index >= $bcLast || empty($page->link) ) {
					?>
					<li class="breadcrumb-item active">
						<?php echo $page->label; ?>
					</li>
					<?php
				} else {
					?>
					<li class="breadcrumb-item">
						<a href="<?php echo $page->link; ?>"><?php echo $page->label; ?></a>
					</li>
					<?php
				}
			}
			?>
		</ol>
		<?php
	}
	
	$rendering->display('reports');
	echo $content;
	?>
</div>
