<?php


abstract class Controller {
	
	/**
	 * 
	 * @param InputRequest $request
	 * @return OutputRequest
	 */
	public function run(InputRequest $request);
	
}
