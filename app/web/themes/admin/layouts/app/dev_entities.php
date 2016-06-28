<?php
use Orpheus\Rendering\HTMLRendering;
use Orpheus\EntityDescriptor\PermanentEntity;

/* @var HTMLRendering $this */
/* @var HTTPController $Controller */
/* @var HTTPRequest $Request */
/* @var HTTPRoute $Route */
/* @var $resultingSQL string */
/* @var $FORM_TOKEN FormToken */

HTMLRendering::useLayout('page_skeleton');
/*
<div class="mt10">
	<a href="<?php _u('dev_entities_merge'); ?>">Merge tool</a>
</div>
*/
// $this->display('reports-bootstrap3');
?>
<div class="row">
<?php
if( !empty($resultingSQL) ) {
	// 	echo '<div>'.$resultingSQL.'</div>';
	if( !empty($requireEntityValidation) ) {
		/*
<h3><?php _t('generated_sqlqueries', DOMAIN_SETUP); ?></h3>
*/
		?>
	<div class="col-lg-6">
		<?php HTMLRendering::useLayout('panel-default'); ?>
		<div class="sql_query"><?php echo $resultingSQL; ?></div>
		<form method="POST"><?php echo $FORM_TOKEN; ?>
			<?php
			foreach( POST('entities') as $entityClass => $on ) {
				echo htmlHidden('entities/'.$entityClass);
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
							<input class="entitycb" type="checkbox" name="removeTable['.$table.']"/> '.$table.'
						</label>
					</li>';
				}
				?></ul><?php
			}
			?>
			<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_APPLY; ?>]"><?php _t('apply'); ?></button>
		</form>
		<?php
		HTMLRendering::endCurrentLayout(array('title'=>t('generated_sqlqueries', DOMAIN_SETUP)));
		?>
	</div>
<?php
	}
}
?>


	<div class="col-lg-6">
		<form method="POST" role="form" class="form-horizontal"><?php echo $FORM_TOKEN; ?>
		<?php HTMLRendering::useLayout('panel-default'); ?>
		<button class="btn btn-info btn-sm" type="button" onclick="$('.entitycb').prop('checked', true);"><i class="fa fa-fw fa-check-square-o"></i> <?php _t('checkall'); ?></button>
		<button class="btn btn-info btn-sm" type="button" onclick="$('.entitycb').prop('checked', false);"><i class="fa fa-fw fa-square-o"></i> <?php _t('uncheckall'); ?></button>
		
		<ul class="list-group">
		<?php
		foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
			echo '
			<li class="list-group-item">
				<label class="wf">
					<input class="entitycb" type="checkbox" name="entities['.$entityClass.']"'.(!isPOST() || isPOST('entities/'.$entityClass) ? ' checked' : '')
					.' title="'.$entityClass.'"/> '.$entityClass.'
				</label>
			</li>';
		}
		?>
		</ul>
		
		<div class="form-group">
			<label class="col-sm-4 control-label">SQL</label>
			<div class="col-sm-3">
				<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_DISPLAY; ?>]">Generate</button>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-sm-4 control-label">Validation Errors</label>
			<div class="col-sm-3">
				<select name="ve_output" class="form-control">
					<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
					<option value="<?php echo OUTPUT_DLRAW; ?>">Download (TXT)</option>
				</select>
			</div>
			<button type="submit" class="btn btn-primary" name="submitGenerateVE">Generate</button>
		</div>
		
		<?php HTMLRendering::endCurrentLayout(array('title'=>'Toutes les entités')); ?>
		</form>
	</div>

</div>
<style>
.sql_query {
	font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
	font-size: 90%;
	padding: 10px 20px;
	background-color: #f7f7f9;
	border-radius: 4px;
	margin-bottom: 20px;
}
.table-operation {
	margin-bottom:	10px;
	white-space:	pre;
	tab-size:		4;
	-moz-tab-size:	4;
	font-size:		12px;
}
/*
.table-name {
	display: inline;
	font-weight: bold;
}
*/
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
/* 	color: #8a6d3b; */
}
.tabulation {
	display: inline;
	width: 60px;
}
</style>
