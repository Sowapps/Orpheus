<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var FormToken $formToken
 * @var string|null $queriesHtml
 * @var array $unknownTables
 * @var bool $requireEntityValidation
 * @var array $selectedEntities
 * @var bool $allowContinue
 */

use App\Controller\Setup\InstallDatabaseSetupController;
use Orpheus\EntityDescriptor\Entity\PermanentEntity;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.setup');

?>
<div class="row">
	
	<div class="col-lg-8 offset-lg-2">
		
		<h1><?php echo t('install_database_title', DOMAIN_SETUP, [t('app_name')]); ?></h1>
		<p class="lead"><?php echo html(t('install_database_description', DOMAIN_SETUP, ['APP_NAME' => t('app_name')])); ?></p>
		<?php
		$this->display('reports');
		
		if( $requireEntityValidation ) {
			?>
			<h3><?php echo t('generated_sql_queries', DOMAIN_SETUP); ?></h3>
			<?php
			if( $queriesHtml ) {
				?>
				<div class="sql_query"><?php echo $queriesHtml; ?></div>
				<?php
			}
			?>
			<form method="POST"><?php echo $formToken; ?>
				<?php
				foreach( $selectedEntities as $entityClass ) {
					?>
					<input type="hidden" name="entities[]" value="<?php echo $entityClass; ?>"/>
					<?php
				}
				if( $unknownTables ) {
					?>
					<h4><?php echo t('removeUnknownTables', DOMAIN_SETUP); ?></h4>
					<ul class="list-group mb-3">
						<?php
						foreach( $unknownTables as $table ) {
							?>
							<li class="list-group-item">
								<label>
									<input type="checkbox" class="me-1" name="removeTable[]" value="<?php echo $table; ?>"/>
									<?php echo $table; ?>
								</label>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
				}
				?>
				<div class="text-end mt-3">
					<button type="submit" class="btn btn-primary" name="submitGenerateSql" value="<?php echo InstallDatabaseSetupController::OUTPUT_APPLY; ?>">
						<?php echo t('apply'); ?>
					</button>
				</div>
			</form>
			<?php
		}
		?>
		
		<form method="POST" role="form" class="form-horizontal">
			<?php echo $formToken; ?>
			
			<h2><?php echo t('found_entities', DOMAIN_SETUP); ?></h2>
			
			<button class="btn btn-outline-info btn-sm" type="button" data-toggle="check" data-target=".entity-checkbox">
				<i class="far fa-fw fa-check-square"></i>
				<?php echo t('check_all'); ?>
			</button>
			<button class="btn btn-outline-info btn-sm" type="button" data-toggle="uncheck" data-target=".entity-checkbox">
				<i class="far fa-fw fa-square"></i>
				<?php echo t('uncheck_all'); ?>
			</button>
			
			<ul class="list-group mt-2 mb-2">
				<?php
				foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
					?>
					<li class="list-group-item">
						<label>
							<input class="entity-checkbox me-1" type="checkbox" name="entities[]" value="<?php echo $entityClass; ?>" title="<?php echo $entityClass; ?>" <?php
							echo(!$selectedEntities || in_array($entityClass, $selectedEntities) ? ' checked' : ''); ?>/>
							<?php echo $entityClass; ?>
						</label>
					</li>
					<?php
				}
				?>
			</ul>
			
			<div class="text-end mt-3">
				<button title="DO IT ! JUST DO IT !" type="submit" class="btn btn-lg <?php echo !$allowContinue && !$queriesHtml ? 'btn-primary' : 'btn-outline-secondary'; ?>"
						name="submitGenerateSql" value="<?php echo InstallDatabaseSetupController::OUTPUT_DISPLAY; ?>">
					<?php echo t('check_database', DOMAIN_SETUP); ?>
				</button>
				<?php
				if( $allowContinue ) {
					?>
					<a class="btn btn-lg btn-primary" href="<?php echo u('setup_install_fixtures'); ?>" role="button"><?php echo t('continue', DOMAIN_SETUP); ?></a>
					<?php
				}
				?>
			</div>
		
		</form>
	</div>
</div>

<script>
	$(() => {
		function getPropertyFunction(checked) {
			return ($button) => {
				const target = $button.dataset.target;
				$button.addEventListener("click", () => {
					document.querySelectorAll(target)
						.forEach(function ($input) {
							$input.checked = checked;
							$input.dispatchEvent(new Event("change"));
						});
				});
			};
		}
		
		document.querySelectorAll("[data-toggle=\"check\"]")
			.forEach(getPropertyFunction(true));
		document.querySelectorAll("[data-toggle=\"uncheck\"]")
			.forEach(getPropertyFunction(false));
	});
</script>

<style>
	.sql_query {
		font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
		font-size: 90%;
		padding: 10px 20px;
		background-color: #f7f7f9;
		border-radius: 4px;
		margin-bottom: 20px;
	}
	
	.table-operation {
		margin-bottom: 10px;
		white-space: pre;
		tab-size: 4;
		-moz-tab-size: 4;
		font-size: 12px;
	}
	
	.query_reservedWord {
		text-transform: uppercase;
		font-weight: bold;
		color: #31b0d5;
	}
	
	.query_command {
		color: #025aa5;
	}
	
	.query_subCommand {
		color: #025aa5;
	}
	
	.query_identifier {
		color: #5cb85c;
	}
	
	.query_columnType {
		color: #f0ad4e;
	}
	
	.tabulation {
		display: inline;
		width: 60px;
	}
</style>
