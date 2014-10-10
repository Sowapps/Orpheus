<?php
//! The Form Token class
/*!
	This class is limit the use of form data to only one shot.
*/
class FormToken {

	protected $name;
	protected $maxToken;
	protected $maxUsage;
	
	protected $lastToken;
	
	const SESSION_KEY			= 'FORM_TOKENS';
	const HTML_PREFIX			= 'token_';
	public static $TOKEN_LENGTH	= 16;
	// Can not be unlimited or refreshed pages will create a non limited amount of tokens
	// We store the minimum amount of data to allow no control of expiration
	public static $DEFAULT_MAXTOKEN	= 10;

	/**
	 * Constructor
	 * @param string $name
	 * @param string $maxToken
	 * @param number $maxUsage Number of max usage, default value is 1.
	 */
	public function __construct($name=NULL, $maxToken=null, $maxUsage=1) {
		$this->name		= $name===NULL ? $GLOBALS['Module'] : $name;
		$this->maxToken	= $maxToken===NULL ? static::$DEFAULT_MAXTOKEN : $maxToken;
		$this->maxUsage	= $maxUsage;
	}

	public function generateToken() {
		if( !isset($_SESSION[self::SESSION_KEY][$this->name]) ) {
			$_SESSION[self::SESSION_KEY][$this->name]	= array();
		}
		$TOKEN_SESSION	= &$_SESSION[self::SESSION_KEY][$this->name];
		do {
			$token	= generatePassword(static::$TOKEN_LENGTH);
		} while( isset($TOKEN_SESSION[$token]) );
		if( count($TOKEN_SESSION) >= $this->maxToken ) {
			array_shift($TOKEN_SESSION);
		}
		$TOKEN_SESSION[$token]	= 0;
		return $token;
	}
	
	public function generateTokenHTML($force=false) {
		if( $force ) {
			$token	= $this->generateToken();
		} else {
			if( !isset($this->lastToken) ) {
				$this->lastToken	= $this->generateToken();
			}
			$token	= $this->lastToken;
		}
		return '<input type="hidden" name="'.self::HTML_PREFIX.$this->name.'" value="'.$token.'" />';
	}
	public function _generateTokenHTML($force=false) {
		echo $this->generateTokenHTML($force);
	}
	public function __toString() {
		return $this->generateTokenHTML();
	}

	public function validate($token) {
		if( !isset($_SESSION[self::SESSION_KEY][$this->name]) ) {
			return false;
		}
		$TOKEN_SESSION	= &$_SESSION[self::SESSION_KEY][$this->name];
		if( empty($token) || empty($TOKEN_SESSION) || !isset($TOKEN_SESSION[$token]) ) {
			return false;
		}
		$TOKEN_SESSION[$token]++;
		if( $TOKEN_SESSION[$token] >= $this->maxUsage ) {
			unset($TOKEN_SESSION[$token]);
		}
		return true;
	}
	public function validateForm($domain=null) {
		if( !$this->validate(POST(self::HTML_PREFIX.$this->name)) ) {
			throw new UserException('invalidFormToken', $domain);
		}
	}
}
