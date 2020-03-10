<?php

namespace Demo\Controller\Developer;

use Exception;
use Orpheus\Cache\FSCache;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class DevCacheController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		$this->addThisToBreadcrumb();
		
		try {
			if( $request->hasData('submitClearAllAPCCache') ) {
				// Clear all APC caches
				apcu_clear_cache();
				reportSuccess('APC Cache cleared');
				
			} elseif( $request->hasData('submitClearAllFSCache') ) {
				// Clear all FS caches
				foreach( FSCache::listAll() as $class => $classCaches ) {
					foreach( $classCaches as $cacheName => $cacheFile ) {
						unlink($cacheFile);
					}
					try {
						rmdir(FSCache::getFolderPath($class));
					} catch( Exception $e ) {
						// Ignore
					}
				}
				reportSuccess('FS Cache cleared');
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		return $this->renderHTML('developer/dev_cache');
	}
	
}
