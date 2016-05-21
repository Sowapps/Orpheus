<?php
/**
 * Loader File for the YAML sources
 * 
 * This library requires the yaml_parse_file() function, so the YAML php5 lib.
 * To get it, you should do:
 * apt-get install php5-dev libyaml-dev
 * pecl install yaml
 */

addAutoload('YAML',							'yaml/YAML');
//addAutoload('Config',						'yaml/yaml_lib.php');

