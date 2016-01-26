<?php
/**
 * The Slug Generator class
 * 
 * This class generate slug
*/
class SlugGenerator {
	
	/**
	 * Should remove space instead of replacing them
	 * 
	 * @var boolean $removeSpaces
	 */
	protected $removeSpaces		= false;
	
	const CAMELCASE	= 1<<0;
	const LOWERCAMELCASE	= self::CAMELCASE;
	const UPPERCAMELCASE	= self::CAMELCASE | 1<<1;

	/**
	 * How to process word case
	 *
	 * @var boolean $caseProcessing
	 */
	protected $caseProcessing	= self::UPPERCAMELCASE;
	
	public function format($string) {
		
		if( $this->isRemovingSpaces() ) {
			$string	= str_replace(' ', '', $string);
		}
		
		$string	= strtr(ucwords(str_replace('&', 'and', strtolower($string))), " .'\"", '----');
		if( isset($this->caseProcessing) ) {
			if( $this->isCamelCaseProcessing() ) {
				if( $this->caseProcessing == self::LOWERCAMELCASE ) {
					$string = lcfirst($string);
					// } else
					// if( $case == UPPERCAMELCASE ) {
					// $string = ucfirst($string);
				}
			}
		}
		return convertSpecialChars($string);
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isRemovingSpaces() {
		return $this->removeSpaces;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function getRemoveSpaces() {
		return $this->removeSpaces;
	}
	
	/**
	 * 
	 * @param boolean $removeSpaces
	 * @return SlugGenerator
	 */
	public function setRemoveSpaces($removeSpaces=true) {
		$this->removeSpaces = $removeSpaces;
		return $this;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isCamelCaseProcessing() {
		return bintest($this->caseProcessing, CAMELCASE);
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getCaseProcessing() {
		return $this->caseProcessing;
	}
	
	/**
	 * 
	 * @param int $caseProcessing
	 * @return SlugGenerator
	 */
	public function setCaseProcessing($caseProcessing) {
		$this->caseProcessing = $caseProcessing;
		return $this;
	}
	
	
	
}
