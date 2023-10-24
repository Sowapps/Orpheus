<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Admin;

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Rendering\HtmlRendering;

abstract class AbstractAdminController extends HttpController {
	
	const OPTION_PAGE_TITLE = 'pageTitle';
	const OPTION_CONTENT_TITLE = 'contentTitle';
	const OPTION_CONTENT_LEGEND = 'contentLegend';
	
	const SESSION_SUCCESS = 'SUCCESS';
	
	protected array $breadcrumb = [];
	
	public function addThisToBreadcrumb($label = null, $link = false): static {
		return $this->addRouteToBreadcrumb($this->getRouteName(), $label, $link);
	}
	
	/**
	 * Add given route to breadcrumb
	 * Label is optional, else we translate the route name
	 * Link could be
	 *  - disabled using false
	 *  - auto-generated using true or an array of value (passed as values)
	 *  - Specified using string
	 */
	public function addRouteToBreadcrumb(string $route, ?string $label = null, bool|array|string $link = true): static {
		if( !$link ) {
			$link = null;
			
		} else if( typeOf($link) !== 'string' ) {
			// Could be true => generate with no args
			// Could be an array => generate using args
			$params = $this->getValues();
			if( is_array($link) ) {
				$params += $link;
			}
			$link = u($route, $params);
		}
		return $this->addBreadcrumb($label ?: t($route), $link);
	}
	
	public function getValues(): array {
		return [];
	}
	
	public function addBreadcrumb(string $label, string $link = null): static {
		$this->breadcrumb[] = (object)['label' => $label, 'link' => $link];
		
		return $this;
	}
	
	public function preRun($request): ?HttpResponse {
		parent::preRun($request);
		HtmlRendering::setDefaultTheme('admin');
		
		$this->addRouteToBreadcrumb(DEFAULT_ROUTE);
		$this->addRouteToBreadcrumb(ROUTE_ADM_HOME);
		
		return null;
	}
	
	public function fillValues(array &$values = []): void {
		parent::fillValues($values);
		
		if( isset($GLOBALS['USER']) ) {
			$values['USER'] = $GLOBALS['USER'];
		}
		$values['breadcrumb'] = $this->breadcrumb;
	}
	
	
	/**
	 * Set content title displayed to user
	 * False to not show the title
	 * Default is null, the content is auto-generated from route name
	 * String set the title
	 * False hides the title
	 */
	public function setContentTitle(string|false|null $title): AbstractAdminController {
		return $this->setOption(self::OPTION_CONTENT_TITLE, $title);
	}
	
	/**
	 * Set page title
	 */
	public function setPageTitle(?string $title): AbstractAdminController {
		return $this->setOption(self::OPTION_PAGE_TITLE, t('app_label', 'global', [$title, t('app_name')]));
	}
	
	public function storeSuccess(string $key, string $message, array $params = [], ?string $domain = null): static {
		if( !isset($_SESSION[self::SESSION_SUCCESS][$key]) ) {
			$_SESSION[self::SESSION_SUCCESS][$key] = [];
		}
		$_SESSION[self::SESSION_SUCCESS][$key][] = t($message, $domain, $params);
		
		return $this;
	}
	
	public function consumeSuccess(string $key, ?string $stream = null): static {
		if( isset($_SESSION[self::SESSION_SUCCESS][$key]) ) {
			if( $stream ) {
				startReportStream($stream);
			}
			foreach( $_SESSION[self::SESSION_SUCCESS][$key] as $report ) {
				reportSuccess($report);
			}
			if( $stream ) {
				endReportStream();
			}
		}
		unset($_SESSION[self::SESSION_SUCCESS][$key]);
		
		return $this;
	}
	
}
