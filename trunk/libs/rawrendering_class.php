<?php
//! The raw rendering class
/*!
	A class to render module display without any treatment.
*/
class RawRendering extends Rendering {
	
	//! Render the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($env, $model=null) {
		extract($env);
		echo $Page;
	}
}