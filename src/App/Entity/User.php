<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace App\Entity;

use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\Publisher\Fixture\FixtureInterface;
use Orpheus\Publisher\Validation\Validation;

/** The site user class
 *
 * A site user is a registered user.
 *
 * Require:
 * is_id()
 * is_email()
 *
 * @property string $create_date
 * @property string $create_ip
 * @property int $create_user_id
 * @property string $login_date
 * @property string $login_ip
 * @property string $activity_date
 * @property string $activity_ip
 * @property string $activation_date
 * @property string $activation_ip
 *
 * @property string $email
 * @property string $password
 * @property string $fullname
 * @property int $avatar_id
 * @property boolean $published
 *
 * @property int $accesslevel
 * @property string $recovery_code
 * @property string $activation_code
 *
 */
class User extends AbstractUser implements FixtureInterface {
	
	public function asArray(string $model = self::OUTPUT_MODEL_ALL): array {
		if( $model === self::OUTPUT_MODEL_PUBLIC ) {
			return [
				'id'          => $this->id(),
				'label'       => $this->getLabel(),
				'fullname'    => $this->fullname,
				'email'       => $this->email,
				'create_date' => $this->create_date,
			];
		}
		return parent::asArray($model);
	}
	
	public function getLabel(): string {
		return $this->fullname;
	}
	
	public function getRank(): string {
		$perms = array_flip(static::getAvailRoles());
		
		return $perms[$this->accesslevel] ?? 'unknown_rank';
	}
	
	public function getAvailRoles(): array {
		$roles = static::getUserRoles();
		foreach( $roles as $status => $accessLevel ) {
			if( !$this->checkPerm($accessLevel) ) {
				unset($roles[$status]);
			}
		}
		
		return $roles;
	}
	
	public function getRoleText(): string {
		$status = array_flip(static::getAvailRoles());
		
		return isset($status[$this->accesslevel]) ? static::text('role_' . $status[$this->accesslevel]) : static::text('role_unknown', [$this->accesslevel]);
	}
	
	public function activate(): void {
		$this->published = 1;
		$this->logEvent('activation');
		$this->activation_code = null;
	}
	
	public function getActivationLink(): string {
		return u(ROUTE_USER_LOGIN) . '?ac=' . $this->activation_code . '&u=' . $this->id();
	}
	
	public function getAdminLink($ref = 0): string {
		return u('adm_user', ['userId' => $this->id()]);
	}
	
	public function getLink(): string {
		return static::genLink($this->id());
	}
	
	public function canUserList(): bool {
		return $this->canUserEdit();
	}
	
	public function canUserCreate(): bool {
		return $this->canUserEdit();
	}
	
	public function canUserEdit(): bool {
		if( $this->canDo('user_edit') ) {
			return true;
		}
		
		// Only App admins can do it
		return false;
	}
	
	public function canSeeDevelopers(): bool {
		return $this->canDo('developer_list');// Only App admins can do it.
	}
	
	public function canUserStatus(): bool {
		return $this->canDo('user_status');// Only App admins can do it.
	}
	
	public function canUserDelete(): bool {
		return $this->canDo('user_delete');// Only App admins can do it.
	}
	
	public function canUserGrant(): bool {
		return $this->canDo('user_grant');// Only App admins can do it.
	}
	
	public function canUserImpersonate(?User $user = null): bool {
		// Only App dev can do it or a user with more permission (inclusive)
		return $this->canDo('user_impersonate') && (!$user || $user->accesslevel <= $this->accesslevel);
	}
	
	public function canEntityDelete(): bool {
		return $this->canDo('entity_delete');// Only App admins can do it.
	}
	
	public function getAuthenticationToken(): string {
		return $this->auth_token;
	}
	
	public static function genLink(string $id): string {
		return u('profile', [$id]);
	}
	
	public static function getByEmail(string $email): ?User {
		if( !is_email($email) ) {
			static::throwException('invalidEmail');
		}
		
		return static::requestSelect()
			->where('email', 'LIKE', $email)
			->asObject()->run();
	}
	
	public static function getByAuthenticationToken(string $token): ?static {
		return static::requestSelect()
			->where('auth_token', 'LIKE', $token)
			->asObject()->run();
	}
	
	public static function onValidCreate(array &$input, Validation $validation): bool {
		$input['auth_token'] ??= generateRandomString();
		return parent::onValidCreate($input, $validation);
	}
	
	public static function generateAuthenticationToken(): string {
		do {
			$token = generateRandomString();
			$existingUser = User::getByAuthenticationToken($token);
		} while( $existingUser );
		return $token;
	}
	
	public static function create(array $input = [], ?array $fields = null, ?Validation $validation = null): int {
		if( empty($input['auth_token']) ) {
			$input['auth_token'] = static::generateAuthenticationToken();
			if( $fields ) {
				$fields[] = 'auth_token';
			}
		}
		return parent::create($input, $fields, $validation);
	}
	
	public static function loadFixtures(): void {
		static::create([
			'email'         => 'contact@sowapps.com',
			'fullname'      => 'Administrateur',
			'password'      => 'admin',
			'password_conf' => 'admin',
			'accesslevel'   => 300,
			'published'     => 1,
			'timezone'      => 'Europe/Paris',
		]);
	}
	
}

User::initialize('user');
