<?php


class TemplateRenderer implements Templatable {
	
	private $renderer;
	
	public function __construct(Rendering $renderer) {
		$this->renderer	= $renderer;
	}

	public function includeTemplate($model) {
		return $this->renderer->render($model);
	}


	public function includeInlay($identifier, $model, $max=1) {
	
	}
	
}