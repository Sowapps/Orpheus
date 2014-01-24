<?php
using('entitydescriptor.entitydescriptor');

define('OUTPUT_APPLY', 1);
define('OUTPUT_DISPLAY', 2);
//define('OUTPUT_SQLDOWNLOAD');

// MySQL Generator
function mysqlColumnInfosFromField($field) {
	$TYPE = EntityDescriptor::getType($field->type);
	$cType = '';
	if( $TYPE instanceof TypeString ) {
		$max = $TYPE instanceof TypePassword ? 128 : $field->args->max;
		if( $max < 256 ) {
			$cType = "VARCHAR({$field->args->max})";
		} else
		if( $max < 65536 ) {
			$cType = "TEXT";
		} else
		if( $max < 16777216 ) {
			$cType = "MEDIUMTEXT";
		} else {
			$cType = "LONGTEXT";
		}
	} else
	if( $TYPE instanceof TypeNumber ) {
		if( !isset($field->args->max) ) {
			text('Issue with '.$fName);
			text($field->args);
		}
		$dc = strlen((int) $field->args->max);
		if( !$field->args->decimals ) {
			$max		= (int) $field->args->max;
			$unsigned	= $field->args->min >= 0 ? 1 : 0;
			$f			= 1+1*$unsigned;
			if( $max < 128*$f ) {
				$cType	= "TINYINT";
			} else
			if( $max < 32768*$f ) {
				$cType	= "SMALLINT";
			} else
			if( $max < 8388608*$f ) {
				$cType	= "MEDIUMINT";
			} else
			if( $max < 2147483648*$f ) {
				$cType	= "INT";
			} else {
				$cType	= "BIGINT";
			}
			$cType .= '('.strlen($max).')';
			
		} else {
			$dc += $field->args->decimals;
			if( $dc+$field->args->decimals < 7 ) {// Approx accurate to 7 decimals
				$cType = "FLOAT({$dc}, {$field->args->decimals})";
			} else {// Approx accurate to 15 decimals
				$cType = "DOUBLE";
			}
			$cType .= "({$dc}, {$field->args->decimals})";
		}
	} else
	if( $TYPE instanceof TypeDate ) {
		$cType = 'DATE';
	} else
	if( $TYPE instanceof TypeDatetime ) {
		$cType = 'DATETIME';
	} else {
		throw new UserException('Type of '.$fName.' ('.$TYPE->getName().') not found');
// 		return null;
	}
	//, 'primaryKey'=>false, 'autoIncrement'=>false
	$r = array('name'=>$field->name, 'type'=>$cType, 'nullable'=>$field->nullable);
	$r['autoIncrement'] = $r['primary_key'] = ($field->name=='id');
	//, 'key'=>'', 'extra'=>''
// 	if( $field->name=='id' ) {
// 		$r['primary_key'] = $r['autoIncrement'] = true;
// 	}
	return $r;
}

function mysqlColumnDefinition($field) {
	$field = (object) $field;
	return SQLAdapter::doEscapeIdentifier($field->name).' '.$field->type.
		($field->nullable ? ' NULL' : ' NOT NULL').
		(!empty($field->autoIncrement) ? ' AUTO_INCREMENT' : '').(!empty($field->primaryKey) ? ' PRIMARY KEY' : '');
}

function mysqlTableMatch($ed) {
	$query = '';
	if( $columns=pdo_query('SHOW COLUMNS FROM '.SQLAdapter::doEscapeIdentifier($ed->getName()), PDOFETCHALL|PDOERROR_MINOR) ) {
		$fields = $ed->getFields();
		$alter = '';
		foreach( $columns as $cc ) {
			$cc = (object) $cc;
			$cf = array(
				'name'=>$cc->Field, 'type'=>strtoupper($cc->Type), 'nullable'=>$cc->Null=='YES',
				'primaryKey'=>$cc->Key=='PRI', 'autoIncrement'=>strpos($cc->Extra, 'auto_increment')!==false);
			if( isset($fields[$cf['name']]) ) {
				$f = mysqlColumnInfosFromField($fields[$cf['name']]);
				unset($fields[$cf['name']]);
				// Current definition is different from former
				if( $f!=$cf ) {
					text('Different columns');
					text($f);
					text($cf);
					$alter .= (!empty($alter) ? ", \n" : '')."\t CHANGE COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']).' '.mysqlColumnDefinition($f);
				}
			} else {
				// Remove column
				$alter .= (!empty($alter) ? ", \n" : '')."\t DROP COLUMN ".SQLAdapter::doEscapeIdentifier($cf['name']);
			}
		}
		foreach( $fields as $f ) {
			$alter .= (!empty($alter) ? ", \n" : '')."\t ADD COLUMN ".mysqlColumnDefinition($f);
		}
		if( empty($alter) ) {
			return null;
		}
		return 'ALTER TABLE '.SQLAdapter::doEscapeIdentifier($ed->getName())."\n".$alter.';';
	} else {
		return mysqlCreate($ed);
	}
}

function mysqlCreate($ed) {
	$columns = '';
	text($ed);
	foreach( $ed->getFields() as $field ) {
		text($field);
		$columns .= (!empty($columns) ? ", \n" : '')."\t".mysqlColumnDefinition(mysqlColumnInfosFromField($field));
	}
	if( empty($columns) ) {
		throw new UserException('No columns');
		return null;
	}
	return '
CREATE TABLE IF NOT EXISTS '.SQLAdapter::doEscapeIdentifier($ed->getName()).' (
'.$columns.'
) ENGINE=MYISAM CHARACTER SET utf8;';
}

// text(isPOST('submitGenerateSQL'));
// text(isPOST('entities'));
// text(isPOST('entities') && is_array(POST('entities')));
if( isPOST('submitGenerateSQL') && isPOST('entities') && is_array(POST('entities')) ) {
	$output = isPOST('output') && POST('output')==OUTPUT_APPLY ? OUTPUT_APPLY : OUTPUT_DISPLAY;
// 	text($output);
	foreach( POST('entities') as $entityName => $on ) {
// 		text("- $entityName");
		try {
// 			$query = mysqlCreate(EntityDescriptor::load($entityName));
			$query = mysqlTableMatch(EntityDescriptor::load($entityName));
			if( empty($query) ) {
				throw new UserException('No changes');
// 				throw new UserException('Empty query');
			}
			if( $output==OUTPUT_APPLY ) {
				pdo_query($query, PDOEXEC);
				reportSuccess('Database contents applied successfully !');
				
			} else {
				echo '<pre>'.$query.'</pre>';
			}
		} catch( UserException $e ) {
			reportError($e);
		}
	}
}
?>
<form method="POST">
<?php displayReportsHTML(); ?>
<p>This tool allows you to generate SQL source for MySQL.</p>
<h4>Entities found</h4>
<?php 
$entities = cleanscandir(pathOf(CONFDIR.ENTITY_DESCRIPTOR_CONFIG_PATH));
foreach( $entities as $filename ) {
	$pi = pathinfo($filename);
	if( $pi['extension'] != 'yaml' ) { continue; }
	echo '
<label>'.$pi['filename'].'</label><input type="checkbox" name="entities['.$pi['filename'].']" '.(isPOST('entities/'.$pi['filename']) ? ' checked' : '').'/><br />';
}
?>
<label>Output</label><select name="output">
	<option value="<?php echo OUTPUT_DISPLAY; ?>" selected>Display</option>
	<option value="<?php echo OUTPUT_APPLY; ?>">Apply</option>
</select><br />
<button type="submit" name="submitGenerateSQL">Generate</button>
</form>
<style>
<!--
label {
	width: 200px;
}
-->
</style>
