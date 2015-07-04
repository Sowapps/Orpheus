<?php
/** The user exception class
 * This exception is thrown when an occured caused by the user.
 */
class UserException extends Exception {
	
	protected $domain;
	
	/**
	 * Constructor
	 * @param string $message The exception message
	 * @param string $domain The domain for the message, optionnal, allow $code
	 * @param string $code The code of the exception
	 * @param string $previous The previous exception
	 */
	public function __construct($message=null, $domain=null, $code=0, $previous=null) {
		if( is_int($domain) ) {
			$code	= $domain;
			$domain	= null;
		}
		parent::__construct($message, $code, $previous);
		$this->setDomain($domain);
	}

	/**
	 * Get the domain
	 * @return string
	 */
	public function getDomain() {
		return $this->domain;
	}
	
	/**
	 * Set the domain
	 * @param string $domain The new domain
	 */
	public function setDomain($domain) {
		$this->domain = $domain;
	}

	/**
	 * Get the report from this exception
	 * @return string The report 
	 */
	public function getReport() {
		return $this->getText();
	}

	/**
	 * Get the user's message
	 * @return string The translated message from this exception
	 */
	public function getText() {
		return $this->getMessage();
	}

	/**
	 * Return the string representation of this exception
	 * @return string
	 */
	public function __toString() {
		try {
			return $this->getText();
		} catch(Exception $e) {
			if( ERROR_LEVEL == DEV_LEVEL ) {
				die('A fatal error occurred in UserException::__toString() :<br />'.$e->getMessage());
			}
			die('A fatal error occurred, please report it to an admin.<br />Une erreur fatale est survenue, veuillez contacter un administrateur.<br />');
// 			reportError($e);
		}
		return '';
	}
}

/** The Not Found Exception class
 * This exception is thrown when something requested is not found.
 */
class NotFoundException extends UserException {
	public function __construct($message=null, $domain=null, $previous=null) {
		parent::__construct($message ? $message : 'notFound', $domain, defined('HTTP_NOT_FOUND') ? HTTP_NOT_FOUND : null, $previous);
	}
}

/** The Forbidden Exception class
 * This exception is thrown when something requested require an authentication or more permissions.
 */
class ForbiddenException extends UserException {
	public function __construct($message=null, $domain=null, $previous=null) {
		parent::__construct($message ? $message : 'forbidden', $domain, defined('HTTP_FORBIDDEN') ? HTTP_FORBIDDEN : null, $previous);
	}
}

class OperationCancelledException extends UserException {}

class UserReportsException extends UserException {
	
	protected $reports;
	
	public function setReports(array $reports) {
		$this->reports	= $reports;
		return $this;
	}
	
	public function getReports() {
		return $this->reports;
	}
	
}
