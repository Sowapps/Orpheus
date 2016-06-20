<?php


abstract class Controller {

	/* @var $request InputRequest */
	protected $request;
	
	protected $options = array();
	
	public function __toString() {
		return get_called_class();
	}

	/**
	 *
	 * @param InputRequest $request
	 * @return OutputResponse
	 */
	public function process(InputRequest $request) {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$this->request	= $request;
		
		ob_start();
		$result	= null;
		try {
			// Could prevent Run & PostRun
			// We recommend that PreRun only return Redirections and Exceptions
			$result	= $this->preRun($request);
// 			print_r($result);
// 			die();
		} catch( UserException $e ) {
// 			print_r($e);
// 			die();
			$this->fillValues($values);
			$result	= $this->processUserException($e, $values);
// 		} catch( Exception $e ) {
// 			print_r($e);
// 			die();
		}
// 		die('$result '.print_r($result, 1));
		if( !$result ) {
			// PreRun could prevent Run & PostRun
			try {
				$result	= $this->run($request);
			} catch( UserException $e ) {
				$this->fillValues($values);
				$result	= $this->processUserException($e, $values);
			}
			$this->postRun($request, $result);
		}
// 		$output = ob_get_clean();
// 		debug('Got controller output => '.strlen($output));
// 		$result->setControllerOutput($output);
		$result->setControllerOutput(ob_get_clean());
		
		return $result;
	}
	
	public function processUserException(UserException $e) {
		throw $e;// Throw to request
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * @return ControllerRoute
	 */
	public function getRoute() {
		return $this->request->getRoute();
	}
	
	public function getRouteName() {
		return $this->request->getRouteName();
	}
	
	public function fillValues(&$values=array()) {
		$values['Controller']	= $this;
		$values['Request']		= $this->getRequest();
		$values['Route']		= $this->getRoute();
	}
	
	public function render($response, $layout, $values=array()) {
		$this->fillValues($values);
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	public function getOption($key, $default=null) {
		return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
	}
	
	public function setOption($key, $value) {
		$this->options[$key] = $value;
		return $this;
	}
	
	
	
	
}
