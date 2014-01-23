<?php
using('entitydescriptor.entitydescriptor');

define('OUTPUT_APPLY', 1);
define('OUTPUT_DISPLAY', 2);
//define('OUTPUT_SQLDOWNLOAD');

// MySQL Generator
function generateSQLCreate($ed) {
	$columns = '';
	$i = 0;
	foreach( $ed->getFields() as $fName => $field ) {
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
			return null;
		}
// 		text($fName.' => '.($field->nullable ? ' NULLABLE' : ' NOTNULL'));
		$columns .= ($i ? ", \n" : '')."\t".SQLAdapter::doEscapeIdentifier($fName).' '.$cType.($field->nullable ? ' NULL' : ' NOT NULL').($fName=='id' ? ' AUTO_INCREMENT PRIMARY KEY' : '');
		$i++;
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
			$query = generateSQLCreate(EntityDescriptor::load($entityName));
			if( empty($query) ) {
				throw new UserException('Empty query');
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
<label>'.$pi['filename'].'</label><input type="checkbox" name="entities['.$pi['filename'].']"/><br />';
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
