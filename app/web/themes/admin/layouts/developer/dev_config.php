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

$rendering->useLayout('page_skeleton');

?>
<div class="row">
	<div class="col-lg-12">
		<?php $rendering->useLayout('panel-default'); ?>
		<div class="btn-group" role="group" aria-label="<?php _t('actionsColumn'); ?>">
			<button type="button" class="btn btn btn-primary mb10 createbtn"><i class="fa fa-plus"></i> <?php _t('new'); ?></button>
		</div>
		<table class="table table-bordered table-hover">
			<thead>
			<tr>
				<th><?php _t('key'); ?></th>
				<th class="sorter-false"><?php _t('value'); ?></th>
				<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach( $config->asArray() as $key => $value ) {
				?>
				<tr data-key="<?php echo $key; ?>" data-type="<?php echo $config->getType($key); ?>" data-value="<?php echo escapeText($value, ENT_COMPAT | ENT_HTML5); ?>">
					<td><?php echo escapeText($key); ?></td>
					<td><?php echo nl2br(escapeText(formatToUser($value))); ?></td>
					<td>
						<div class="btn-group" role="group" aria-label="Actions">
							<button type="button" class="btn btn-outline-secondary editbtn"><i class="fa fa-edit"></i></button>
							<button type="button" class="btn btn-outline-secondary"
									data-confirm_title="Suppression"
									data-confirm_message="Souhaitez-vous réellement supprimer la clé <?php echo $key; ?> ?"
									data-confirm_submit_name="submitRemove" data-confirm_submit_value="<?php echo $key; ?>">
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


<div id="EditConfigDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="POST">
				
				<div class="modal-header">
					<h4 class="modal-title">Edit configuration</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					
					<div class="form-group">
						<label for="inputRowKey">Key</label>
						<input name="row[key]" type="text" class="form-control row_key" id="inputRowKey" readonly>
					</div>
					<div class="form-group">
						<label for="inputRowType">Type</label>
						<input type="text" class="form-control row_type" id="inputRowType" disabled>
					</div>
					<div class="form-group">
						<label for="inputRowValue">Value</label>
						<input name="row[value]" type="text" class="form-control row_value type_simple type_boolean" id="inputRowValue" required>
						<textarea name="row[value]" class="form-control row_value type_text" id="inputRowValue" rows="10" required></textarea>
					</div>
				
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
					<button name="submitSave" type="submit" class="btn btn-primary" data-submittext="Saving...">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var DIALOG_EDITCONFIG;
var largeTypeDialog = ['<?php echo AppConfig::TYPE_LONG_TEXT; ?>'];

function setEditDialogValueType(type) {
	if( largeTypeDialog.includes(type) ) {
		DIALOG_EDITCONFIG.find('.modal-dialog').addClass('modal-lg');
	} else {
		DIALOG_EDITCONFIG.find('.modal-dialog').removeClass('modal-lg');
	}
	console.log('setEditDialogValueType', type);
	console.log('All input of type', DIALOG_EDITCONFIG.find('[class*="type_"]'));
	console.log('This input of type', DIALOG_EDITCONFIG.find('[class*="type_"]').filter('.type_' + type));
	DIALOG_EDITCONFIG
		.find('[class*="type_"]').hide().prop('disabled', true)
		.filter('.type_' + type).show().prop('disabled', false);
}

$(function () {
	DIALOG_EDITCONFIG = $("#EditConfigDialog").modal({show: false});
	
	$(".createbtn").click(function () {
		DIALOG_EDITCONFIG.find("form").get(0).reset();
		setEditDialogValueType("<?php echo AppConfig::DEFAULT_TYPE; ?>");
		DIALOG_EDITCONFIG.modal("show");
	});
	
	$(".editbtn").click(function () {
		var data = $(this).closest("tr").data();
		DIALOG_EDITCONFIG.find("form").get(0).reset();
		setEditDialogValueType(data.type);
		DIALOG_EDITCONFIG.fill("row_", data);
		DIALOG_EDITCONFIG.modal("show");
	});
});
</script>

