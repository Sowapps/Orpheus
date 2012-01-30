<?php
class Config extends ConfigCore {
	
	public function load($source) {
		$this->add(parse_ini_file(CONFPATH.$source.'.ini', true));
	}
	
}
