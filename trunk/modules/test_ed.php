<?php

$ed = new EntityDescriptor('users');

$values = array(
	array('a_number', '125'),
	array('a_number', '-125'),
	array('a_string2', 'short string'),
	array('a_string2', 'This is a test for a very long string'),
);
foreach( $values as $a ) {
	try {
		text('Value: '.$a[1]);
		$ed->validateFieldValue($a[0], $a[1]);
		text('OK.');
	} catch( InvalidFieldException $e ) {
		text($e->getMessage());
	} catch( Exception $e ) {
		text($e);
	}
}