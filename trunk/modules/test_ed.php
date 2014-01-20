<?php

$ed = new EntityDescriptor('users');

$values = array('125', '-125');
foreach( $values as $value ) {
	try {
		text('Value: '.$value);
		$ed->validateFieldValue('a_number', $value);
		text('OK.');
	} catch( InvalidFieldException $e ) {
		text($e->getMessage());
	} catch( Exception $e ) {
		text($e);
	}
}