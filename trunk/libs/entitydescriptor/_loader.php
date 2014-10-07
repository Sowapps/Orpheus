<?php
/* Loader File for the Entity Descriptor sources
 */

addAutoload('EntityDescriptor',					'entitydescriptor/entitydescriptor');
addAutoload('TypeDescriptor',					'entitydescriptor/typedescriptor');
addAutoload('FieldDescriptor',					'entitydescriptor/fielddescriptor');

addAutoload('PermanentEntity',					'entitydescriptor/permanententity');

// Form Things

/** 
 * Get the field descriptor from a field path
 * @param string $fieldPath
 * @param string $class
 * @return FieldDescriptor
 */
function getField($fieldPath, $class=null) {
	if( $class === NULL ) {
		$fieldPathArr	= explode('/', $fieldPath);
		$class			= $fieldPathArr[0];
	}
	if( !class_exists($class, 1) || !in_array('PermanentEntity', class_parents($class)) ) {
		return null;
	}
	return $class::getField($fieldPathArr[count($fieldPathArr)-1]);
}