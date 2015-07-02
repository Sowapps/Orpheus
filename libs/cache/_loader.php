<?php
/* Loader File for the cache sources
 */

debug('Cache lib ok');
addAutoload('Cache',	'cache/cache');
addAutoload('FSCache',	'cache/fscache');
addAutoload('APCache',	'cache/apcache');