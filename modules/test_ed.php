<?php

$ed = new EntityDescriptor('users');

$values = array('125', '-125');
foreach( $values as $value ) {
	$ed->validateFieldValue('a_number', $value);
}