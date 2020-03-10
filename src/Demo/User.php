<?php
/**
 * @author Florent HAZARD <f.hazard@sowapps.com>
 */

namespace Demo;

use Exception;
use Orpheus\EntityDescriptor\User\AbstractUser;
use Orpheus\Publisher\Fixture\FixtureInterface;

/** The site user class
 *
 * A site user is a registered user.
 *
 * Require:
 * is_id()
 * is_email()
 * pdo_query()
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
	
	// *** OVERLOADED METHODS ***
	
	
	public function onConnected() {
// 		date_default_timezone_set($this->timezone);
	}
	
	public function __toString() {
		try {
			return escapeText($this->fullname);
		} catch( Exception $e ) {
			return '';
		}
	}
	
	public function getNicename() {
		return strtolower($this->name);
	}
	
	public function getRank() {
		$perms = array_flip(static::getAvailRoles());
		return isset($perms[$this->accesslevel]) ? $perms[$this->accesslevel] : 'unknown_rank';
	}
	
	public function getAvailRoles() {
		$roles = static::getUserRoles();
		foreach( $roles as $status => $accesslevel ) {
			if( !$this->checkPerm($accesslevel) ) {
				unset($roles[$status]);
			}
		}
		return $roles;
	}
	
	public function getRoleText() {
		$status = array_flip(static::getAvailRoles());
		return isset($status[$this->accesslevel]) ? static::text('role_'.$status[$this->accesslevel]) : static::text('role_unknown', $this->accesslevel);
	}
	
	public function activate() {
		$this->published	= 1;
		$this->logEvent('activation');
		$this->activation_code	= null;
	}
	
	
	public function getActivationLink() {
		return u(ROUTE_LOGIN).'?ac='.$this->activation_code.'&u='.$this->id();
	}
	
	public function getAdminLink($ref=0) {
		return u('adm_user', array('userID'=>$this->id()));
	}
	
	public function getLink() {
		return static::genLink($this->id());
	}
	
	public static function genLink($id) {
		return u('profile', $id);
	}
	
	public function canUserList($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canUserUpdate();
	}
	
	/**
	 * @param int $context
	 * @param User $contextResource
	 * @return boolean
	 */
	public function canUserCreate($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_edit');// Only App admins can do it.
	}
	
	/**
	 * @param int $context
	 * @param User $contextResource
	 * @return boolean
	 */
	public function canUserEdit($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		if( $this->canDo('user_edit') ) { return true; }
		return false;
	}
	
	public function canSeeDevelopers($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_seedev');// Only App admins can do it.
	}
	
	public function canUserStatus($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_status');// Only App admins can do it.
	}
	
	public function canUserDelete($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_delete');// Only App admins can do it.
	}
	
	public function canUserGrant($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('user_grant');// Only App admins can do it.
	}
	
	public function canThreadMessageManage($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('threadmessage_manage');// Only App admins can do it.
	}
	
	public function canEntityDelete($context=CRAC_CONTEXT_APPLICATION, $contextResource=null) {
		return $this->canDo('entity_delete');// Only App admins can do it.
	}
	
	/**
	 * @param string $email
	 * @return User
	 */
	public static function getByEmail($email) {
		if( !is_email($email) ) {
			static::throwException('invalidEmail');
		}
		return static::get()->where('email', 'LIKE', $email)->asObject()->run();
	}
	
	public static function loadFixtures() {
		static::create(array(
			'email'         => 'contact@sowapps.com',
			'fullname'      => 'Administrateur',
			'password'      => 'admin',
			'password_conf' => 'admin',
			'accesslevel'   => 300,
			'published'     => 1,
			'timezone'      => 'Europe/Paris',
		));
	}
	
}
User::init();
