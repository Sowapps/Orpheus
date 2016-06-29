<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;

class FileDownloadController extends HTTPController {

	/**
	 * Controller declaration
	 *
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$file = File::load($request->getPathValue('fileID'));
		
		$file->download($request->hasParameter('download'));
		
		return null;
		// Stop the script, the download feature take response in
		// return $this->renderHTML('app/home');
	}

}
