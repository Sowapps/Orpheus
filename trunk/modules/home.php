<?php
if( !empty($_POST['data']) ) {
	try {
		
		$tid = DemoTest::create($_POST['data']);
		reportSuccess("Object created.");
		
		$test = DemoTest::load($tid);
		reportSuccess("Object \"{$test}\" loaded, it's named \"{$test->name}\".");
		
		DemoTest::delete($tid);
		reportSuccess("Object deleted.");
		
	} catch (UserException $e) {
		reportError($e);
	}
	
}
?>
This page is an example of Home using Orpheus.<br />
<a href="user_login.html">Try user system ! (Publisher Plugin)</a><br />
<br />
<?php
displayReportsHTML();
?>
Try to create you own DemoTest object:<br />
<form method="POST">

<label for="name">Name: </label><input type="text" name="data[name]" value="A new value" /><br />
<input type="submit" value="Insert it !"/>

</form>