<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Exception;
use Orpheus\Exception\ForbiddenException;
use Orpheus\Exception\NotFoundException;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;
use Orpheus\Service\SecurityService;

class AdminUserEditController extends AbstractAdminController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 * @throws NotFoundException
	 * @throws Exception
	 */
	public function run($request): HttpResponse {
		$authUser = User::getActiveUser();
		$userDomain = User::getDomain();
		
		$user = User::load($request->getPathValue('userId'), false);
		
		$this->addRouteToBreadcrumb(ROUTE_ADM_USER_LIST);
		$this->addThisToBreadcrumb($user->getLabel());
		$this->setContentTitle($user->getLabel());
		
		$allowUserUpdate = $authUser->canUserEdit();
		$allowUserPasswordChange = $authUser->canUserEdit();
		$allowUserDelete = $authUser->canUserDelete();
		$allowUserGrant = $authUser->canUserGrant();
		$allowImpersonate = $authUser->canUserImpersonate($user);
		
		try {
			if( $request->hasData('submitUpdate') ) {
				if( !$allowUserUpdate ) {
					throw new ForbiddenException();
				}
				$userInput = $request->getData('user');
				$userFields = ['fullname', 'email'];
				if( $allowUserGrant ) {
					$userFields[] = 'accesslevel';
				}
				$user->update($userInput, $userFields);
				
				$this->storeSuccess('userEditPassword', 'successUpdate', ['name' => $user->getLabel()], $userDomain);
				
				return $this->redirectToSelf();
				
			} else if( $request->hasData('submitUpdatePassword') ) {
				if( !$allowUserPasswordChange ) {
					throw new ForbiddenException();
				}
				$userInput = $request->getData('user');
				$userInput['password_conf'] = $userInput['password'] ?? null;
				$userFields = ['password'];
				$user->update($userInput, $userFields);
				
				$this->storeSuccess('userEditPassword', 'successUpdatePassword', ['name' => $user->getLabel()], $userDomain);
				
				return $this->redirectToSelf();
				
			} else if( $request->hasData('submitImpersonate') ) {
				SecurityService::get()->setActiveUser($user);
				reportSuccess(User::text('successImpersonate', [$user]));
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		$this->consumeSuccess('userEdit', 'userEdit');
		$this->consumeSuccess('userEditPassword', 'userEditPassword');
		
		$formData = ['user' => $user->all];
		
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
