<?php


class TemplateAnalyzer extends Templatable {
	
	private $inlay;
	private $model;
	private $renderer;
	
	protected $modelsPath;
	protected static $modelsFolder	= '';
// 	protected static $modelsFolder	= 'templates/';
	
	private $contents;
	
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
	
	public function analyze() {
		$this->contents	= array();
		$modelPath	= $this->renderer->getModelPath($this->getModel());
		if( !is_readable($modelPath) ) {
			throw new Exception('TemplateAnalyzer Exception, unable to find model "'.$this->getModel().'"');
		}
		$View	= $this;
		$Inlay	= $this->getInlay();
		include $modelPath;
		return $this->contents;
	}

	// Could contains an inlay
	public function includeTemplate($identifier, $model) {
		$tplAnalyzer	= new static($model, $this->getRenderer());
		$this->contents[$identifier]	= array($tplAnalyzer->analyze());
	}

	// Could contains another inlay
	public function includeInlay($identifier, $model, $min=1, $max=1) {
		$max	= max($min, $max);
		$inlays	= Inlay::getByIdentifier($identifier, $max);
		$count	= 0;
		$this->contents[$identifier]	= array();
		foreach( $inlays as $inlay ) {
// 			$tplAnalyzer->setInlay($inlay);
			$tplAnalyzer	= new static($inlay, $this->getRenderer());
			$this->contents[$identifier][]	= $tplAnalyzer->analyze();
			$count++;
		}
		for( ; $count<$min; $count++ ) {
			$tplAnalyzer	= new static($model, $this->getRenderer());
			$this->contents[$identifier][]	= $tplAnalyzer->analyze();
		}
	}
	
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