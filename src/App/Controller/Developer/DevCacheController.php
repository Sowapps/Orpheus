<?php

namespace App\Controller\Developer;

use Orpheus\Cache\Cache;
use Orpheus\Cache\Service\CacheService;
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
		$caches = CacheService::get()->getAllCaches();
		
		try {
			$clearCache = $request->getData('submitClearAll');
			if( $clearCache ) {
				$cacheClass = $caches[$clearCache] ?? null;
				if( !$cacheClass ) {
					throw new UserException(t('action.clearAll.errorInvalid', DOMAIN_CACHE, [$clearCache]), DOMAIN_CACHE);
				}
				/** @var Cache $cacheClass */
				if( $cacheClass::clearAll() ) {
					reportSuccess(t('action.clearAll.success', DOMAIN_CACHE, [strtoupper($clearCache)]));
				}
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		return $this->renderHtml('developer/dev_cache', ['caches' => $caches]);
	}
	
}
