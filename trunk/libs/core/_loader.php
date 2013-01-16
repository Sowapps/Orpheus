<?php
/* Loader File for the core sources
 */

// Core
require_once LIBSPATH.'core/core.php';

// Important
addAutoload('ConfigCore',						'core/configcore_class.php');

require_once LIBSPATH.'core/hooks.php';
require_once LIBSPATH.'core/validators.php';

addAutoload('UserException',					'core/userexception_class.php');