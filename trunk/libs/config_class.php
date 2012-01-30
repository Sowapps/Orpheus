<?php
echo "Config - Start<br />";
class Config extends ConfigCore {
	
	public function load($source) {
		$this->add(parse_ini_file(CONFPATH.$source.'.ini', true));
	}
	
}
echo "Config - End<br />";
