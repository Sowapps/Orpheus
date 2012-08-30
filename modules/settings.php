<?php
$MODTITLE = 'Options';

if( !empty($_POST['submitEdituser']) && !empty($_POST['data']) ) {
	$noReportErrors = array('invalidPassword', 'invalidName', 'invalidEmail', 'invalidAccessLevel');
	$form = $_POST['data'];
	$result = $USER->update($_POST['data']);
	if( $result ) {
		reportSuccess('Settings saved.');
	}
	
	displayReportsHTML($noReportErrors);
} else {
	$form = $USER->all;
}

?>
	<form method="POST">
	<div class="settingsform form">
		<div class="fullname">
			<label for="fullname">Displayed name</label>
			<input class="input" id="fullname" type="text" name="data[fullname]" value="<?php echo (!empty($form['fullname'])) ? $form['fullname'] : ''; ?>"/>
		</div>
		<div class="email_public">
			<label for="email_public">Public email</label>
			<input class="input" id="email_public" type="text" name="data[email_public]" value="<?php echo (!empty($form['email_public'])) ? $form['email_public'] : ''; ?>"/>
		</div>
		<div class="password">
			<label for="password">Password</label>
			<input class="input" id="password" type="password" name="data[password]"/>
			<span class="help">(Only fill to change it)</span>
		</div>
		<div class="password_conf">
			<label for="password_conf">Password Confirm</label>
			<input class="input" id="password_conf" type="password" name="data[password_conf]"/>
		</div>
		<div class="settingsSubmit">
			<input class="submit" type="submit" name="submitEdituser" value="Save"/>
		</div>
	</div>
	</form>
</form>