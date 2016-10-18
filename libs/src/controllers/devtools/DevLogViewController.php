<?php

use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\UserException;

class DevLogViewController extends DevController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$file = $request->getParameter('file');
		
		if( !$file ) {
			throw new NotFoundException('Invalid log file');
		}
		
		$filePath = LOGSPATH.$file;
		
		$filePathInfo = (object) pathinfo($file);
		$format = strtoupper($filePathInfo->extension);
		/* @var AbstractFile $fileHandler */
		$fileHandler = null;
		
		switch( $format ) {
			case 'GZ' : {
				$fileHandler = new GZFile($filePath);
				break;
			}
			case 'LOG' : {
				$fileHandler = new TextFile($filePath);
				break;
			}
			default : {
				throw new UserException('invalidFileFormat', DOMAIN_LOGS); 
				break;
			}
		}
		
		if( !$fileHandler->isReadable() ) {
			throw new NotFoundException('Invalid log file');
		}
		
		try {
// 			debug('All data', $request->getAllData());
			if( $request->hasData('submitArchive') ) {
				if( !$fileHandler->isCompressible() ) {
					throw new UserException('logNotCompressible');
				}
				
				$ext = '.gz';
				$archPath = $filePath.'.'.date('YmdHis').$ext;
				
				$archHandler = new GZFile($archPath);
				$archHandler->write($fileHandler->getContents());
				unset($archHandler);
// 				$fp = gzopen($archPath, 'w9'); // w == write, 9 == highest compression
// 				gzwrite($fp, file_get_contents($filePath));
// 				gzclose($fp);
				
				$fileHandler->remove();
				redirectTo(u(ROUTE_DEV_LOG_VIEW).'?file='.basename($archPath));
// 				unlink($filePath);
// 				file_put_contents($filePath, '');
// 				reportSuccess(t('successFileArchived', DOMAIN_LOGS, u(ROUTE_DEV_LOG_VIEW).'?file='.basename($archPath)));
				
			} else
			if( $request->hasData('submitRemoveAll') ) {

				$fileHandler->remove();
				redirectTo(u(ROUTE_DEV_LOGS));
// 				unlink($filePath);
// 				file_put_contents($filePath, '');
				reportSuccess(t('successFileErased', DOMAIN_LOGS));
				
			} else
			if( $crc = $request->getData('submitRemoveByCRC') ) {
// 				debug('submitRemoveByCRC => '.$crc);
				
				$tmpFile = $filePath.'.tmp';
				$c = 0;
				
// 				$logInput = fopen($filePath, 'r+');// Lock write ?
// 				$logOutput = fopen($tmpFile, 'w');// Erase previous
				$tmpHandler = $fileHandler->getAnotherHandler($tmpFile);
// 				if( !$logInput ) {
// 					throw new UserException('unableToOpenLogFile', DOMAIN_LOGS);
// 				}
// 				if( !$logOutput ) {
// 					throw new UserException('unableToWriteTempFile', DOMAIN_LOGS);
// 				}
// 				while( ($line = fgets($logInput)) !== false ) {
				while( ($line = $fileHandler->getNextLine()) !== false ) {
					$log = json_decode($line);
					if( !isset($log->crc32) ) {
						$log->crc32 = crc32($log->report);
					}
					if( $log->crc32 == $crc ) {
						$c++;
					} else {
// 						fwrite($logOutput, $line);
						$tmpHandler->write($line);
					}
				}
				unset($log);
// 				$fileHandler->close();
// 				fclose($logInput);
// 				fclose($logOutput);
// 				debug('Logs folder', cleanscandir(LOGSPATH));
				if( $c ) {
					$tmpHandler->moveTo($fileHandler);
// 					unlink($filePath);
// 					rename($tmpFile, $filePath);
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
		
		return $this->renderHTML('devtools/dev_logfile', array(
			'file' => $filePath,
			'filePathInfo' => $filePathInfo,
			'format' => $format,
			'fileHandler' => $fileHandler,
			'hideDuplicate' => $hideDuplicate
		));
	}

}
