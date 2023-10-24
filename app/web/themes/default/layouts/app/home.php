<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 */

use App\Entity\User;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$user = User::getActiveUser();
$rendering->useLayout('layout.public');

$home = [
	'link_setup' => '<a href="' . u('setup_start') . '">',
	'link_end'   => '</a>',
	'code_start' => '<span class="text-secondary fst-italic">',
	'code_end'   => '</span>',
];
?>

<div class="container py-4">
	
	<div class="p-5 mb-5 bg-light border rounded-3">
		<div class="px-3">
			<h1 class="display-5 fw-bold mb-3">
				<?php echo t('home.introduction.title', DOMAIN_APP, $home); ?>
			</h1>
			<p class="col-md-10 fs-4">
				<?php echo nl2br(t('home.introduction.legend', DOMAIN_APP, $home)); ?>
			</p>
		</div>
	</div>
	
	<section class="mb-4">
		<h2 class="border-start border-5 border-info ps-3">
			<?php echo t('home.information.title', DOMAIN_APP, $home); ?>
		</h2>
		<p><?php echo nl2br(t('home.information.usingMvc', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.information.usingTheming', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.information.usingInternationalization', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.information.usingBackOffice', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.information.usingBootstrap', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.information.usingFontAwesome', DOMAIN_APP, $home)); ?></p>
	</section>
	
	<section class="mb-4">
		<h2 class="border-start border-5 border-info ps-3">
			<?php echo t('home.start.title', DOMAIN_APP, $home); ?>
		</h2>
		<p><?php echo nl2br(t('home.start.legend', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepActiveLanguages', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepSetApplicationName', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepConfigureDefaultLanguage', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepCompleteTranslations', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepConfigureDatabase', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepCreateDatabaseStructure', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepConfigureFixtures', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepRunSetup', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepCreateControllers', DOMAIN_APP, $home)); ?></p>
		<p><?php echo nl2br(t('home.start.stepCleanInstallationHelp', DOMAIN_APP, $home)); ?></p>
	</section>

</div>
