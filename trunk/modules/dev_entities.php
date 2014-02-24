<?php
using('entitydescriptor.entitydescriptor');
using('entitydescriptor.sqlgenerator_mysql');
using('entitydescriptor.langgenerator');

define('OUTPUT_APPLY',		1);
define('OUTPUT_DISPLAY',	2);
define('OUTPUT_DLRAW',		3);
define('OUTPUT_DLZIP',		4);
//define('OUTPUT_SQLDOWNLOAD');


try {
if( isPOST('entities') && is_array(POST('entities')) ) {
	if( isPOST('submitGenerateSQL') ) {
		$output		= POST('sql_output')==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
		$generator	= new SQLGenerator_MySQL;
		$result		= '';
		foreach( POST('entities') as $entityName => $on ) {
			$result	.= $generator->matchEntity(EntityDescriptor::load($entityName));
		}
		if( empty($result) ) {
			throw new UserException('No changes');
		}
		if( $output==OUTPUT_APPLY ) {
			pdo_query($result, PDOEXEC);
			reportSuccess('successSQLApply');
			
		} else {
			echo '<pre style="tab-size: 4; -moz-tab-size: 4;">'.$result.'</pre>';
		}
	} else
	if( isPOST('submitGenerateVE') ) {
		$output		= POST('ve_output')==OUTPUT_DLRAW ? OUTPUT_DLRAW : OUTPUT_DISPLAY;
		$generator	= new LangGenerator;
		$result		= '';
		foreach( POST('entities') as $entityName => $on ) {
			$result	.= "\n\n\t$entityName.ini\n";
			foreach( $generator->getRows(EntityDescriptor::load($entityName)) as $k => $exc ) {
				/* @var $exc InvalidFieldException */
				$exc->setDomain('entity_model');
				$exc->removeArgs();//Does not replace arguments
				// Tab size is 4 (as my editor's config)
				$result .= $k.str_repeat("\t", 11-floor(strlen($k)/4)).'= "'.$exc->getText()."\"\n";
			}
			//paymentsbyexchangeaccepted_aboveMaxValue\t\t\t
		}
		if( $output==OUTPUT_APPLY ) {
// 			reportSuccess('Output not implemented !');
			reportError('Output not implemented !');
			
		} else {
			echo '<pre style="tab-size: 4; -moz-tab-size: 4;">'.$result.'</pre>';
		}
	}
}
} catch( UserException $e ) {
	reportError($e);
}
/*

		<p>This tool allows you to generate SQL source for MySQL.</p>
 */
?>
<form method="POST" role="form" class="form-horizontal">

<div class="row">
	<div class="col-lg-6">
		<h2>Entities found</h2>
		<button class="btn btn-default btn-sm" type="button" onclick="$('.entitycb').prop('checked', true);">Check all</button>
		<?php 
		$entities = cleanscandir(pathOf(CONFDIR.ENTITY_DESCRIPTOR_CONFIG_PATH));
		foreach( $entities as $filename ) {
			$pi = pathinfo($filename);
			if( $pi['extension'] != 'yaml' ) { continue; }
			echo '
		<div class="checkbox"><label><input class="entitycb" type="checkbox" name="entities['.$pi['filename'].']"'.(isPOST('entities/'.$pi['filename']) ? ' checked' : '').'/> '.$pi['filename'].'</label></div>';
		}
		?>
		
		<div class="form-group">
			<label class="col-sm-4 control-label">SQL</label>
			<div class="col-sm-3"><select name="sql_output" class="form-control">
				<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
				<option value="<?php echo OUTPUT_APPLY; ?>">Apply</option>
			</select></div>
			<button type="submit" class="btn btn-default" name="submitGenerateSQL">Generate</button>
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