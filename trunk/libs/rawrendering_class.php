<?php
class RawRendering extends Rendering {
	
	public function render($env, $model=null) {
		extract($env);
		echo $Page;
	}
}