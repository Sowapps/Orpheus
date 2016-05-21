<?php


class TemplateAnalyzer extends Templatable {
	
	private $contents;
	
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

	// Link to an Inlay
	public function includeObject($model) {
		
	}
	// Link to multiple Inlays
	public function includeObjectList($model) {
		
	}

	// Could contains another inlay
// 	public function includeInlay($identifier, $model, $min=1, $max=1);
	
}