<?php

class PasswordGenerator {

	protected $primarySets	= array();

	const CHAR_ALPHA_LOWER	= 1;
	const CHAR_ALPHA_UPPER	= 2;
	const CHAR_DIGIT		= 4;
	const CHAR_SYMBOL		= 8;
	const CHAR_ALPHA		= 3;//self::CHAR_ALPHA_LOWER|self::CHAR_ALPHA_UPPER;
	const CHAR_ALPHADIGIT	= 7;//self::CHAR_ALPHA|self::CHAR_DIGIT;
	const CHAR_ALL			= 15;//self::CHAR_ALPHADIGIT|self::CHAR_SYMBOL;

	public function __construct() {
		$this->setPrimarySet(self::CHAR_ALPHA_LOWER, 'abcdefghijklmnopqrstuvwxyz');
		$this->setPrimarySet(self::CHAR_ALPHA_UPPER, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
		$this->setPrimarySet(self::CHAR_DIGIT, '0123456789');
		$this->setPrimarySet(self::CHAR_SYMBOL, '!@#$%&*?');
	}

	/**
	 * Set a known set of character by flag
	 *
	 * @param int $flag
	 * @param string $characters
	 */
	public function setPrimarySet($flag, $characters) {
		$this->primarySets[$flag]	= $characters;
		return $this;
	}

	/**
	 * Generate a random complex password
	 *
	 * @param int $length
	 * @param int $availables
	 * @param int[] $forced
	 * @return string
	 */
	public function generate($length=10, $availables=self::CHAR_ALPHADIGIT, array $tokens=array(self::CHAR_ALPHA, self::CHAR_DIGIT)) {
		$tokens	= array_pad($tokens, $length, $availables);
		shuffle($tokens);
		$password	= '';
		foreach( $tokens as $token ) {
			$tokenChars = '';
			foreach( $this->primarySets as $flag => $chars ) {
				if( bintest($token, $flag) ) {
					$tokenChars .= $chars;
				}
			}
			$password .= $tokenChars[mt_rand(0, strlen($tokenChars)-1)];
		}
		return $password;
	}
}
