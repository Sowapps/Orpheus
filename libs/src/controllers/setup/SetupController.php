<?php
use Orpheus\Cache\FSCache;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\RedirectHTTPResponse;
use Orpheus\Rendering\HTMLRendering;
use Orpheus\InputController\HTTPController\HTTPRequest;

/*
 * Vérifier écriture sur le FS
 * Vérifier BDD
 * Install bdd
 * Install user
 * 
 */

abstract class SetupController extends HTTPController {
	
	private static $setupData;
	/**
	 * @var FSCache
	 */
	private static $setupCache;
	
	protected static $route;
	
	private $step;
	
	private static $stepOrder = array(
		'StartSetupController',
		'CheckFileSystemSetupController',
		'CheckDatabaseSetupController',
		'InstallDatabaseSetupController',
		'InstallFixturesSetupController',
		'EndSetupController',
	);
	
	public static function getDefaultRoute() {
		if( !static::$route ) {
			throw new Exception('SetupController::getRoute() should be overridden in '.get_called_class());
		}
		return static::$route;
	}
	
	public static function getStepPosition($stepName) {
		return array_search($stepName, self::$stepOrder);
	}
	
	public static function getPreviousStep($stepName) {
		$stepPos = static::getStepPosition($stepName);
		return $stepPos ? self::$stepOrder[$stepPos-1] : null;
	}
	
	protected static function getAvailableStepTo($target) {
		// Return the last available step to reach this one, or this one if this is available
		$prevStep	= static::getPreviousStep($target);
		if( !$prevStep ) {
			// This one is the first or unknown, so we redirect to the first step
			return self::$stepOrder[0];
		} else
		if( static::isThisStepValidated($prevStep) ) {
			// The target step is available
			return $target;
		} else {
			return static::getAvailableStepTo($prevStep);
		}
	}

	public function preRun(HTTPRequest $request) {
		parent::preRun($request);
// 		die('Controler preRun');
		HTMLRendering::setDefaultTheme('setup');
// 		HTMLRendering::setDefaultTheme('admin');

// 		die('Controler preRun - check');
		if( self::$setupData === null ) {
			self::$setupCache = new FSCache('setup', 'data');
			if( !self::$setupCache->get(self::$setupData) ) {
				self::$setupData	= (object) array('steps'=>array());
			}
		}
// 		$class	= get_called_class();
// 		die('Controler preRun - saveSetupData');
		$class	= static::getStepName();
		if( empty(self::$setupData->steps[$class]) ) {
			self::$setupData->steps[$class]	= (object) array('init_time'=>time());
			static::saveSetupData();
		}
		$this->step	= &self::$setupData->steps[$class];
		
		$availClass	= static::getAvailableStepTo($class);
// 		die('Controler preRun - '.$availClass.' VS '.$class);
		if( $class != $availClass ) {
			return new RedirectHTTPResponse($availClass::getDefaultRoute());
		}
		
	}
	
	protected static function getStepName() {
		return get_called_class();
	}
	
	protected function isStepValidated() {
		return isset($this->step->lastvalidate_time);
	}
	
	protected static function isThisStepValidated($stepName) {
		return isset(self::$setupData->steps[$stepName]) && self::$setupData->steps[$stepName]->lastvalidate_time;
	}
	
	protected function validateStep() {
// 		$class	= get_called_class();
		$this->step->lastvalidate_time	= time();
		static::saveSetupData();
	}
	
	protected static function saveSetupData() {
		self::$setupCache->set(self::$setupData);
	}

}
