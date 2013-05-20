<?php

// var_dump(DemoTest_MSSQL::get());

if( isPOST('data') ) {
	try {
		
		$tid = DemoTest_MSSQL::create($_POST['data']);
		reportSuccess("Object created.");
// 		text("ID: ".$tid);
		
		$test = DemoTest_MSSQL::load($tid);
		reportSuccess("Object \"{$test}\" loaded, it's named \"{$test->name}\".");
		
		DemoTest_MSSQL::delete($tid);
		reportSuccess("Object deleted.");
		
	} catch (UserException $e) {
		reportError($e);
	}
	
}
?>
<section id="demotest">
<?php
displayReportsHTML();
?>
	<form method="POST">
	<fieldset>
		<legend>Using multiple DB instance feature and MS SQL server, try to create you own DemoTest object</legend>
		<label>Create it from a new value</label>
		<input type="text" name="data[name]" placeholder="Type new value...">
		<span class="help-block">Submit a new value to see this working test in action.</span>
		<button id="submitDemoTest" type="submit" class="btn">Insert it !</button>
	</fieldset>
	</form>
</section>