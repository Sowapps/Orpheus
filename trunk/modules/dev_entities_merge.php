<?php
using('entitydescriptor.entitydescriptor');

$FORM_TOKEN	= new FormToken();

try {
	debug('POST()', POST());
// 	isPOST() && $FORM_TOKEN->validateForm();
	if( isPOST('submitAnalyze') ) {
// 		$entity	= POST('merge/entity');
		$COMPARE	= POST('compare');
	} else
	if( isPOST('submitMerge') ) {
// 		$entity	= POST('merge/entity');
		$class	= entityClass(POST('merge/entity'));
		$from	= $class::load(POST('merge/from'));
		$to		= $class::load(POST('merge/to'));
		foreach( POST('confirm') as $field => $value ) {
			if( $value === '' ) {
				$value	= NULL;
			}
			$to->setValue($field, $value);
		}
		
		foreach( POST('transfer') as $entity => $entRows ) {
			$entityClass	= entityClass($entity);
			foreach( $entRows as $rowID => $fields ) {
				/* @var $row PermanentEntity */
				$row	= $entityClass::load($rowID);
				foreach( $fields as $field => $val ) {
					if( !$val ) {
						debug('Remove row', $row);
// 						$row->remove();
						break;
					}
					text($row->getEntity().'#'.$row->id().' - '.$field.' => '.$to->id());
					$row->{$field} = $to->id();
				}
				$row->save();// Check if valid
			}
		}
		/*
		transfer['.$entity.']['.$fromRow->id().']['.$rfields['id'].']" type="hidden" value="0"/>
		<input name="transfer['.$entity.']['.$fromRow->id().']['.$rfields['id'].']
		*/
		debug('Remove from', $from);
		reportSuccess('successEntityMerge');
		$from->remove();
	}
	
} catch( UserException $e ) {
	reportError($e);
} catch( Exception $e ) {
	debug($e);
}

function compareTable($entity, $from, $to) {
	$class	= entityClass($entity);
	$from	= $class::load($from);
	$to		= $class::load($to);
	$fromData	= $from->all;
	$toData		= $to->all;
	?>
<table class="table">	
<thead>
	<tr>
		<th>Field</th>
		<th>From</th>
		<th>Into</th>
		<th>Finally</th>
	</tr>
</thead>
<tbody>
	<?php
	$desc	= EntityDescriptor::load($entity);
	foreach( $desc->getFieldsName() as $field ) {
		$fromValue	= &$fromData[$field];
		$toValue	= &$toData[$field];
		?>
	<tr>
		<td><?php echo $field; ?></td>
		<td><?php echo $fromValue; ?></td>
		<td><?php echo $toValue; ?></td>
		<td><?php echo $field==EntityDescriptor::IDFIELD ?
			$toValue :
			'<input name="confirm['.$field.']" value="'.($toValue===NULL ? $fromValue : $toValue).'"/>';
		?></td>
	</tr>
	<?php
	}
	?>
</tbody>
</table>
<?php
}

