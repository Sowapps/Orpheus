<?php
global $USER;
HTMLRendering::useLayout('page_skeleton');

if( User::isLogged() ) {
	echo "<p>Welcome {$USER->fullname} </p>";
}

?>
<div class="row">
	
	<div class="col-xs-6">
		<form method="POST" role="form"><?php echo $FORM_TOKEN; ?>
		<fieldset>
			<legend>Sign in</legend>
			<?php displayReportsHTML('global'); ?>
			<div class="form-group">
				<label for="loginEmail">Email</label>
				<input type="text" class="form-control" id="loginEmail" name="login[email]" placeholder="Enter your email"/>
			</div>
			<div class="form-group">
				<label for="loginPassword">Password</label>
				<input type="password" class="form-control" id="loginPassword" name="login[password]" placeholder="Enter your password"/>
			</div>
			<button name="submitLogin" type="submit" class="btn btn-primary">Sign in</button>
		</fieldset>
		</form>
	</div>
		
	<div class="col-xs-6">
		<form method="POST" role="form"><?php echo $FORM_TOKEN; ?>
		<fieldset>
			<legend>Register</legend>
			<?php displayReportsHTML('register'); ?>
			
			<div class="form-group">
				<label for="registerFullname">Your name</label>
				<input type="text" class="form-control" id="registerFullname" name="user[fullname]" required placeholder="My displayed name, e.g. Cartman34" />
			</div>
			<div class="form-group">
				<label for="registerEmail">Email</label>
				<input type="email" class="form-control" id="registerEmail" name="user[email]" required placeholder="My email, e.g. cartman@domain.com"/>
			</div>
<!-- 			<div class="checkbox"> -->
<!-- 				<label> -->
<!-- 					<input type="checkbox" name="user[email_public]"> I allow other members to see my email address. -->
<!-- 				</label> -->
<!-- 			</div> -->
			<div class="form-group">
				<label for="registerPassword">Your password</label>
				<input type="password" class="form-control" id="registerPassword" name="user[password]" required />
			</div>
			<div class="form-group">
				<label for="registerConfirmPassword">Confirm password</label>
				<input type="password" class="form-control" id="registerConfirmPassword" name="user[password_conf]" required />
			</div>
			<button name="submitRegister" type="submit" class="btn btn-primary">Register</button>
		</fieldset>
		</form>
	</div>
</div>