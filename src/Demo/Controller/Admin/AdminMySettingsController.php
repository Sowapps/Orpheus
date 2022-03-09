<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo\Controller\Admin;

use Demo\User;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class AdminMySettingsController extends AdminController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		
		/* @var $USER User */
		global $USER, $formData;
		
		$user = User::getLoggedUser();
		
		$this->addThisToBreadcrumb();
		
		try {
			if( $request->hasData('submitUpdate') ) {
				$userInput = POST('user');
				$userFields = ['fullname', 'email', 'timezone'];
				if( !empty($userInput['password']) ) {
					$userInput['password_conf'] = $userInput['password'];
					$userFields[] = 'password';
				}
				$result = $user->update($userInput, $userFields);
				if( $result ) {
					reportSuccess(User::text('succesSelfEdit'));
				}
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		unset($userInput);
		
		$formData = ['user' => $user->all];
		
		$USER_CAN_USER_EDIT = !CHECK_MODULE_ACCESS || $USER->canUserEdit();
		
		require_once ORPHEUS_PATH . LIBRARY_FOLDER . '/src/admin-form.php';
		
		return $this->renderHtml('app/admin_useredit', [
			'USER_CAN_USER_EDIT'   => $USER_CAN_USER_EDIT,
			'USER_CAN_USER_GRANT'  => false,
			'USER_CAN_USER_DELETE' => false,
			'user'                 => $user,
		]);
	}
	
}
