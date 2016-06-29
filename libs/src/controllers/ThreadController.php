<?php

use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;
use Orpheus\Form\FormToken;

class ThreadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		global $USER;
		$FORM_TOKEN	= new FormToken();

		$USER_CAN_THREADMESSAGE_MANAGE	= User::isLogged() && $USER->canThreadMessageManage();
		try {
			$request->isPOST() && $FORM_TOKEN->validateForm();
			if( $request->hasData('submitAdd') ) {
				// Create new message
				if( !User::isLogged() ) {
					User::throwException('forbiddenOperation');
				}
				$input	= $request->getData('tm');
				$input['user_id']	= $USER->id();
				$input['user_name']	= $USER->fullname;
				$tm	= ThreadMessage::createAndGet($input, array('content', 'user_id', 'user_name'));
				sendNewThreadMessageEmail($tm);
				reportSuccess('successCreate', ThreadMessage::getDomain());
				
			} else if( $request->hasDataKey('submitDelete', $tmID) ) {
				// Delete existing message
				if( !$USER_CAN_THREADMESSAGE_MANAGE ) {
					User::throwException('forbiddenOperation');
				}
				$tm	= ThreadMessage::load($tmID);
				$tm->remove();
				unset($tm);
				reportSuccess('successDelete', ThreadMessage::getDomain());
			}
		} catch(UserException $e) {
			reportError($e);
		}
		
		return HTMLHTTPResponse::render('app/thread', array(
			'FORM_TOKEN'	=> $FORM_TOKEN,
			'USER_CAN_THREADMESSAGE_MANAGE'	=> $USER_CAN_THREADMESSAGE_MANAGE
		));
	}

}
