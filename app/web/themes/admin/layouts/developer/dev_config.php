<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpRequest $request
 * @var HttpRoute $route
 * @var HttpController $controller
 *
 * @var AppConfig $config
 */

use Orpheus\Config\AppConfig;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.admin');
$rendering->addThemeJsFile('developer/dev_config.js');

?>
<div class="row">
	<div class="col-lg-12">
		<?php $rendering->useLayout('component/panel'); ?>
		<div class="btn-group mb-3" role="group" aria-label="<?php echo t('actionsColumn'); ?>">
			<button type="button" class="btn btn-outline-primary mb10 action-config-create">
				<i class="fa fa-plus"></i>
				<?php echo t('new'); ?>
			</button>
		</div>
		<table class="table table-bordered table-hover">
			<thead>
			<tr>
				<th><?php echo t('key'); ?></th>
				<th class="sorter-false"><?php echo t('value'); ?></th>
				<th class="sorter-false"><?php echo t('actionsColumn'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach( $config->asArray() as $key => $value ) {
				?>
				<tr data-key="<?php echo $key; ?>" data-type="<?php echo $config->getType($key); ?>" data-value="<?php echo escapeText($value, ENT_COMPAT | ENT_HTML5); ?>">
					<td><?php echo escapeText($key); ?></td>
					<td><?php echo html($value); ?></td>
					<td>
						<div class="btn-group" role="group" aria-label="Actions">
							<button type="button" class="btn btn-outline-secondary action-config-update"><i class="fa fa-edit"></i></button>
							<button type="button" class="btn btn-outline-secondary"
									data-confirm-title="Suppression"
									data-confirm-message="Souhaitez-vous réellement supprimer la clé <?php echo $key; ?> ?"
									data-confirm-submit-name="submitRemove" data-confirm-submit-value="<?php echo $key; ?>">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php $rendering->endCurrentLayout(); ?>
	</div>
</div>

<?php
$rendering->useLayout('component/dialog');
?>
<div class="mb-3">
	<label for="InputRowKey">Key</label>
	<input name="row[key]" type="text" class="form-control row_key update-readonly" id="InputRowKey">
</div>
<div class="mb-3">
	<label for="InputRowType">Type</label>
	<select name="row[type]" id="InputRowType" class="form-control">
		<?php echo htmlOptions(null, AppConfig::ALL_TYPES, AppConfig::DEFAULT_TYPE, OPT_VALUE); ?>
	</select>
	<!--		<input type="text" class="form-control row_type" id="InputRowType" disabled>-->
</div>
<div class="mb-3">
	<label for="InputRowValue">Value</label>
	<input name="row[value]" type="text" class="form-control row_value type_simple" id="InputRowValue" required>
	<input name="row[value]" type="checkbox" class="form-control row_value type_boolean" id="InputRowValue">
	<textarea name="row[value]" class="form-control row_value type_text" id="InputRowValue" rows="10" required hidden></textarea>
</div>
<?php
$rendering->startNewBlock('footer');
?>
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
<button type="submit" class="btn btn-primary" name="submitSave" data-submittext="<?php echo t('saving'); ?>"><?php echo t('save'); ?></button>
<?php
$rendering->endCurrentLayout([
	'id'    => 'DialogConfigEdit',
	'title' => t('save'),
	'form'  => true,
]);
?>

