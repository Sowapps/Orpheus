<?php
/* Loader File for the core sources
 */

// Core
require_once pathOf(LIBSDIR.'core/core.php');

// Important
addAutoload('ConfigCore',						'core/configcore_class.php');

require_once pathOf(LIBSDIR.'core/hooks.php');
require_once pathOf(LIBSDIR.'core/validators.php');

addAutoload('UserException',					'core/userexception_class.php');
addAutoload('SQLException',						'core/sqlexception_class.php');
addAutoload('FormToken',						'core/formtoken_class.php');