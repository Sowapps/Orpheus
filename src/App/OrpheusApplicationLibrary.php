<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App;

use App\Entity\User;
use App\File\File;
use Orpheus\Core\AbstractOrpheusLibrary;
use Orpheus\EntityDescriptor\Entity\PermanentEntity;

class OrpheusApplicationLibrary extends AbstractOrpheusLibrary {
	
	public function start(): void {
		// Declare entities (Remove it if you are not using any entity)
		PermanentEntity::registerEntity(File::class);
		PermanentEntity::registerEntity(User::class);
		
		User::setUserClass();
	}
	
}
