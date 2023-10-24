<?php

namespace App\Controller\Developer;

use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\UserException;
use Orpheus\File\AbstractFile;
use Orpheus\File\GZFile;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\RedirectHttpResponse;

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
		
		$logPath = LOGS_PATH . '/' . $file;
		
		$format = AbstractFile::guessFileFormat($logPath);
		$logFile = AbstractFile::resolveFile($logPath, $format);
		
		if( !$logFile->isFormatSupported() ) {
			throw new UserException(sprintf('Format handler is not supported for "%s"', $format));
		}
		if( !$logFile->isReadable() ) {
			throw new NotFoundException('Invalid log file');
		}
		
		try {
			if( $request->hasData('submitArchive') ) {
				if( !$logFile->isCompressible() ) {
					throw new UserException('logNotCompressible');
				}
				
				$ext = '.gz';
				$archPath = $logPath . '.' . date('YmdHis') . $ext;
				
				$archHandler = new GZFile($archPath);
				$archHandler->write($logFile->getContents());
				unset($archHandler);
				
				$logFile->remove();
				
				return new RedirectHttpResponse(u(ROUTE_DEV_LOG_VIEW) . '?file=' . basename($archPath));
				
			} else if( $request->hasData('submitRemoveAll') ) {
				$logFile->remove();
				//				redirectTo(u(ROUTE_DEV_LOGS));
				//				reportSuccess(t('successFileErased', DOMAIN_LOGS));
				return new RedirectHttpResponse(u(ROUTE_DEV_LOGS));
				
			} else if( $crc = $request->getData('submitRemoveByCRC') ) {
				$tmpFilePath = $logPath . '.tmp';
				$c = 0;
				
				// Write new contents to another file
				//				$tempFile = $logFile->copyTo($tmpFile);
				$tempFile = $logFile::fromSameType($tmpFilePath);
				$tempFile->create();
				
				// Loop all lines to remove rows matching CRC32
				while( ($line = $logFile->getNextLine()) !== false ) {
					$log = json_decode($line);
					if( !isset($log->crc32) ) {
						$log->crc32 = crc32($log->report);
					}
					if( $log->crc32 === $crc ) {
						$c++;
					} else {
						$tempFile->write($line);
					}
				}
				unset($log);
				if( $c ) {
					// There is some lines left in file, we move it to the original log file
					$tempFile->moveTo($logFile);
				} else {
					// Nothing was removed, we don't touch the original file, we just remove the temporary file
					$tempFile->remove();
					throw new UserException('noEntryToRemoveByCRC', DOMAIN_LOGS);
				}
				unset($tempFile);
				reportSuccess(t('successDeleteX', DOMAIN_LOGS, [$c]));
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		$this->addRouteToBreadcrumb(ROUTE_DEV_LOGS);
		$this->addThisToBreadcrumb($file);
		
		return $this->renderHtml('developer/dev_log_file', [
			'format'        => $format,
			'logPath'       => $logPath,
			'logFile'       => $logFile,
			'hideDuplicate' => true,
		]);
	}
	
}
