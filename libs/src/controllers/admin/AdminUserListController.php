<?php

class AdminUserListController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER User */
		global $USER;
// 		global $USER_CLASS;
		$userDomain	= User::getDomain();
		
		$this->addThisToBreadcrumb();
		
		$USER_CAN_USER_EDIT	= !CHECK_MODULE_ACCESS || $USER->canUserEdit();
		$USER_CAN_DEV_SEE	= !CHECK_MODULE_ACCESS || $USER->canSeeDevelopers();
		
// 		$formData = array();
		if( $request->hasData('submitCreate') ) {
		
			try {
				$data = $request->getArrayData('user');
				if( !$USER_CAN_USER_EDIT ) {
					throw new UserException('forbiddenOperation');
				}
// 				$formData = POST('createData');
				$newUser = User::create($request->getArrayData('user'));
				reportSuccess(User::text('successCreate', $newUser));
// 				$formData = array();
		
			} catch(UserException $e) {
				reportError($e, $userDomain);
			}
		}
		
		$users = User::get(array(
				'where'		=> $USER_CAN_DEV_SEE ? '' : 'accesslevel<='.Config::get('user_roles/administrator'),
				'orderby'	=> 'fullname ASC',
				'output'	=> SQLAdapter::ARR_OBJECTS
		));
		
		return $this->renderHTML('app/admin_userlist', array(
			'USER_CAN_USER_EDIT'	=> $USER_CAN_USER_EDIT,
			'users'	=> $users
		));
	}

}
