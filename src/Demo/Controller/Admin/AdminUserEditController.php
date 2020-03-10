<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Admin;

use Demo\User;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTTPResponse;

class AdminUserEditController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 */
	public function run($request) {
		
		/* @var $USER User */
		global $USER, $formData;
		$userDomain = User::getDomain();
		
		$user = User::load($request->getPathValue('userID'));
		
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
				$userFields = ['fullname', 'email', 'accesslevel'];
				if( !empty($userInput['password']) ) {
					$userInput['password_conf'] = $userInput['password'];
					$userFields[] = 'password';
				}
				$result = $user->update($userInput, $userFields);
				if( $result ) {
					reportSuccess('successEdit', $userDomain);
				}
				
			} elseif( $request->hasData('submitDelete') ) {
				if( !$USER_CAN_USER_DELETE ) {
					throw new ForbiddenException();
				}
				if( $user->remove() ) {
					reportSuccess('successDelete', $userDomain);
				}
			}
		} catch( UserException $e ) {
			reportError($e, $userDomain);
		}
		
		$formData = ['user' => $user->all];
		
		includeHTMLAdminFeatures();
		
		return $this->renderHTML('app/admin_useredit', [
			'USER_CAN_USER_EDIT'   => $USER_CAN_USER_EDIT,
			'USER_CAN_USER_DELETE' => $USER_CAN_USER_DELETE,
			'USER_CAN_USER_GRANT'  => true,
			'ContentTitle'         => $user,
			'user'                 => $user,
		]);
	}
	
}
