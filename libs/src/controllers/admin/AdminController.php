<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Rendering\HTMLRendering;

abstract class AdminController extends HTTPController {
	
	protected $breadcrumb = array();

	public function addBreadcrumb($label, $link=null) {
		$this->breadcrumb[]	= (object)array('label' => $label, 'link' => $link);
	}

	public function addRouteToBreadcrumb($route, $label=null, $link=true) {
		if( $link ) {
			if( typeOf($link) === 'string' ) {
				$link = $link;
			} else {
				$link = u($route, is_array($link) ? $link : array());
			}
		} else {
			$link = null;
		}
		$this->addBreadcrumb($label ? $label : t($route), $link);
	}

	public function addThisToBreadcrumb($label=null, $link=false) {
		$link = $this->getRequest()->getParameters();
		$this->addRouteToBreadcrumb($this->getRouteName(), $label, $link);
	}
	
	public function preRun($request) {
		parent::preRun($request);
		HTMLRendering::setDefaultTheme('admin');
		
		$this->addRouteToBreadcrumb(DEFAULT_ROUTE);
		$this->addRouteToBreadcrumb(ROUTE_ADM_HOME);
	}
	
	public function render($response, $layout, $values=array()) {
		if( isset($GLOBALS['USER']) ) {
			$values['USER']	= $GLOBALS['USER'];
		}
		$values['Breadcrumb'] = $this->breadcrumb;
		return parent::render($response, $layout, $values);
	}

}
