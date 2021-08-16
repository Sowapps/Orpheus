<?php

namespace Demo\Controller\Developer;

use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Sowapps\File\AbstractFile;
use Sowapps\File\GZFile;
use Sowapps\File\TextFile;

class DevLogViewController extends DevController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		$file = $request->getParameter('file');
		
		if( !$file ) {
			throw new NotFoundException('Invalid log file');
		}
		
		$filePath = LOGSPATH . $file;
		
		$filePathInfo = (object) pathinfo($file);
		$format = strtoupper($filePathInfo->extension);
		/* @var AbstractFile $fileHandler */
		$fileHandler = null;
		
		switch( $format ) {
			case 'GZ' :
			{
				$fileHandler = new GZFile($filePath);
				break;
			}
			case 'LOG' :
			{
				$fileHandler = new TextFile($filePath);
				break;
			}
			default :
			{
				throw new UserException('invalidFileFormat', DOMAIN_LOGS);
				break;
			}
		}
		
		if( !$fileHandler->isReadable() ) {
			throw new NotFoundException('Invalid log file');
		}
		
		try {
			if( $request->hasData('submitArchive') ) {
				if( !$fileHandler->isCompressible() ) {
					throw new UserException('logNotCompressible');
				}
				
				$ext = '.gz';
				$archPath = $filePath . '.' . date('YmdHis') . $ext;
				
				$archHandler = new GZFile($archPath);
				$archHandler->write($fileHandler->getContents());
				unset($archHandler);
				
				$fileHandler->remove();
				redirectTo(u(ROUTE_DEV_LOG_VIEW) . '?file=' . basename($archPath));
				
			} elseif( $request->hasData('submitRemoveAll') ) {
				
				$fileHandler->remove();
				redirectTo(u(ROUTE_DEV_LOGS));
				reportSuccess(t('successFileErased', DOMAIN_LOGS));
				
			} elseif( $crc = $request->getData('submitRemoveByCRC') ) {
				$tmpFile = $filePath . '.tmp';
				$c = 0;
				
				$tmpHandler = $fileHandler->getAnotherHandler($tmpFile);
				
				while( ($line = $fileHandler->getNextLine()) !== false ) {
					$log = json_decode($line);
					if( !isset($log->crc32) ) {
						$log->crc32 = crc32($log->report);
					}
					if( $log->crc32 == $crc ) {
						$c++;
					} else {
						$tmpHandler->write($line);
					}
				}
				unset($log);
				if( $c ) {
					$tmpHandler->moveTo($fileHandler);
				} else {
					$tmpHandler->remove();
					throw new UserException('noEntryToRemoveByCRC', DOMAIN_LOGS);
				}
				unset($tmpHandler);
				reportSuccess(t('successDeleteX', DOMAIN_LOGS, $c));
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		$hideDuplicate = 1;
		
		$this->addRouteToBreadcrumb(ROUTE_DEV_LOGS);
		$this->addThisToBreadcrumb($file);
		
		return $this->renderHtml('developer/dev_logfile', [
			'file'          => $filePath,
			'filePathInfo'  => $filePathInfo,
			'format'        => $format,
			'fileHandler'   => $fileHandler,
			'hideDuplicate' => $hideDuplicate,
		]);
	}
	
}
