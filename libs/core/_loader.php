<?php
/* Loader File for the core sources
 */

require_once LIBSPATH.'core/core.php';

addAutoload('ConfigCore',						'core/configcore_class.php');

require_once LIBSPATH.'core/hooks.php';
require_once LIBSPATH.'core/validators.php';