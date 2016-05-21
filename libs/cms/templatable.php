<?php


abstract class Templatable {
	
	private $inlay;
	private $model;
	private $renderer;
	
	protected $modelsPath;
	protected static $modelsFolder	= '';
// 	protected static $modelsFolder	= 'templates/';
	
	/**
	 * @param string|Inlay $inlay Inlay or model
	 */
	public function __construct($inlay, $renderer) {
		if( is_string($inlay) ) {
			$this->inlay	= null;
			$this->model	= $inlay;
		} else {
			$this->inlay	= $inlay;
			$this->model	= $inlay ? $inlay->getModel() : null;
		}
		$this->renderer		= $renderer;
		$this->modelsPath	= $renderer->getModelsPath().static::$modelsFolder;
	}
	
	public function includeTemplate($identifier, $model);
	
	public function includeInlay($identifier, $model, $min=1, $max=1);
	
	public function setInlay(Inlay $inlay) {
		$this->inlay	= $inlay;
		$this->setModel($inlay->getModel());
	}
	
	public function getInlay() {
		return $this->inlay;
	}
	
	public function setModel($model) {
		$this->model	= $model;
	}
	
	public function getModel() {
		return $this->model;
	}
	
	public function getRenderer() {
		return $this->renderer;
	}
	
}