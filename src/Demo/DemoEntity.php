<?php

namespace Demo;

use Orpheus\EntityDescriptor\PermanentEntity;

/** A sample Demo Entity class
 *
 * Example of how to use the permanent entity.
 */
class DemoEntity extends PermanentEntity {
	
	//Attributes
	protected static string $table = 'demoentity';
	
	// Final attributes
	protected static array $fields = [];
	
	protected static $validator = null;
	
	protected static string $domain;
	
}

DemoEntity::init();
