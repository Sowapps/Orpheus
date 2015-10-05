<?php


abstract class Controller {
	
	/**
	 * 
	 * @param InputRequest $request
	 * @return OutputRequest
	 */
// 	public function run(InputRequest $request);

	/* @var $request InputRequest */
	protected $request;
	
	public function __toString() {
		return get_called_class();
	}
	
	public function process(InputRequest $request) {
		$this->request	= $request;
		
		$this->preRun($request);
		$result	= $this->run($request);
		$this->preRun($request, $result);
		
		return $result;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getRoute() {
		return $this->request->getRoute();
	}
	
	public function getRouteName() {
		return $this->request->getRouteName();
	}
	
	public function render($response, $layout, $values=array()) {
		$values['Controller']	= $this;
		$values['Request']		= $this->getRequest();
		$values['Route']		= $this->getRoute();
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	
}
