
var EDITOR_MODE = false;
var EDITOR_RENDERED = false;

$(function() {
	$(".editmode-btn").click(function() {
		EDITOR_MODE ? editorMode_leave() : editorMode_enter();
	});
});

function editorMode_render() {
	if( EDITOR_RENDERED || !EDITOR_MODE ) {
		return;
	}
	EDITOR_RENDERED = true;
	
	// Add forum
	$(".forumlist").prepend('<button type="button" class="editor-element addForum-btn btn btn-default btn-xs" style="display: none;">Nouveau forum <span class="icon-plus"></span></button>');
	$('#newForumForm').modal({show:false});
	$(".addForum-btn").click(function() {
		var pid = $(this).closest(".forumlist").attr("id").split("-");
		if( pid.length < 2 ) {
			return;
		}
		$('#newForumForm').find("#nff_fid").val(pid[1])
		$('#newForumForm').modal("show");
	});
	
	// Edit Forum
	$(".forumlist").prepend('<button class="editor-element btn btn-default btn-sm right editForum-btn"><i class="icon-edit"></i> Edit Forum</button>');
	
}

function editorMode_enter() {
	if( EDITOR_MODE ) {
		return;
	}
	EDITOR_MODE = true;
	
	// Rendering if not done yet
	editorMode_render();
	
	// Showing editor mode
	$("body").addClass("editor");
	$(".editmode-btn").removeClass("btn-default").addClass("btn-primary");
	$(".editor-element").show();
//	$(".addForum-btn").show();
}

function editorMode_leave() {
	if( !EDITOR_MODE ) {
		return;
	}
	EDITOR_MODE = false;
	
	// Hiding editor mode
	$("body").removeClass("editor");
	$(".editmode-btn").removeClass("btn-primary").addClass("btn-default");
	$(".editor-element").hide();
//	$(".addForum-btn").hide();
}