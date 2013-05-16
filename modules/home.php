<?php
if( isPOST('data') ) {
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
<div class="hero-unit">
	<h1>Hello PHP developer !</h1>
	<p>This is the home page of my new PHP Framework, named Orpheus. This one is made for you because you want to develop your website quickly with something easy-to-use, optimized, secured and standardized with the simplest way to do it and the maximum customizing capabilities.</p>
	<p><a href="downloads/" class="btn btn-primary btn-large">&darr; Download &darr;</a></p>
</div>

<section id="demotest">
<?php
displayReportsHTML();
?>
	<form method="POST">
	<fieldset>
		<legend>Try to create you own DemoTest object</legend>
		<label>Create it from a new value</label>
		<input type="text" name="data[name]" placeholder="Type new value...">
		<span class="help-block">Submit a new value to see this working test in action.</span>
		<button id="submitDemoTest" type="submit" class="btn">Insert it !</button>
	</fieldset>
	</form>
</section>