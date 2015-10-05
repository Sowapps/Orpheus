<?php

abstract class OutputResponse {


	public function __toString() {
		return get_called_class();
	}
	
}
