<?php

$ed = new EntityDescriptor('users');

$ed->validateFieldValue('a_number', '125');
$ed->validateFieldValue('a_number', '-125');