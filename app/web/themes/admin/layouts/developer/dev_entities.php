<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var FormToken $formToken
 * @var string|null $resultingSQL
 */

use Orpheus\EntityDescriptor\PermanentEntity;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('page_skeleton');
?>
<div class="row">
	<?php
	if( !empty($resultingSQL) ) {
		if( !empty($requireEntityValidation) ) {
			?>
			<div class="col-lg-6">
				<?php $rendering->useLayout('panel-default'); ?>
				<div class="sql_query"><?php echo $resultingSQL; ?></div>
				<form method="POST"><?php echo $formToken; ?>
					<?php
					foreach( POST('entities') as $entityClass => $on ) {
						echo htmlHidden('entities/' . $entityClass);
					}
					if( !empty($unknownTables) ) {
						?>
						<h3><?php _t('removeUnknownTables', DOMAIN_SETUP); ?></h3>
						<ul class="list-group">
						<?php
						foreach( $unknownTables as $table => $on ) {
							echo '
					<li class="list-group-item">
						<label class="wf">
							<input class="entitycb" type="checkbox" name="removeTable[' . $table . ']"/> ' . $table . '
						</label>
					</li>';
						}
						?></ul><?php
					}
					?>
					<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_APPLY; ?>]"><?php _t('apply'); ?></button>
				</form>
				<?php
				$rendering->endCurrentLayout(['title' => t('generated_sqlqueries', DOMAIN_SETUP)]);
				?>
			</div>
			<?php
		}
	}
	?>
	
	<div class="col-lg-6">
		<form method="POST" role="form" class="form-horizontal"><?php echo $formToken; ?>
			<?php $rendering->useLayout('panel-default'); ?>
			<button class="btn btn-info btn-sm" type="button" onclick="$('.entitycb').prop('checked', true);"><i class="far fa-fw fa-check-square"></i> <?php _t('checkall'); ?></button>
			<button class="btn btn-info btn-sm" type="button" onclick="$('.entitycb').prop('checked', false);"><i class="far fa-fw fa-square"></i> <?php _t('uncheckall'); ?></button>
			
			<ul class="list-group mt-2 mb-2">
				<?php
				foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
					echo '
			<li class="list-group-item">
				<label class="wf mb-0">
					<input class="entitycb" type="checkbox" name="entities[' . $entityClass . ']"' . (!isPOST() || isPOST('entities/' . $entityClass) ? ' checked' : '')
						. ' title="' . $entityClass . '"/> ' . $entityClass . '
				</label>
			</li>';
				}
				?>
			</ul>
			
			<div class="form-group row">
				<label class="col-sm-4 control-label">SQL</label>
				<div class="col-sm-3">
					<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_DISPLAY; ?>]">Generate</button>
				</div>
			</div>
			
			<div class="form-group row">
				<label class="col-sm-4 control-label">Validation Errors</label>
				<div class="col-sm-3">
					<select name="ve_output" class="form-control">
						<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
						<option value="<?php echo OUTPUT_DLRAW; ?>">Download (TXT)</option>
					</select>
				</div>
				<div class="col-sm-3">
					<button type="submit" class="btn btn-primary" name="submitGenerateVE">Generate</button>
				</div>
			</div>
			
			<?php $rendering->endCurrentLayout(['title' => 'Toutes les entités']); ?>
		</form>
	</div>

</div>
<style>
.sql_query {
	font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
	font-size: 90%;
	padding: 10px 20px;
	background-color: #f7f7f9;
	border-radius: 4px;
	margin-bottom: 20px;
}
</style>
