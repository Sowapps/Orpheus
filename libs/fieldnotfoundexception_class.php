<?php
/* fieldnotfoundexception_class.php -> Class Field Not Found Exception
 * Fichier pour la classe d'exception Champs non trouvé.
 *
 * Auteur: Florent HAZARD.
 * Révision: 2
 * Creation: 15/02/2011
*/

class FieldNotFoundException extends Exception {
	
	//Attributs
	private $fieldname;
	
	//Methodes
	public function __construct($fieldname) {
		parent::__construct('fieldNotFound', 1001);
		$this->fieldname = (string) $fieldname;
	}
	
	public function getFieldName() {
		return $this->fieldname;
	}
}
