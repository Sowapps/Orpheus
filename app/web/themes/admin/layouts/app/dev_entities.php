<?php
/* @var $resultingSQL string */
/* @var $FORM_TOKEN FormToken */

HTMLRendering::useLayout('page_skeleton');
/*
<div class="mt10">
	<a href="<?php _u('dev_entities_merge'); ?>">Merge tool</a>
</div>
*/
if( !empty($resultingSQL) ) {
// 	echo '<div>'.$resultingSQL.'</div>';
	if( !empty($requireEntityValidation) ) {
		?>
<div><?php echo $resultingSQL; ?></div>
<form method="POST"><?php echo $FORM_TOKEN; ?>
<?php
		foreach( POST('entities') as $entityName => $on ) {
			echo htmlHidden('entities/'.$entityName);
		}
		?>
	<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_APPLY; ?>]">Apply</button></form>
<?php
	}
}
?>
<form method="POST" role="form" class="form-horizontal">
<?php echo $FORM_TOKEN; ?>

<div class="row">
	<div class="col-lg-6">
		<h2>Entities found</h2>
		<button class="btn btn-default btn-sm" type="button" onclick="$('.entitycb').prop('checked', true);">Check all</button>
		<button class="btn btn-default btn-sm" type="button" onclick="$('.entitycb').prop('checked', false);">Uncheck all</button>
		
		<div class="row">
		<?php
// 		$entities = cleanscandir(pathOf(CONFDIR.ENTITY_DESCRIPTOR_CONFIG_PATH));
		foreach( EntityDescriptor::getAllEntities() as $entity ) {
// 			$pi = pathinfo($filename);
// 			if( $pi['extension'] != 'yaml' ) { continue; }
			echo '
		<div class="checkbox col-sm-4"><label><input class="entitycb" type="checkbox" name="entities['.$entity.']"'.(!isPOST() || isPOST('entities/'.$entity) ? ' checked' : '').'/> '.$entity.'</label></div>';
		}
		/*
			<label class="col-sm-4 control-label">SQL</label>
			<div class="col-sm-3"><select name="sql_output" class="form-control">
				<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
				<option value="<?php echo OUTPUT_APPLY; ?>">Apply</option>
			</select></div>
			*/
		?>
		</div>
		
		<div class="row form-group">
			<label class="col-sm-4 control-label">SQL</label>
			<div class="col-sm-3">
				<button type="submit" class="btn btn-default" name="submitGenerateSQL[<?php echo OUTPUT_DISPLAY; ?>]">Generate</button>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-4 control-label">Validation Errors</label>
			<div class="col-sm-3"><select name="ve_output" class="form-control">
				<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
				<option value="<?php echo OUTPUT_DLRAW; ?>">Download (TXT)</option>
			</select></div>
			<button type="submit" class="btn btn-default" name="submitGenerateVE">Generate</button>
		</div>
		
	</div>
</div>

</form>
<style>
.table-operation {
	margin-bottom: 10px;
	white-space: pre;
	tab-size: 4;
	-moz-tab-size: 4;
	font-size: 12px;
}
.table-name {
	display: inline;
	font-weight: bold;
}

</style>
