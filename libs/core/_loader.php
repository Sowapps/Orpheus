<?php
/* Loader File for the core sources
 */

echo __FILE__.' : '.__LINE__."<br />\n";
require_once LIBSPATH.'core/core.php';

echo __FILE__.' : '.__LINE__."<br />\n";

addAutoload('ConfigCore',						'core/configcore_class.php');

echo __FILE__.' : '.__LINE__."<br />\n";
require_once LIBSPATH.'core/hooks.php';
echo __FILE__.' : '.__LINE__."<br />\n";
require_once LIBSPATH.'core/validators.php';
echo __FILE__.' : '.__LINE__."<br />\n";