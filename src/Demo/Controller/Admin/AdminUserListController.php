<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Admin;

use Demo\User;
use Orpheus\Config\Config;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;
use Orpheus\SQLAdapter\SQLAdapter;

class AdminUserListController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		/* @var $USER User */
		global $USER;
		$userDomain = User::getDomain();
		
		$this->addThisToBreadcrumb();
		
		$USER_CAN_USER_EDIT = !CHECK_MODULE_ACCESS || $USER->canUserEdit();
		$USER_CAN_DEV_SEE = !CHECK_MODULE_ACCESS || $USER->canSeeDevelopers();
		
		if( $request->hasData('submitCreate') ) {
			
			try {
				if( !$USER_CAN_USER_EDIT ) {
					throw new UserException('forbiddenOperation');
				}
				$newUser = User::create($request->getArrayData('user'));
				reportSuccess(User::text('successCreate', $newUser));
				
			} catch(UserException $e) {
				reportError($e, $userDomain);
			}
		}
		
		$users = User::get(array(
			'where'   => $USER_CAN_DEV_SEE ? '' : 'accesslevel<='.Config::get('user_roles/administrator'),
			'orderby' => 'fullname ASC',
			'output'  => SQLAdapter::ARR_OBJECTS
		));
		
		return $this->renderHTML('app/admin_userlist', array(
			'USER_CAN_USER_EDIT' => $USER_CAN_USER_EDIT,
			'users'              => $users
		));
	}
	
}
