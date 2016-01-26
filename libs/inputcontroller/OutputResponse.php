<?php

abstract class OutputResponse {

	/**
	 * 
	 * @var string
	 */
	protected $controllerOutput;
	
	/**
	 * @param string $controllerOutput
	 */
	public function setControllerOutput($controllerOutput) {
// 		debug('Set controller output response with '.strlen($controllerOutput).' characters');
		$this->controllerOutput	= $controllerOutput;
	}
		
	/**
	 * 
	 * @return string
	 */
	public function getControllerOutput() {
		return $this->controllerOutput;
	}

	/**
	 * 
	 * @return string
	 */
	public function __toString() {
		return get_called_class();
	}
	
}
