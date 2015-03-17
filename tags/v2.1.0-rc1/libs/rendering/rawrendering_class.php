<?php
/** The raw rendering class

	A class to render module display without any treatment.
*/
class RawRendering extends Rendering {
	
	/** Renders the model.

		\copydoc Rendering::render()
	*/
	public function render($model=null, $env=array()) {
		extract($env);
		if( !isset($Page) ) {
			$Page = '';
		}
		return $Page;
	}
}