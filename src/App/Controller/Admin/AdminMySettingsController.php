<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Admin;

use App\Entity\User;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class AdminMySettingsController extends AbstractAdminController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$authUser = User::getActiveUser();
		$user = $authUser;
		
		$this->addThisToBreadcrumb();
		
		$allowUserUpdate = true;
		$allowUserPasswordChange = true;
		$allowUserDelete = false;
		$allowUserGrant = false;
		$allowImpersonate = false;
		
		try {
			if( $request->hasData('submitUpdate') ) {
				$userInput = $request->getData('user');
				$userFields = ['fullname', 'email', 'timezone'];
				if( !empty($userInput['password']) ) {
					$userInput['password_conf'] = $userInput['password'];
					$userFields[] = 'password';
				}
				$result = $user->update($userInput, $userFields);
				if( $result ) {
					reportSuccess(User::text('successSelfEdit'));
				}
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		unset($userInput);
		
		$allowUserEdit = !CHECK_MODULE_ACCESS || $user->canUserEdit();
		
		return $this->renderHtml('admin/admin_user_edit', [
			'allowUserUpdate'         => $allowUserUpdate,
			'allowUserPasswordChange' => $allowUserPasswordChange,
			'allowUserDelete'         => $allowUserDelete,
			'allowUserGrant'          => $allowUserGrant,
			'allowImpersonate'        => $allowImpersonate,
			'user'                    => $user,
		]);
	}
	
}
