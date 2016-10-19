<?php

use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\Exception\ForbiddenException;

class AdminUserEditController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER, $formData;
// 		global $USER_CLASS;
		$userDomain	= User::getDomain();

		$user	= User::load($request->getPathValue('userID'));
		
		if( !$user ) {
			User::throwNotFound();
		}
		
		$this->addRouteToBreadcrumb(ROUTE_ADM_USERS);
		$this->addThisToBreadcrumb($user);

		$USER_CAN_USER_EDIT		= !CHECK_MODULE_ACCESS || $USER->canUserEdit();
		$USER_CAN_USER_DELETE	= $USER->canUserDelete();

		try {
			if( $request->hasData('submitUpdate') ) {
				if( !$USER_CAN_USER_EDIT ) {
					throw new ForbiddenException();
				}
				$userInput = $request->getArrayData('user');
				$userFields = array('fullname', 'email', 'accesslevel');
				if( !empty($userInput['password']) ) {
					$userInput['password_conf']	= $userInput['password'];
					$userFields[] = 'password';
				}
				$result = $user->update($userInput, $userFields);
				if( $result ) {
					reportSuccess('successEdit', $userDomain);
				}
				
			} else
			if( $request->hasData('submitDelete') ) {
				if( !$USER_CAN_USER_DELETE ) {
					throw new ForbiddenException();
				}
				if( $user->remove() ) {
					reportSuccess('successDelete', $userDomain);
				}
			}
		} catch(UserException $e) {
			reportError($e, $userDomain);
		}
		
		$formData = array('user'=>$user->all);
		
		require_once ORPHEUSPATH.LIBSDIR.'src/admin-form.php';
		
		return $this->renderHTML('app/admin_useredit', array(
			'USER_CAN_USER_EDIT'	=> $USER_CAN_USER_EDIT,
			'USER_CAN_USER_DELETE'	=> $USER_CAN_USER_DELETE,
			'USER_CAN_USER_GRANT'	=> true,
			'ContentTitle'	=> $user,
			'user'	=> $user
		));
	}

}
