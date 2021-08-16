<?php

namespace Demo\Controller\Developer;

use Exception;
use Orpheus\Cache\FSCache;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class DevCacheController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
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
		
		return $this->renderHtml('developer/dev_cache');
	}
	
}
