<?php


class JSONHTTPResponse extends HTTPResponse {

	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @param array $data
	 */
	public function __construct($data=null) {
		$this->setData($data);
	}
	
	
// 	/**
// 	 * @return string
// 	 */
// 	public function __toString() {
// 		return $this->data.'';
// 	}
	
	/**
	 * @see HTTPResponse::run()
	 */
	public function run() {
		if( !headers_sent() ) {
			header('Content-Type: application/json');
		}
		echo json_encode($this->data);
// 		die(json_encode($data));
	}
	
	public function collectFrom($textCode, $other=null, $domain='global', $description=null) {
		$this->data	= array(
			'code'			=> $textCode,
			'description'	=> t($description ? $description : $textCode, $domain),
			'other'			=> $other
		);
	}
	
	public static function render($textCode, $other=null, $domain='global', $description=null) {
		$response	= new static();
		$response->collectFrom($textCode, $other, $domain, $description);
		return $response;
	}
	
	/**
	 * Generate HTMLResponse from 
	 * 
	 * @param Exception $exception
	 * @param string $action
	 */
	public static function generateFromException(Exception $exception, $action='Handling the request') {
		$code	= $exception->getCode();
		if( !$code ) {
			$code	= HTTP_INTERNAL_SERVER_ERROR;
		}
// 		debug('$exception', $exception);
// 		debug('$exception', (array) $exception);
		$other = new stdClass();
		$other->code	= $exception->getCode();
		$other->message	= $exception->getMessage();
		$other->file	= $exception->getFile();
		$other->line	= $exception->getLine();
		$other->trace	= $exception->getTrace();
		$response	= static::render('exception', $other, 'global', t('fatalErrorOccurred', 'global'));
// 		$response	= static::render('exception', $exception->getTrace(), 'global', 'fatalErrorOccurred');
		$response->setCode($code);
		return $response;
	}
	
	public static function generateFromUserException(UserException $exception, $values=array()) {
		$code	= $exception->getCode();
		if( !$code ) {
			$code = HTTP_BAD_REQUEST;
		}
// 		reportError($exception);
		if( $exception instanceof UserReportsException ) {
			/* @var $exception UserReportsException */
			$response = static::render($data->getMessage(), $exception->getReports(), $exception->getDomain());
		} else
		if( $exception instanceof UserException ) {
			$response = static::render($exception->getMessage(), null, $exception->getDomain());
		}
		$response->setCode($code);
		return $response;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function setData($data) {
		$this->data = $data;
		return $this;
	}
	
}
