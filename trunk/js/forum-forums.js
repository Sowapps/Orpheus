
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
	
});


function getEntry(el) {
	return $(el).closest(".panel").find(".panel-heading");
}