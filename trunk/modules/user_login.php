<a href="./">Basic demo</a>
<?php
$formRegData = array();
if( !empty($_POST['submitLogin']) ) {
	
	try {
		SiteUser::userLogin($_POST['data']);
		$_SESSION['welcome'] = 3;
		echo '<span class="success">You\'re successfully loggued in.</span><br />';
		
	} catch(UserException $e) {
		echo '<span class="error">'.error($e, 'user').'</span><br />';
	}
} else if( !empty($_POST['submitRegister']) ) {
	try {
		$formRegData = $_POST['regdata'];
		$Membre = SiteUser::create($formRegData);
		echo '<span class="success">You\'re successfully registered.</span><br />';
	} catch(UserException $e) {
		echo '<span class="error">'.error($e, 'user').'</span><br />';
	}
}
?>
<h2>Login</h2>
<form method="POST">
<div class="loginform form">
	<div class="username">
		<input class="input" id="name" type="text" name="data[name]" placeholder="User name" required="required" />
	</div>
	<div class="password">
		<label for="password">Mot de passe </label> 
		<input class="input" id="password" type="password" name="data[password]" placeholder="Password" required="required" />
	</div>
	<div class="loginSubmit">
		<input class="submit" type="submit" name="submitLogin" value="Se connecter"/>
	</div>
</div>
</form>

<h2>Register</h2>
<br />
<form method="POST">
<div class="registerform form">
	<div class="name">
		<label for="name">User name</label>
		<input class="input" id="name" type="text" name="regdata[name]" required="required" />
	</div>
	<div class="fullname">
		<label for="fullname">Displayed name</label>
		<input class="input" id="fullname" type="text" name="regdata[fullname]" required="required" />
	</div>
	<div class="email">
		<label for="email">Email</label>
		<input class="input" id="email" type="text" name="regdata[email]" required="required" />
	</div>
	<div class="email_public">
		<label for="email_public">Public email</label>
		<input class="checkbox" id="email_public" name="regdata[email_public]" type="checkbox"/>
	</div>
	<div class="password">
		<label for="password">Password</label>
		<input class="input" id="password" type="password" name="regdata[password]" required="required"/>
	</div>
	<div class="password_conf">
		<label for="password_conf">Confirm</label>
		<input class="input" id="password_conf" type="password" name="regdata[password_conf]" required="required"/>
	</div>
	<div class="registerSubmit">
		<input class="submit" type="submit" name="submitRegister" value="S'enregistrer"/>
	</div>
</div>
</form>