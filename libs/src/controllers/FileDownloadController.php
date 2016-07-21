<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;

class FileDownloadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var File $file */
		$file = File::load($request->getPathValue('fileID'), false);
		
		$file->download($request->getParameter('k'), $request->hasParameter('download'));
		
		// Stop the script, the download feature take response in 
// 		return $this->renderHTML('app/home');
	}

	
}
