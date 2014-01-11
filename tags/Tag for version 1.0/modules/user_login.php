<a href="./">Basic demo</a><br />
<br />
<?php
$formRegData = array();
if( isPOST('submitLogin') ) {
	
	try {
		SiteUser::userLogin($_POST['logindata']);
		reportSuccess('You\'re successfully loggued in.');
		
	} catch(UserException $e) {
		reportError($e);
	}
} else if( isPOST('submitRegister') ) {
	try {
		$formRegData = $_POST['regdata'];
		$Membre = SiteUser::create($formRegData);
		reportSuccess('You\'re successfully registered.');
	} catch(UserException $e) {
		reportError($e);
	}
}
displayReportsHTML();

if( User::is_login() ) {
	echo "<p>Welcome {$USER->fullname} </p>";
}
?>
	<div class="row show-grid">
		
		<div class="span3">
			<form method="POST">
			<fieldset>
				<legend>Log in</legend>
				<label for="login_username">Log in with your registered account</label>
				<input class="input" id="login_username" type="text" name="logindata[name]" placeholder="User name" required="required" /><br />
				<input class="input" id="login_password" type="password" name="logindata[password]" placeholder="Password" required="required" /><br />
				<button id="submitLogin" type="submit" class="btn">Login</button>
			</fieldset>
			</form>
		</div>
			
		<div class="span6 offset1">
			<form method="POST">
			<fieldset>
				<legend>Register</legend>
				<label for="register_name">Or register your own account</label>
				
				<div class="row show-grid">
					<div class="span3">
						<label class="control-label" for="register_name">User name</label>
						<input class="input" id="register_name" type="text" name="regdata[name]" placeholder="Enter your user name" required="required" /><br />
						
						<label class="control-label" for="register_fullname">Displayed name</label>
						<input class="input" id="register_fullname" type="text" name="regdata[fullname]" placeholder="Enter your displayed name" required="required" /><br />
						
						<label class="control-label" for="register_email">Email</label>
						<input class="input" id="register_email" type="text" name="regdata[email]" placeholder="Enter your email address" required="required" />
						
						<label class="checkbox">
							<input name="regdata[email_public]" type="checkbox"/>Public
						</label>
					</div>
				
					<div class="span3">
						<label class="control-label" for="register_password">Password</label>
						<input class="input" id="register_password" type="password" name="regdata[password]" placeholder="Enter your password" required="required" /><br />
						
						<label class="control-label" for="register_confpassword">Confirm password</label>
						<input class="input" id="register_confpassword" type="password" name="regdata[password_conf]" placeholder="Confirm your password" required="required" /><br />
						
						<button id="submitRegister" type="submit" class="btn">Register</button>
					</div>
				</div>
			</fieldset>
			</form>
		</div>
	</div>