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
 */

use App\Controller\Developer\DevEntitiesController;
use Orpheus\EntityDescriptor\Entity\PermanentEntity;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');
?>
<div class="row">
	<?php
	if( $requireEntityValidation ) {
		?>
		<div class="col-lg-6">
			<?php
			$rendering->useLayout('component/panel');
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
				<button type="submit" class="btn btn-primary" name="submitGenerateSql" value="<?php echo DevEntitiesController::OUTPUT_APPLY; ?>">
					<?php echo t('apply'); ?>
				</button>
			</form>
			<?php
			$rendering->endCurrentLayout(['title' => t('generated_sql_queries', DOMAIN_SETUP)]);
			?>
		</div>
		<?php
	}
	?>
	
	<div class="col-lg-6">
		<form method="POST" role="form" class="form-horizontal">
			<?php echo $formToken; ?>
			<?php $rendering->useLayout('component/panel'); ?>
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
				<button type="submit" class="btn btn-primary" name="submitGenerateSql" value="<?php echo DevEntitiesController::OUTPUT_DISPLAY; ?>">
					<?php echo t('generate'); ?>
				</button>
			</div>
			
			<?php $rendering->endCurrentLayout(['title' => 'Toutes les entités']); ?>
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