if( !empty($COMPARE) ) {
	?>
<div class="row">
	<div class="col-lg-12">
<h3>Compare and validate the changes</h3>
<p class="bg-warning p15">
Warning ! this operation will remove the " From " instance (#<?php echo $COMPARE['from']; ?>) of the entity " <?php echo $COMPARE['entity']; ?> ". 
The data are provided as " raw ", this means if you mess up, you could corrupt your database.<br />
Selected related entries will be linked to the " To " instance (#<?php echo $COMPARE['to']; ?>). 
Non selected entries will be removed. If an entry is not preselected it means there is a duplicate, <i class="fa fa-warning"></i> means this is a <i title="All ref fields match the From and the To instance">full matching duplicate</i>, you can check it to force to transfer.
</p>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<form role="form" method="POST">
			<input type="hidden" name="compare[entity]" value="<?php echo $COMPARE['entity']; ?>"/>
			<input type="hidden" name="compare[from]" value="<?php echo $COMPARE['to']; ?>"/>
			<input type="hidden" name="compare[to]" value="<?php echo $COMPARE['from']; ?>"/>
			<button type="submit" name="submitAnalyze" class="btn btn-default">Compare</button>
		</form>
	</div>
</div>

<form class="form-horizontal" role="form" method="POST"><?php echo $FORM_TOKEN; ?>
<div class="row">
	<div class="col-lg-6">
		<input type="hidden" name="merge[entity]" value="<?php echo $COMPARE['entity']; ?>"/>
		<input type="hidden" name="merge[from]" value="<?php echo $COMPARE['from']; ?>"/>
		<input type="hidden" name="merge[to]" value="<?php echo $COMPARE['to']; ?>"/>
	<?php
	compareTable($COMPARE['entity'], $COMPARE['from'], $COMPARE['to']);
	?>
		<div class="row">
			<div class="col-xs-3 col-xs-offset-9">
				<button type="submit" name="submitMerge" class="btn btn-default">Validate merge <i class="fa fa-code-fork"></i></button>
			</div>
		</div>
	</div>
	<div class="col-lg-6">
	<?php
	try {
		foreach( EntityDescriptor::getAllEntities() as $entity ) {
			$entityDesc	= EntityDescriptor::load($entity);
			$entFields	= $entityDesc->getFields();
			$entRefFields	= array();
			$allRefFields	= array();			
			foreach( $entFields as $field => $fieldDesc ) {
				if( $fieldDesc->type!='ref' || $field===EntityDescriptor::IDFIELD ) { continue; }
				$refEntity	= $fieldDesc->arg('entity');
				if( empty($refEntity) ) { continue; }
				$refField	= null;
				$allRefFields[]	= $field;
				if( $refEntity[0] == '$' ) {
					$entField		= substr($refEntity, 1);
					if( !isset($entFields[$entField]) ) {
						throw new Exception('In entity '.$entity.', the field " '.$field.' " uses a reference to the unknown field " '.$entField.' ".');
					}
					if( $entFields[$entField]->type != 'enum' ) {
						throw new Exception('In entity '.$entity.', the field " '.$field.' " uses a reference to the field " '.$entField.' ", this one should be an ENUM.');
					}
// 					$refField = array('id'=>$field, 'entity'=>$entField);
// 					$allRefFields[]	= $refField;
					if( !in_array($entity, call_user_func($entFields[$entField]->arg('source'))) ) { continue; }
					$entRefFields[]	= array('id'=>$field, 'entity'=>$entField);
// 					if( !in_array($entity, call_user_func($entFields[$entField]->arg('source'))) ) {
// 						unset($refField);
// 					}
					
				} else if( $refEntity==$COMPARE['entity'] ) {
					$entRefFields[]	= array('id'=>$field);
// 					$refField	= array('id'=>$field);
// 					$allRefFields[]	= $refField;
				}
// 				if( $refField ) {
// 					$entRefFields[]	= $refField;
// 				}
			}
			if( !empty($entRefFields) ) {
				// Entity -> Class
				$class	= entityClass($entity);
?>
<h4><? echo $entity; ?></h4>
<table class="table">
<thead>
	<tr>
		<th class="w60">ID</th>
		<th>Label</th>
		<th class="w300">Field</th>
		<th class="w100">Transfer ?</th>
	</tr>
</thead>
<tbody>
<?php
$row	= null;
foreach( $entRefFields as $rfields ) {
	try {
		$entWC	= isset($rfields['entity']) ? ' AND '.$class::escapeIdentifier($rfields['entity']).' LIKE '.$class::formatValue($COMPARE['entity']) : '';
		$fromRows	= $class::get($class::escapeIdentifier($rfields['id']).'='.$class::formatValue($COMPARE['from']).$entWC);
		$toRows		= $class::get($class::escapeIdentifier($rfields['id']).'='.$class::formatValue($COMPARE['to']).$entWC);
// 		debug('$toRows', $toRows);
		$duplicates	= implode(', ', $toRows);
		foreach( $fromRows as $fromRow ) {
			$fromRefs	= '';
			foreach( $allRefFields as $afield ) {
				if( $afield == $rfields['id'] ) { continue; }
				$fromRefs	.= $fromRow->{$afield}.',';
			}
			$fullMatchDuplicate	= false;
			foreach( $toRows as $toRow ) {
				$toRefs	= '';
				foreach( $allRefFields as $afield ) {
					if( $afield == $rfields['id'] ) { continue; }
					$toRefs	.= $toRow->{$afield}.',';
				}
				if( !$fullMatchDuplicate && $toRefs == $fromRefs ) {
					$fullMatchDuplicate = true;
				}
			}
			echo '
	<tr>
		<td>'.$fromRow->id().'</td>
		<td title="'.implode(', ', array_peer($fromRow->all)).'">'.$fromRow.'</td>
		<td>'.$rfields['id'].(isset($rfields['entity']) ? ' / '.$rfields['entity'] : '').'</td>
		<td>
			<input name="transfer['.$entity.']['.$fromRow->id().']['.$rfields['id'].']" type="hidden" value="0"/>
			<input name="transfer['.$entity.']['.$fromRow->id().']['.$rfields['id'].']" type="checkbox"'.($duplicates ? ' title="Duplicates: '.$duplicates.'"' : ' checked').'/>'.($fullMatchDuplicate ? '<i class="fa fa-warning ml5" title="Full matching duplicate"></i>' : '').'
		</td>
	</tr>';
		}
	} catch( Exception $e ) {
		echo '<tr><td colspan="99">';
		debug('Exception', $e);
		echo '</td></tr>';
	}
}
if( empty($fromRow) ) {
	?><tr><td colspan="99">No entry.</td></tr><?php
}
unset($entRefFields, $fromRows, $toRows, $duplicates, $fromRow, $toRow);
?>
</tbody>
</table>
<?php
			}
		}
	} catch( Exception $e ) {
		debug('Exception', $e);
	}
?>
		<div class="row">
			<div class="col-xs-3 col-xs-offset-9">
				<button type="submit" name="submitMerge" class="btn btn-default">Validate merge <i class="fa fa-code-fork"></i></button>
			</div>
		</div>
	</div>
</div>
</form>
<?php
}
?>

<form class="form-horizontal" role="form" method="POST"><?php echo $FORM_TOKEN; ?>
<div class="row">
	<div class="col-lg-6">
	
<h3>Merge tool</h3>
<p class="bg-info p15">
The merge tool allow you to merge 2 instances of one entity, it merges all its fields' value and it follows all its relation to merge them to finally use only the " into " entity.<br />
First, choose the instances to merge, then choose the applying changes and finally we merge it.
</p>
		
<div class="form-group">
	<label class="col-xs-1 control-label">Merge</label>
	<div class="col-xs-3">
		<select class="select form-control" name="compare[entity]">
<?php
$selected	= POST('compare/entity');
foreach( EntityDescriptor::getAllEntities() as $entity ) {
	echo '
			<option value="'.$entity.'"'.($entity==$selected ? ' selected' : '').'>'.$entity.'</option>';
}
?>
		</select>
	</div>
	<label class="col-xs-1 control-label">from</label>
	<div class="col-xs-2">
		<div class="input-group">
			<span class="input-group-addon">#</span>
			<input name="compare[from]" type="text" class="form-control" placeholder="ID" value="<?php echo POST('compare/from'); ?>" >
		</div>
	</div>
	<label class="col-xs-1 control-label">into</label>
	<div class="col-xs-2">
		<div class="input-group">
			<span class="input-group-addon">#</span>
			<input name="compare[to]" type="text" class="form-control" placeholder="ID" value="<?php echo POST('compare/to'); ?>">
		</div>
	</div>
	<div class="col-xs-1">
		<button type="submit" name="submitAnalyze" class="btn btn-default">Compare</button>
	</div>
</div>
		
	</div>
</div>
</form>