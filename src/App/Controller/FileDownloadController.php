<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller;

use App\File\File;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\InputController\HttpController\LocalFileHttpResponse;

class FileDownloadController extends HttpController {
	
	/**
	 * Controller declaration
	 *
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 * @see HttpController::run()
	 */
	public function run($request): HttpResponse {
		/* @var File $file */
		$file = File::load($request->getPathValue('fileId'), false);
		$passKey = $request->getParameter('k');
		$forceDownload = $request->hasParameter('download');
		
		// Allow File to have no passkey, then the file is public
		if( $file->passkey && $passKey !== $file->passkey ) {
			File::throwException('invalidPasskey');
		}
		
		return new LocalFileHttpResponse($file->getPath(), $file->getFileName(), $forceDownload, $file->getCacheMaxAge());
	}
	
}
