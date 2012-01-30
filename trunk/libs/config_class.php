<?php
echo "Config - Start<br />";
class Config extends ConfigCore {
	
	public static function load($source) {
		return parse_ini_file(CONFPATH.$source.'.ini', true);
	}
	
}
echo "Config - End<br />";
