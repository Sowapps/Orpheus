<?php

function getEnumValues() {
	return array('first', 'second', 'third');
}

$ed = new EntityDescriptor('entity_tests');

// Validator
$values = array(
	array('a_number',	'125'),
	array('a_number',	'-125'),
	array('a_string',	'short string'),
	array('a_string',	'This is a test for a very long string'),
	array('a_date',		'25/12/1987'),
	array('a_date',		'25/12/1987 12:50:12'),
	array('a_datetime',	'25/12/1987'),
	array('a_datetime',	'25/12/1987 12:50:12'),
	array('an_email',	'test@domain.com'),
	array('an_email',	'128.14967.16'),
	array('a_password',	'test'),
	array('a_password',	'password'),
	array('a_phone',	'0123456789'),
	array('a_phone',	'+331.23.45.67.89'),
	array('a_phone',	'invalid number'),
	array('an_url',		'http://zerofraisdecourtage.fr'),
	array('an_url',		'378954156546'),
	array('an_ip',		'127.0.0.1'),
	array('a_ref',		'9874'),
	array('an_enum',	'second'),
	array('an_enum',	'zero'),
);
foreach( $values as $a ) {
	try {
		echo $a[0].' => '.$a[1].' : ';
		$ed->validateFieldValue($a[0], $a[1]);
		text('OK ('.$a[1].').');
	} catch( InvalidFieldException $e ) {
		text($e->getMessage());
	} catch( Exception $e ) {
		text($e);
	}
}


// MySQL Generator
$columns = '';
$i = 0;
foreach( $ed->getFields() as $fName => $field ) {
	text('$fName : '.$fName);
	text($field);
	if( !is_object($field) ) {
		continue;
	}
	$TYPE = EntityDescriptor::getType($field->type);
	$cType = '';
	if( $TYPE->knowType('string') ) {
		$max = $TYPE->knowType('password') ? 128 : $field->args->max;
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
	if( $TYPE->knowType('number') ) {
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
	if( $TYPE->knowType('date') ) {
		$cType = 'DATE';
	} else
	if( $TYPE->knowType('datetime') ) {
		$cType = 'DATETIME';
	} else {
		text('Type of '.$fName.' ('.$TYPE->getName().') not found.');
	}
	
	$columns .= ($i ? ", \n" : '')."\t".$fName.' '.$cType.($field->nullable ? ' NULL' : ' NOT NULL').($fName=='id' ? ' AUTO_INCREMENT PRIMARY KEY' : '');
	$i++;
}
if( !empty($columns) ) {
	$query = <<<EOF
CREATE TABLE {$ed->getName()} (
{$columns}
) ENGINE=MYISAM CHARACTER SET utf8;
EOF;
	echo '<pre>'.$query.'</pre>';
}