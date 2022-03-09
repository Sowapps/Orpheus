<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 */

use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('page_skeleton');
?>
<div class="jumbotron">
	<h1>Hello PHP developer !</h1>
	<p>
		Get the power with the new Orpheus, the PHP framework from your dreams, coming with all features you need !
		This framework is made for you, you want to develop your website quickly with something easy-to-use, optimized,
		secured and standardized by easiest way to use it and the maximum customizing capabilities.
	</p>
	<p class="cb tac mt30">
		<a href="<?php _u('gettingstarted'); ?>" class="btn btn-primary btn-lg"><i class="fa fa-star-o"></i> Getting Started</a>
		<?php /*
		<a href="<?php _u(ROUTE_DOWNLOAD_LATEST); ?>" class="btn btn-primary btn-large"><i class="fa fa-download"></i> Download latest</a>
		<a href="<?php _u(ROUTE_DOWNLOAD_RELEASES); ?>" target="_blank" class="btn btn-link fs16">All releases</a>
		<a href="<?php echo u('download'); ?>" class="btn btn-primary btn-large"><i class="fa fa-download"></i> Download latest</a>
		<a href="<?php echo u('download').'?releases'; ?>" class="link fs16 ml10">All releases</a>
		*/ ?>
	</p>
</div>
<?php
$this->display('reports-bootstrap3');
?>

<div class="row">
	<div class="col-xs-8">
		
		<div class="row">
			<div class="col-xs-4">
				<h3>Persistant Entity System</h3>
				<p>The framework allow you to do not manipulate SQL queries anymore. It includes a SQL Adapter system with a full object entity handling for PHP.</p>
			</div>
			<div class="col-xs-4">
				<h3>A Light &amp; Powerful Rendering system</h3>
				<p>You can define the renderer on-the-fly from PHP, as the theme you want. Basically, it includes a simple & powerful raw php renderer, so no exotic thing, just what you already know
					!</p>
			</div>
			<div class="col-xs-4">
				<h3>i18n - Internationalization</h3>
				<p>All features allow you to translate contents using our easy-to-use internationalization library. The translation functions also allow you to pass replacement values from PHP.</p>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-4">
				<h3>Debug Tools Provided</h3>
<p>This PHP framework provides you all tools to debug your application, it catches all error that occurred running your scripts and log it for you.</p>
			</div>
			<div class="col-xs-4">
<h3>Edit the content online <span class="badge">In progress</span></h3>
<p>The framework will provide a CMS library soon, you will be able to edit your application online &amp; inline, no need to edit PHP sources.</p>
			</div>
			<div class="col-xs-4">
<h3>Speak about it <span class="badge">In progress</span></h3>
<p>We are developing a PHP forum library for our framework, you will be able to create and integrate a forum in your App !</p>
			</div>
		</div>
<!--
badge
label label-warning
-->
	</div>
	<div class="col-xs-4">

<section id="demotest">
	<form method="POST">
	<fieldset>
		<legend>Try to create you own DemoTest object</legend>
		<div class="form-group">
			<label for="inputValue">Create it from a new value</label>
			<input class="form-control" type="text" name="data[name]" placeholder="Type new value, longer than 3 characters" id="inputValue">
		</div>
		<span class="help-block">Submit a new value to see this working test in action.</span>
		<button id="submitDemoTest" type="submit" class="btn btn-primary">Insert it !</button>
	</fieldset>
	</form>
</section>
	</div>
</div>
