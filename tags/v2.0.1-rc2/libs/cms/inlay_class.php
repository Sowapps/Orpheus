<?php
//! The inlay class for contents blocks
/*!
 *
 * Require core and publisher plugin.
 */

class Inlay extends AbstractPublication {

	//Attributes
	protected static $table = 'inlays';
	protected static $status = array('approved'=>array('rejected'), 'rejected'=>array('approved'));
	
}