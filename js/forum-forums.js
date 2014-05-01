
function updateContainerHeight() {
	$("body > .container").height($(window).height()-110);
}

$(function() {
	
	// New thread modal
	var newThreadForm = $("#newThreadForm").modal({show:false}).bind("shown.bs.modal", function() {
		if( $(this).data("modal_loaded") ) { return; }
//		debug("Modal is shown");
////		$("#editor").wysiwyg();
		var btn	= $("#pictureUploadBtn");
		$("#pictureUploadInput").css({"width": btn.outerWidth(), "height": btn.outerHeight(), "margin-left": (-btn.outerWidth())+"px", "opacity": 0, "float": "left", "cursor": "pointer", "display": "block"});
		$(this).data("modal_loaded", 1);
	});
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
//	$('#editor').wysiwyg();
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