<?php

class AdminMySettingsController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER, $formData;
		
		$userDomain	= User::getDomain();

		$user	= User::getLoggedUser();

		if( isPOST('submitUpdate') ) {
			try {
				$userInput	= POST('user');
				$userFields	= array('fullname', 'email', 'accesslevel');
				if( isset($userInput['password']) ) {
					$userInput['password_conf']	= $userInput['password'];
					$userFields[]	= 'password';
				}
				$result = $user->update($userInput, $userFields);
				if( $result ) {
					reportSuccess('successEdit', $userDomain);
				}
			} catch(UserException $e) {
				reportError($e, $userDomain);
			}
		}
		
		$formData	= array('user'=>$user->all);

		$USER_CAN_USER_EDIT	= !CHECK_MODULE_ACCESS || $USER->canUserEdit();
		
		require_once ORPHEUSPATH.LIBSDIR.'src/admin-form.php';
		
		return $this->renderHTML('app/admin_useredit', array(
			'USER_CAN_USER_EDIT'	=> $USER_CAN_USER_EDIT,
			'USER_CAN_USER_GRANT'	=> false,
			'user'	=> $user
		));
	}

}
