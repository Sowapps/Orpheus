<?php

namespace App\Controller\Developer;

use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class DevComposerController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		define('DOMAIN_COMPOSER', 'composer');
		defifn('COMPOSER_HOME', INSTANCE_PATH . '/.composer');
		// 		define('COMPOSER_HOME', APPLICATION_PATH.'.composer');
		
		$composerFile = APPLICATION_PATH . '/composer.json';
		
		if( !file_exists($composerFile) ) {
			throw new UserException('Unable to find composer.json file');
		}
		
		try {
			// Readonly for now
			//			if( !is_writable($composerFile) ) {
			//				reportWarning('composerFileNotWritable', DOMAIN_COMPOSER);
			//				// 				throw new UserException('composerFileNotWritable');
			//			}
			//			if( !is_writable(COMPOSER_HOME) ) {
			//				reportWarning('composerHomeNotWritable', DOMAIN_COMPOSER);
			//				// 				throw new UserException('composerFileNotWritable');
			//			}
			
			// Always save data
			if( ($data = $request->getArrayData('composer')) && is_array($data) ) {
				// 				debug('$data', $data);
				// 				die();
				$composerConfig = json_decode(file_get_contents($composerFile));
				
				// 				debug('$data', $data);
				foreach( $data as $property => $value ) {
					$composerConfig->$property = $value;
				}
				if( !empty($composerConfig->authors) ) {
					$composerConfig->authors = json_decode($composerConfig->authors);
				} else {
					unset($composerConfig->authors);
				}
				if( !empty($composerConfig->require) ) {
					$composerConfig->require = json_decode($composerConfig->require);
				} else {
					unset($composerConfig->require);
				}
				
				// 				$composerConfig->test = 'Test property';
				// 				debug('$composerConfig', $composerConfig);
				file_put_contents($composerFile, json_encode($composerConfig));
				
			}
			unset($data);
			
			if( $request->hasData('submitUpdateInstall') ) {
				
				$command = $request->hasData('update/refresh') ? 'update' : 'install';
				$devOpt = $request->hasData('update/withdev') ? '' : '--no-dev';
				// --dev is deprecated, this is default
				// 				$devOpt		= $request->hasData('update/withdev') ? '--dev' : '--no-dev';
				$optiOpt = $request->hasData('update/optimize') ? '--optimize-autoloader' : '';
				
				// 			debug('CWD => '.getcwd());
				putenv('COMPOSER_HOME=' . COMPOSER_HOME);
				
				$cmd = 'cd "' . APPLICATION_PATH . '"; php composer.phar ' . $command . ' ' . $devOpt . ' ' . $optiOpt . ' 2>&1';
				// 			$cmd = 'php '.APPLICATION_PATH.'composer.phar '.$command.' '.$devOpt.' '.$optiOpt.' --no-progress';
				// 				debug('Command => '.$cmd);
				
				ob_start();
				$return = null;
				system($cmd, $return);
				// 			system('php '.APPLICATION_PATH.'composer.phar '.$command.' '.$devOpt.' '.$optiOpt.' --no-progress');
				$output = ob_get_clean();
				
				reportInfo(nl2br(t('outputLog', DOMAIN_COMPOSER, [$cmd, $output])));
				
				if( $return ) {
					throw new UserException('updateFailed');
				}
				reportSuccess('successUpdateInstall', DOMAIN_COMPOSER);
				
				// 				debug('Return => '.$return);
				// 				debug('Output', nl2br($output));
			}
		} catch( UserException $e ) {
			reportError($e, DOMAIN_COMPOSER);
		}
		$composerConfig = $this->parseComposerFile($composerFile);
		
		$this->addThisToBreadcrumb();
		
		return $this->renderHtml('developer/dev_composer', [
			'composerConfig'    => $composerConfig,
			'applicationFolder' => APPLICATION_PATH,
			'composerFile'      => $composerFile,
			'composerHome'      => COMPOSER_HOME,
		]);
	}
	
	protected function parseComposerFile(string $file): array {
		$config = json_decode(file_get_contents($file), true);
		// Set defaults
		$config['keywords'] ??= [];
		$config['authors'] ??= [];
		$config['require'] ??= [];
		$config['minimum-stability'] ??= 'stable';
		return $config;
	}
	
}
