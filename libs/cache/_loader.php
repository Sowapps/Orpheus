<?php
/* Loader File for the cache sources
 */

addAutoload('Cache',			'cache/cache');
addAutoload('FSCache',			'cache/fscache');
addAutoload('APCache',			'cache/apcache');
addAutoload('CacheException',	'cache/CacheException');