<?php
/* @var Orpheus\Rendering\HTMLRendering $this */
use Orpheus\Rendering\HTMLRendering;
use Orpheus\EntityDescriptor\PermanentEntity;

/* @var $resultingSQL string */
/* @var $FORM_TOKEN FormToken */

HTMLRendering::useLayout('page_skeleton');
/*
<div class="mt10">
	<a href="<?php _u('dev_entities_merge'); ?>">Merge tool</a>
</div>
*/
?>
<div class="row">

	<div class="col-lg-8 col-lg-offset-2">

		<h1><?php _t('installdb_title', DOMAIN_SETUP, t('app_name')); ?></h1>
		<p class="lead"><?php echo text2HTML(t('installdb_description', DOMAIN_SETUP, array('APP_NAME'=>t('app_name')))); ?></p>
		<?php
// 		displayReportsHTML();
		$this->display('reports-bootstrap3');
		
		if( !empty($resultingSQL) ) {
		// 	echo '<div>'.$resultingSQL.'</div>';
			if( !empty($requireEntityValidation) ) {
				?>
		<h3><?php _t('generated_sqlqueries', DOMAIN_SETUP); ?></h3>
		<div class="sql_query"><?php echo $resultingSQL; ?></div>
		<form method="POST"><?php echo $FORM_TOKEN; ?>
		<?php
			foreach( POST('entities') as $entityClass => $on ) {
				echo htmlHidden('entities/'.$entityClass);
			}
			?>
			<button type="submit" class="btn btn-primary" name="submitGenerateSQL[<?php echo OUTPUT_APPLY; ?>]"><?php _t('apply'); ?></button>
		</form>
		<?php
			}
		}
		?>
		
		<form method="POST" role="form" class="form-horizontal">
		<?php echo $FORM_TOKEN; ?>

		<h2><?php _t('foundentities', DOMAIN_SETUP); ?></h2>
		<button class="btn btn-default btn-sm" type="button" onclick="$('.entitycb').prop('checked', true);"><i class="fa fa-fw fa-check-square-o"></i> <?php _t('checkall'); ?></button>
		<button class="btn btn-default btn-sm" type="button" onclick="$('.entitycb').prop('checked', false);"><i class="fa fa-fw fa-square-o"></i> <?php _t('uncheckall'); ?></button>
		
		<ul class="list-group">
		<?php
		foreach( PermanentEntity::listKnownEntities() as $entityClass ) {
			echo '
		<li class="list-group-item">
			<label class="wf">
				<input class="entitycb" type="checkbox" name="entities['.$entityClass.']"'.(!isPOST() || isPOST('entities/'.$entityClass) ? ' checked' : '').'
				title="'.$entityClass.'"/> '.$entityClass.'
			</label>
		</li>';
		}
		?>
		</ul>
		
		<p>
			<button title="DO IT ! JUST DO IT !" type="submit" class="btn btn-lg <?php echo !$allowContinue && empty($resultingSQL) ? 'btn-primary' : 'btn-default' ; ?>" name="submitGenerateSQL[<?php echo OUTPUT_DISPLAY; ?>]">
				<?php _t('check_database', DOMAIN_SETUP); ?>
			</button>
			<?php
			if( $allowContinue ) {
				?>
			<a class="btn btn-lg btn-primary" href="<?php _u('setup_installfixtures'); ?>" role="button"><?php _t('continue', DOMAIN_SETUP); ?></a>
			<?php
			}
			?>
		</p>

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
