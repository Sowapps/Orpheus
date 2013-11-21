
function updateContainerHeight() {
	$("body > .container").height($(window).height()-110);
}

$(function() {
	
	// New thread modal
	var newThreadForm = $("#newThreadForm").modal({show:false});
// 	var newThreadForm = $("#newThreadForm").modal();// Tests only
	$(".newthreadbtn").click(function() {
//		debug("Click");
//		debug(newThreadForm);
		var forum = getEntry(this);
		$(newThreadForm).find("#ntf_fid").val(forum.data("id"));
		$(newThreadForm).find("#ntf_title").text(forum.data("label"));
		$(newThreadForm).modal("show");
	});
	
	// Wysiwyg
// 	$("#newThreadForm textarea").wysiwyg();
	$("#editor").wysiwyg();
	
	debug($(document).height());
	debug($("body > .container"));
	
	updateContainerHeight();
	$(window).resize(updateContainerHeight);
//			function() {
//		debug("resized document, updating container height");
//		$("body > .container").height($(window).height()-110);
//	});
	
});


function getEntry(el) {
	return $(el).closest(".panel").find(".panel-heading");
}