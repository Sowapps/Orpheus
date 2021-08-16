<?php

namespace Demo\Controller\Developer;

use Orpheus\Exception\UserException;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use ZipArchive;

class DevAppTranslateController extends DevController {
	
	protected $fallbackLocale;
	
	protected $translatingLocale;
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$this->fallbackLocale = DEFAULT_LOCALE;
		
		/**
		 * @var string $translatingFile
		 * Temporarily file for all translation for the current selected language
		 * Translations are sorted by domain
		 */
		$translatingFile = null;
		
		$translatingFilePath = null;
		
		$formToken = new FormToken();
		
		$editedDomains = [];
		try {
			if( $request->hasParameter('locale') ) {
				$this->translatingLocale = $request->getParameter('locale');
				$translatingFilePath = TRANSLATIONS_PATH . $this->translatingLocale . '.json';
				$translatingZIPPath = TRANSLATIONS_PATH . $this->translatingLocale . '.zip';
			}
			if( $this->translatingLocale ) {
				if( $request->hasData('submitSave') ) {
					$formToken->validateForm($request);
					checkDir(TRANSLATIONS_PATH);
					file_put_contents($translatingFilePath, json_encode($request->getData('translate')));
					reportSuccess('successSaveAppTranslations', DOMAIN_TRANSLATIONS);
				}
				
				$translatingFile = file_exists($translatingFilePath) ? json_decode(file_get_contents($translatingFilePath), true) : null;
				
				if( $request->hasData('submitAnalyze') ) {
					$created = 0;
					$updated = 0;
					foreach( $this->listDomains() as $domain ) {
						$currentDomain = getLangDomainFile($this->translatingLocale, $domain);
						if( isset($translatingFile[$domain]) ) {
							foreach( $translatingFile[$domain] as $key => $value ) {
								if( $value ) {
									if( empty($currentDomain[$key]) ) {
										$created++;
										$editedDomains[$domain] = 1;
									} elseif( $value !== $currentDomain[$key] ) {
										$updated++;
										$editedDomains[$domain] = 1;
									}
								}
							}
						}
					}
					reportInfo(t('translationAnalyzeReport', DOMAIN_TRANSLATIONS, $created, $updated));
					unset($currentDomain, $value, $created, $updated);
					
				} elseif( $request->hasData('submitDownload') ) {
					$formToken->validateForm($request);
					if( !$translatingFile ) {
						throw new UserException('noDataToTranslationArchive', DOMAIN_TRANSLATIONS);
					}
					$arch = new ZipArchive();
					if( !$arch->open($translatingZIPPath, ZipArchive::CREATE) ) {
						throw new UserException('unableToOpenAppTranslationArchive', DOMAIN_TRANSLATIONS);
					}
					foreach( $translatingFile as $domain => $domainData ) {
						$domainContent = '
; Language ini file for domain ' . strtoupper($domain) . '
; The current locale is ' . $this->translatingLocale . '
		
';
						foreach( $domainData as $key => $text ) {
							$domainContent .= "{$key} = \"{$text}\"\n";
						}
						$arch->addFromString($domain . '.ini', $domainContent);
					}
					$arch->close();
					unset($arch);
					
					session_write_close();
					ob_clean();
					
					header('Content-Type: application/zip');
					header('Content-Disposition: attachment; filename="' . $this->translatingLocale . '-' . date('YmdHis') . '.zip"');
					header('Content-length: ' . filesize($translatingZIPPath));
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($translatingZIPPath)) . ' GMT');
					header('Cache-Control: private, max-age=86400');
					header('Pragma: public');
					
					readfile($translatingZIPPath);
					die();
				}
			}
			
		} catch( UserException $e ) {
			reportError($e);
		}
		
		return $this->renderHtml('developer/dev_apptranslate', [
			'formToken'           => $formToken,
			'editedDomains'       => $editedDomains,
			'fallbackLocale'      => $this->fallbackLocale,
			'translatingLocale'   => $this->translatingLocale,
			'translatingFile'     => $translatingFile,
			'translatingFilePath' => $translatingFilePath,
		]);
	}
	
	public function listDomains() {
		$domainsFiles = [];
		foreach( listSrcPath() as $path ) {
			if( is_dir($path . LANGDIR . $this->fallbackLocale) ) {
				foreach( cleanscandir($path . LANGDIR . $this->fallbackLocale) as $file ) {
					$pathInfo = (object) pathinfo($file);
					if( isset($pathInfo->extension) && $pathInfo->extension === 'ini' ) {
						$domainsFiles[$pathInfo->filename] = 1;
					}
				}
			}
		}
		return array_keys($domainsFiles);
	}
	
	public function listAllLocales() {
		$locales = [];
		foreach( listSrcPath() as $path ) {
			if( is_dir($path . LANGDIR) ) {
				foreach( cleanscandir($path . LANGDIR) as $folder ) {
					if( !is_dir($path . LANGDIR . $folder) ) {
						continue;
					}
					$locales[$folder] = 1;
				}
			}
		}
		return $locales;
	}
	
}
