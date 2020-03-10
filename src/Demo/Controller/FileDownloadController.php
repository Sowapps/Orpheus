<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller;

use Demo\File;
use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class FileDownloadController extends HTTPController {
	
	/**
	 * Controller declaration
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run($request) {
		
		/* @var File $file */
		$file = File::load($request->getPathValue('fileID'), false);
		
		$file->download($request->getParameter('k'), $request->hasParameter('download'));
		
		// Stop the script, the download feature take response in
		return null;
	}
	
}
