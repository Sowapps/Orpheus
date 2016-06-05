<?php

class ComposerController extends DevToolsController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
// 		debug('Composer json path => '.APPLICATIONPATH.'composer.json');
		
		if( !file_exists(APPLICATIONPATH.'composer.json') ) {
			throw new UserException('Unable to find composer.json file');
		}

		$composerConfig = json_decode(file_get_contents(APPLICATIONPATH.'composer.json'));
	
		return $this->renderHTML('devtools/dev_composer', array(
			'composerConfig' => $composerConfig
		));
	}

}
