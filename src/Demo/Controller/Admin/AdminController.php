<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Admin;

use Exception;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\Rendering\HTMLRendering;

abstract class AdminController extends HttpController {
	
	protected $breadcrumb = [];
	
	public function addThisToBreadcrumb($label = null, $link = false) {
		$this->addRouteToBreadcrumb($this->getRouteName(), $label, $link);
	}
	
	/**
	 * Add given route to breadcrumb
	 * Label is optional, else we translate the route name
	 * Link could be
	 *  - disabled using false
	 *  - auto-generated using true or an array of value (passed as values)
	 *  - Specified using string
	 *
	 * @param $route
	 * @param string|null $label
	 * @param string|bool|array $link
	 * @throws Exception
	 */
	public function addRouteToBreadcrumb($route, $label = null, $link = true) {
		if( !$link ) {
			$link = null;
			
		} elseif( typeOf($link) !== 'string' ) {
			// Could be true => generate with no args
			// Could be an array => generate using args
			$params = $this->getValues();
			if( is_array($link) ) {
				$params += $link;
			}
			$link = u($route, $params);
		}
		$this->addBreadcrumb($label ? $label : t($route), $link);
	}
	
	public function getValues(): array {
		return [];
	}
	
	public function addBreadcrumb($label, $link = null) {
		$this->breadcrumb[] = (object) ['label' => $label, 'link' => $link];
	}
	
	public function preRun($request) {
		parent::preRun($request);
		HTMLRendering::setDefaultTheme('admin');
		
		$this->addRouteToBreadcrumb(DEFAULT_ROUTE);
		$this->addRouteToBreadcrumb(ROUTE_ADM_HOME);
	}
	
	public function render($response, $layout, $values = []) {
		if( isset($GLOBALS['USER']) ) {
			$values['USER'] = $GLOBALS['USER'];
		}
		$values['Breadcrumb'] = $this->breadcrumb;
		
		return parent::render($response, $layout, $values);
	}
	
}
