<?php 
?>
This page is an example of Home using Orpheus.<br />
<br />
<?php
if( !empty($_POST['data']) ) {
	try {
		$tid = DemoTest::create($_POST['data']);
		echo "
	<div class=\"success\">Object created.</div>";
		$test = DemoTest::load($tid);
		echo "
	<div class=\"success\">Object \"{$test}\" loaded, it's named \"{$test->name}\".</div>";
		DemoTest::delete($tid);
		echo "
	<div class=\"success\">Object deleted.</div>";
	} catch (UserException $e) {
		echo "
	<div class=\"error\">$e</div>";
	}
	
}
//var_dump(SQLMapper::doSelect(array('table'=>'test')));
?>
Try to create you own DemoTest object:<br />
<form method="POST">

<label for="name">Name: </label><input type="text" name="data[name]" value="A new value" /><br />
<input type="submit" value="Insert it !"/>

</form>