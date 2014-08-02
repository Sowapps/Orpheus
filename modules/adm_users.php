<?php
/*** EDITION ***/

if( $Action == 'edit' && User::canDo('users_edit') ) {
	$noReportErrors = array('invalidPassword', 'sameAccessLevel');
	
	try {
		if( empty($_GET['uid']) ) {
			throw new Exception();
		}
		$user = SiteUser::load($_GET['uid']);
	} catch(UserException $e) {
		reportError('notFound');
	}
	if( isPOST('submitEdituser') ) {
		$userInput = $_POST['userdata'];
		$result = $user->update($_POST['userdata']);
		if( $result ) {
			reportSuccess('User edited.');
		}
		
		$userInput['email_public'] = !empty($userInput['email_public']) ? $userInput['email'] : $userInput['email_public'];
	} else {
		$userInput = $user->getValue();
	}
	
	displayReportsHTML($noReportErrors);
?>You are editing user #<?php echo $user->id; ?> named "<?php echo $user->name; ?>".<br />
<form method="POST">
	<label for="email">Email : </label> <input id="email" type="text" name="userdata[email]" <?php echo (!empty($userInput['email'])) ? "value=\"{$userInput['email']}\" " : ''; ?>/>
	<label for="email_public">Public Email : </label><input id="email_public" name="userdata[email_public]" type="text"<?php echo (!empty($userInput['email_public'])) ? "value=\"{$userInput['email_public']}\" " : ''; ?>/><br />
	<label for="fullname">Displayed name : </label> <input id="fullname" type="text" name="userdata[fullname]" <?php echo (!empty($userInput['fullname'])) ? "value=\"{$userInput['fullname']}\" " : ''; ?>/><br />
	<label for="name">User name : </label> <input id="name" type="text" name="userdata[name]" <?php echo (!empty($userInput['name'])) ? "value=\"{$userInput['name']}\" " : ''; ?>/><br />
	<label for="password">Password : </label> <input id="password" type="password" name="userdata[password]"/><br />
	<label for="accesslevel">Access level : </label> <input id="accesslevel" type="text" name="userdata[accesslevel]" <?php echo (isset($userInput['accesslevel'])) ? "value=\"{$userInput['accesslevel']}\" " : ''; ?>/><br />

	<input type="submit" name="submitEdituser" value="Save" /><br />
</form>
<?php
	return;
}

$formRegData = array();
if( isPOST('submitRegister') ) {
	
	try {
		$formRegData = $_POST['regdata'];
		$newUser = SiteUser::create($formRegData);
		reportSuccess("New user \"{$formRegData['fullname']}\" has been registered.");
	
	} catch(UserException $e) {
		reportError($e);
	}
	
} else if( isPOST('submitDeleteUser') && SiteUser::loggedCanDo('users_delete') ) {
	
	try {
		$delCount = SiteUser::delete(key($_POST['submitDeleteUser']));
		reportSuccess("{$delCount} users deleted.");
		
	} catch(Exception $e) {
		reportError($e);
	}
}

$UsersArr = SiteUser::get(array(
	'where'		=> SiteUser::loggedCanDo('users_seedev') ? '' : 'accesslevel<='.Config::get('perm_status/administrator'),
	'orderby'	=> 'fullname ASC',
	'output'	=> SQLAdapter::ARR_OBJECTS
));

displayReportsHTML();
?>
<form method="POST">
<h3>User list</h3>
<ul class="userslist">
<?php
foreach( $UsersArr as $user ) {
	echo "
	<li>
		<span>#{$user->id}</span>
		<span>{$user->name}</span>".
		( (SiteUser::loggedCanDo('users_delete', $user)) ? "<span><input type='submit' name='submitDeleteUser[{$user->id}]' value='Delete'/></span>" : '').
		( (SiteUser::loggedCanDo('users_edit', $user)) ? "<span><a href=\"adm_users-edit-uid={$user->id}.html\">Edit</a></span>" : '').
		"
	</li>";
}
?>
</ul>
</form>
<h3>Add new user</h3>
<form method="POST">
	<label for="email">Email* : </label> <input id="email" type="text" name="regdata[email]" <?php echo (!empty($formRegData['email'])) ? "value=\"{$formRegData['email']}\" " : ''; ?>/>
		<label for="email_public">Public : </label><input id="email_public" name="regdata[email_public]" type="checkbox"<?php echo (!empty($formRegData['email_public'])) ? 'checked="checked" ' : ''; ?>/><br />
	<label for="name">User name* : </label> <input id="name" type="text" name="regdata[name]" <?php echo (!empty($formRegData['name'])) ? "value=\"{$formRegData['name']}\" " : ''; ?>/><br />
	<label for="fullname">Displayed name* : </label> <input id="fullname" type="text" name="regdata[fullname]" <?php echo (!empty($formRegData['fullname'])) ? "value=\"{$formRegData['fullname']}\" " : ''; ?>/><br />
	<label for="password">Password* : </label> <input id="password" type="password" name="regdata[password]"/><br />
	<label for="password_conf">Confirm* : </label> <input id="password_conf" type="password" name="regdata[password_conf]"/><br />
	<input type="submit" name="submitRegister" value="Save" /><br />
</form>
