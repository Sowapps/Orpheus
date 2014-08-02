<?php
if( isPOST('data') ) {
	try {
		text('Create Demotest');
		DemoTest::isFieldEditable('id');
		text('Create Demotest');
		$test = DemoTest::create($_POST['data']);
		reportSuccess("Object created.");
		
		$test = DemoTest::load($test);
		reportSuccess("Object \"{$test}\" loaded, it's named \"{$test->name}\".");
		
// 		DemoTest::delete($tid);
		$test->remove();
		reportSuccess("Object deleted.");
		
	} catch (UserException $e) {
		reportError($e);
	}
}
?>
<div class="jumbotron">
	<h1>Hello PHP developer !</h1>
	<p>This is the home page of my new PHP Framework, named Orpheus. This one is made for you because you want to develop your website quickly with something easy-to-use, optimized, secured and standardized with the simplest way to do it and the maximum customizing capabilities.</p>
	<p><a href="downloads/" class="btn btn-primary btn-large">&darr; Download &darr;</a></p>
</div>

<div class="row">
	<div class="col-xs-8">

		<div class="row">
			<div class="col-xs-4">
<h3>Persistant Entity System</h3>
<p>The framework allow you to do not manipulate SQL queries anymore. It includes a SQL Adapter system with a full object entity handling.</p>
			</div>
			<div class="col-xs-4">
<h3>A Light &amp; Powerful Renderer</h3>
<p>You can define the renderer on-the-fly, as the theme you want. Basically, it includes a Raw Renderer, a HTML Renderer and a Twig Rendering Adapter.</p>
			</div>
			<div class="col-xs-4">
<h3>i18n - Internationalization</h3>
<p>All features allow you to translate contents using our easy-to-use internationalization library. The translation functions also allow you to pass replacement values.</p>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-4">
<h3>Debug Tools Provided</h3>
<p>The framework provides you all tools to debug your application, it catches all error that occurred running your scripts and log it for you.</p>
			</div>
			<div class="col-xs-4">
<h3>Edit the content online <span class="badge">Building</span></h3>
<p>The framework will provide a CMS library soon, you will be able to edit your application online &amp; inline.</p>
			</div>
			<div class="col-xs-4">
<h3>Speak about it <span class="badge">Building</span></h3>
<p>We are developing a forum library for our framework, you will be able to create and integrate a forum in your App !</p>
			</div>
		</div>
<!--
badge
label label-warning
-->
	</div>
	<div class="col-xs-4">

<section id="demotest">
<?php
displayReportsHTML();
?>
	<form method="POST">
	<fieldset>
		<legend>Try to create you own DemoTest object</legend>
		<div class="form-group">
			<label class="w200">Create it from a new value</label>
			<input class="w300" type="text" name="data[name]" placeholder="Type new value, longer than 10 characters">
		</div>
		<span class="help-block">Submit a new value to see this working test in action.</span>
		<button id="submitDemoTest" type="submit" class="btn btn-primary">Insert it !</button>
	</fieldset>
	</form>
</section>
	</div>
</div>