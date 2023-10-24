<?php

namespace App\Controller\Setup;

use Exception;
use Orpheus\Cache\Cache;
use Orpheus\Cache\FileSystemCache;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\RedirectHttpResponse;
use Orpheus\Rendering\HtmlRendering;

abstract class AbstractSetupController extends HttpController {
	
	protected static string $routeName;
	private static ?object $setupData = null;
	private static Cache $setupCache;
	private static array $stepOrder = [
		'StartSetupController',
		'CheckFileSystemSetupController',
		'CheckDatabaseSetupController',
		'InstallDatabaseSetupController',
		'InstallFixturesSetupController',
		'EndSetupController',
	];
	
	private object $step;
	
	/**
	 * @throws Exception
	 */
	public function preRun($request): ?RedirectHttpResponse {
		parent::preRun($request);
		HtmlRendering::setDefaultTheme('setup');
		
		self::$setupCache = new FileSystemCache('setup', 'data');
		if( !isset(self::$setupData) ) {
			if( !self::$setupCache->get(self::$setupData) ) {
				self::$setupData = (object) ['steps' => []];
			}
		}
		$step = static::getCurrentStepName();
		if( empty(self::$setupData->steps[$step]) ) {
			self::$setupData->steps[$step] = (object) ['init_time' => time()];
			static::saveSetupData();
		}
		$this->step = &self::$setupData->steps[$step];
		$stepClass = static::getStepClass($step);
		
		/** @var class-string<AbstractSetupController> $availClass */
		$availClass = static::getStepClass(static::getAvailableStepTo($step));
		if( $stepClass !== $availClass ) {
			return new RedirectHttpResponse($availClass::getDefaultRoute());
		}
		
		return null;
	}
	
	protected function isStepValidated(): bool {
		return isset($this->step->lastvalidate_time);
	}
	
	protected function validateStep(): void {
		$this->step->lastvalidate_time = time();
		static::saveSetupData();
	}
	
	public static function getDefaultRoute(): string {
		if( !static::$routeName ) {
			throw new Exception('SetupController::getRoute() should be overridden in ' . get_called_class());
		}
		
		return static::$routeName;
	}
	
	public static function getStepPosition($stepName): int {
		return array_search($stepName, self::$stepOrder);
	}
	
	public static function getPreviousStep(string $stepName): ?string {
		$stepPos = static::getStepPosition($stepName);
		
		return $stepPos ? self::$stepOrder[$stepPos - 1] : null;
	}
	
	protected static function getAvailableStepTo(string $target): string {
		// Return the last available step to reach this one, or this one if this is available
		$prevStep = static::getPreviousStep($target);
		if( !$prevStep ) {
			// This one is the first or unknown, so we redirect to the first step
			return self::$stepOrder[0];
		} elseif( static::isThisStepValidated($prevStep) ) {
			// The target step is available
			return $target;
		} else {
			return static::getAvailableStepTo($prevStep);
		}
	}
	
	protected static function getCurrentStepName(): string {
		return str_replace(__NAMESPACE__ . '\\', '', static::class);
	}
	
	protected static function isThisStepValidated(string $stepName): bool {
		return self::$setupData->steps[$stepName]?->lastvalidate_time ?? false;
	}
	
	protected static function saveSetupData(): void {
		self::$setupCache->set(self::$setupData);
	}
	
	/**
	 * @return class-string<AbstractSetupController>
	 */
	private static function getStepClass(string $step): string {
		return __NAMESPACE__ . '\\' . $step;
	}
	
}
