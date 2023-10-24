<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Controller\Admin;

use App\Entity\User;
use Orpheus\Config\Config;
use Orpheus\Exception\UserException;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpResponse;

class AdminUserListController extends AbstractAdminController {
	
	/**
	 * @param HttpRequest $request The input HTTP request
	 * @return HttpResponse The output HTTP response
	 */
	public function run($request): HttpResponse {
		$user = User::getActiveUser();
		
		$this->addThisToBreadcrumb();
		
		// CHECK_MODULE_ACCESS is used in cas you have no valid user to log in to the app, set the constant to false
		$allowCreate = !CHECK_MODULE_ACCESS || $user->canUserCreate();
		$allowUpdate = !CHECK_MODULE_ACCESS || $user->canUserEdit();
		$allowDevSee = !CHECK_MODULE_ACCESS || $user->canSeeDevelopers();
		
		$userInput = [];
		try {
			if( $request->hasData('submitCreate') ) {
				if( !$allowCreate ) {
					throw new UserException('forbiddenOperation');
				}
				$userInput = $request->getArrayData('user');
				$newUser = User::createAndGet($userInput);
				reportSuccess(User::text('successCreate', [$newUser]));
			}
		} catch( UserException $e ) {
			reportError($e);
		}
		
		$query = User::requestSelect()
			->orderby('fullname ASC');
		if( !$allowDevSee ) {
			$query->where('accesslevel', '<=', Config::get('user_roles/administrator'));
		}
		
		return $this->renderHtml('admin/admin_user_list', [
			'allowCreate' => $allowCreate,
			'allowUpdate' => $allowUpdate,
			'users'       => $query,
			'userInput'   => $userInput,
		]);
	}
	
}
