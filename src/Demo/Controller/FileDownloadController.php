<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

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
		$file = File::load($request->getPathValue('fileID'), false);
		
		$file->download($request->getParameter('k'), $request->hasParameter('download'));
		
		// Stop the script, the download feature take response in
		// TODO Use a file download response
		return new HttpResponse();
	}
	
}
