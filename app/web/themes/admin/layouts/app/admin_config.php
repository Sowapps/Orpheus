<?php
use Orpheus\Rendering\HTMLRendering;
use Orpheus\Config\AppConfig;

HTMLRendering::useLayout('page_skeleton');

$AppConfig = AppConfig::instance();
?>
<div class="row">
	<div class="col-lg-12">
		<?php HTMLRendering::useLayout('panel-default'); ?>
<div class="btn-group" role="group" aria-label="Actions">
	<button type="button" class="btn btn btn-primary mb10 createbtn"><i class="fa fa-plus"></i> <?php _t('new'); ?></button>
</div>
<table class="table table-bordered table-hover tablesorter">
	<thead>
		<tr>
			<th><?php _t('key'); ?> <i class="fa fa-sort" title="Trier par Clé"></i></th>
			<th class="sorter-false"><?php _t('value'); ?></th>
			<th class="sorter-false"><?php _t('actionsColumn'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach( $AppConfig->asArray() as $key => $value ) {
	echo '
<tr data-key="'.$key.'" data-value="'.$value.'">
	<td>'.escapeText($key).'</td>
	<td>'.escapeText($value).'</td>
	<td>
		<div class="btn-group" role="group" aria-label="Actions">
			<button type="button" class="btn btn-default editbtn"><i class="fa fa-edit"></i></button>
			<button type="button" class="btn btn-default deletebtn"><i class="fa fa-times"></i></button>
		</div>
	</td>
</tr>';
}
?>
				</tbody>
			</table>
		<?php HTMLRendering::endCurrentLayout(); ?>
	</div>
</div>


<div id="EditConfigDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
		<form method="POST">
			
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit configuration</h4>
			</div>
			<div class="modal-body">
			
				<div class="form-group">
					<label for="inputRowKey">Key</label>
					<input name="row[key]" type="text" class="form-control row_key" id="inputRowKey" required>
				</div>
				<div class="form-group">
					<label for="inputRowValue">Value</label>
					<input name="row[value]" type="text" class="form-control row_value" id="inputRowValue" required>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button name="submitSave" type="submit" class="btn btn-primary" data-submittext="Saving...">Save</button>
			</div>
		</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var DIALOG_EDITCONFIG;
$(function() {
	DIALOG_EDITCONFIG	= $("#EditConfigDialog").modal({show:false});

	$(".createbtn").click(function() {
		DIALOG_EDITCONFIG.find("form").get(0).reset();
		DIALOG_EDITCONFIG.modal("show");
	});

	$(".editbtn").click(function() {
		DIALOG_EDITCONFIG.find("form").get(0).reset();
		DIALOG_EDITCONFIG.fill("row_", $(this).closest("tr").data());
		DIALOG_EDITCONFIG.modal("show");
	});
});
</script>

