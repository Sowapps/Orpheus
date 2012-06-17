<?php
/* unknownkeyexception_class.php -> Class Unknown Key Exception
 * Fichier pour la classe d'exception clé inconnue (inexistante).
 *
 * Auteur: Florent HAZARD.
 * Révision: 1
 * Last edition: 19/08/2011
 * Creation: 19/08/2011
*/

class UnknownKeyException extends Exception {
	
	//Attributs
	private $key;
	
	//Methodes
	public function __construct($m, $key) {
		parent::__construct($m, 1002);
		$this->key = (string) $key;
	}
	
	public function getKey() {
		return $this->key;
	}
}
