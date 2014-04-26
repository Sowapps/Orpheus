<?php
HTMLRendering::addJSFile('base64');
?>
<h1>Frequently Asked Questions</h1>

<h2>Why is this website using the most common bootstrap theme ?</h2>
<p>
The reason is very simple, the orpheus' developer is... a developer and not a web designer.<br />
As the framework is standardized, per default, it uses bootstrap framework with the simplest theme.<br />
If you're a webdesigner and you would like to propose a theme, please contact me at <a data-ee="ZGV2ZWxvcGVyQGNhcnRtYW4zNC5mcg=="></a>.
</p>

<h2>Why this website is (only) in english ?</h2>
<p>
Because English is an international language, this is the language used by developers to make it understandable by everyone, as the Orpheus framework.<br />
Even if the framework has an internationalization library, its default language is English as the demo website and documentation website.
</p>

<h2>Is this website based on Orpheus ?</h2>
<p>
Yes, of course. This version is often updated.
</p>

<h2>How it works ? How can i get started ?</h2>
<p>
Dude, please read <a href="/doc/html/">the manual of Orpheus</a>. ;-)
</p>

<h2>Is it just another PHP framework ?</h2>
<p>
In fact, this is not really a framework, it's the future base of your Application.<br />
It helps you to complete your work, without coding again the common base of a website.<br />
The main difference is that Orpheus forces you to follow certain basic rules, allowing you to maintain a secure and optimized application, even if it's designed to be tolerant and extensible.<br />
With a lot of library, everything is allowed on Orpheus, you can use your own user system, your own config library or import a library from another framework. 
</p>
<script type="text/javascript">
$(function() {
	$("a[data-ee]").each(function() {
		var e = b64_decode($(this).data("ee"));
		$(this).attr("href", "mailto:"+e).text(e).removeAttr("data-ee");
	});
});
</script>