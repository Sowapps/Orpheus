<?php
/** The inlay class for contents blocks

 *
 * Require core and publisher plugin.
 */

class InlayEntity extends PermanentEntity {
	
	//Attributes
	protected static $table		= 'inlay_entity';
	
	// Final attributes
	protected static $fields	= null;
	protected static $validator	= null;
	protected static $domain	= null;
	
	
}
InlayEntity::init();
