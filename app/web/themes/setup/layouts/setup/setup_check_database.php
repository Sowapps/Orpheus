<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var AbstractSqlAdapter $sqlAdapter
 * @var boolean $allowContinue
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;
use Orpheus\SqlAdapter\AbstractSqlAdapter;

$dbConfig = $sqlAdapter->getConfig();

require_once 'components.php';

$rendering->useLayout('layout.setup');

?>
<form method="POST">
	<div class="row">
		
		<div class="col-lg-8 offset-lg-2">
			
			<h1><?php echo t('check_database_title', DOMAIN_SETUP, [t('app_name')]); ?></h1>
			<p class="lead"><?php echo html(t('check_database_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
			
			<div class="form-horizontal">
				<?php
				rowStaticInput('InputDbHost', t('db_host', DOMAIN_SETUP), $dbConfig['host']);
				rowStaticInput('InputDbDriver', t('db_driver', DOMAIN_SETUP), $dbConfig['driver']);
				rowStaticInput('InputDbHost', t('db_database', DOMAIN_SETUP), $dbConfig['dbname']);
				rowStaticInput('InputDbUser', t('db_user', DOMAIN_SETUP), $dbConfig['user']);
				rowStaticInput('InputDbPassword', t('db_password', DOMAIN_SETUP), '**********');
				?>
			</div>
			<?php
			
			$this->display('reports');
			
			if( $allowContinue ) {
				?>
				<div class="text-end mt-3">
					<a class="btn btn-lg btn-primary" href="<?php echo u('setup_install_database'); ?>" role="button"><?php echo t('continue', DOMAIN_SETUP); ?></a>
				</div>
				<?php
			}
			?>
		
		</div>
	
	</div>
</form>
