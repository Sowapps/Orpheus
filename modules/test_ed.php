<?php

function getEnumValues() {
	return array('first', 'second', 'third');
}

$ed = new EntityDescriptor('users');

// Validator
$values = array(
	array('a_number',	'125'),
	array('a_number',	'-125'),
	array('a_string2',	'short string'),
	array('a_string2',	'This is a test for a very long string'),
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
	$TYPE = EntityDescriptor::getType($field->type);
	$cType = '';
	if( $TYPE->knowType('string') ) {
		$max = $TYPE->knowType('password') ? 128 : $field->args->max;
		if( $field->args->max < 256 ) {
			$cType = "VARCHAR({$field->args->max})";
		} else
		if( $field->args->max < 65536 ) {
			$cType = "TEXT";
		} else
		if( $field->args->max < 16777216 ) {
			$cType = "MEDIUMTEXT";
		} else {
			$cType = "LONGTEXT";
		}
	}
	
	$columns .= ($i ? ", \n" : '').$fName.' '.$cType.($field->nullable ? ' NULL' : ' NOT NULL').($fName=='id' ? ' AUTO_INCREMENT PRIMARY KEY' : '');
	$i++;
}
if( !empty($columns) ) {
	$query = <<<EOF
CREATE TABLE {$ed->getName()} (
{$columns}
) ENGINE=MYISAM CHARACTER SET utf8;
EOF;
}