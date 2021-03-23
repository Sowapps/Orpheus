function ConfirmDialog(title, message, submitName, submitValue) {
	
	// Initialize class dialog
	if( !ConfirmDialog.dialog ) {
		ConfirmDialog.dialog = $(ConfirmDialog.dialogTemplate);
		$("body").append(ConfirmDialog.dialog);
		ConfirmDialog.dialog.modal({"show": false});
	}
	if( submitValue === undefined ) {
		submitValue = 1;
	}
	this.dialog = ConfirmDialog.dialog;
	this.title = title;
	this.message = message;
	this.submitName = submitName || "submitValidate";
	this.submitValue = submitValue || 1;
	var widget = this;
	
	this.previous = null;
	this.imageLink = null;
	
	this.open = function () {
		widget.previous && widget.previous.hide();
		this.dialog.find(".confirm_title").text(this.title);
		this.dialog.find(".confirm_message").text(this.message);
		if( this.imageLink ) {
			this.dialog.find(".image_wrapper").show().find("img").attr("src", this.imageLink);
		} else {
			this.dialog.find(".image_wrapper").hide();
		}
		this.dialog.find(".confirm_validate")
			.attr("name", this.submitName).val(this.submitValue);
		
		this.dialog.modal('show');
	};
	
	this.dialog.on("hidden.bs.modal", function () {
		widget.previous && widget.previous.show();
	});
	
	this.getForm = function () {
		return this.dialog.find("form");
	};
	
	this.validate = function () {
		this.dialog.modal('hide');
	};
	
	this.close = function () {
		this.dialog.modal('hide');
	};
	
}

$(function () {
	ConfirmDialog.dialogTemplate = '\
	<div id="OrpheusConfirmDialog" class="modal fade">\
	<div class="modal-dialog">\
		<div class="modal-content">\
		<form method="POST">\
			<div class="modal-header">\
				<h4 class="modal-title confirm_title"></h4>\
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
			</div>\
			<div class="modal-body">\
				<p class="confirm_message mb10"></p>\
				<div class="row image_wrapper"><div class="col-lg-8 col-lg-offset-2"><img class="img-responsive img-thumbnail"/></div></div>\
			</div>\
			<div class="modal-footer">\
				<button type="button" class="btn btn-outline-secondary confirm_cancel" data-dismiss="modal">' + t('cancel') + '</button>\
				<button type="submit" class="btn btn-primary confirm_validate" value="1">' + t('ok') + '</button>\
			</div>\
		</form>\
		</div>\
	</div>\
</div>';
});
ConfirmDialog.buildFromDOMElement = function (element) {
	return new ConfirmDialog(element.data("confirm_title"), element.data("confirm_message"), element.data("confirm_submit_name"), element.data("confirm_submit_value"));
};

$.fn.requireConfirm = function (action) {
	// Filtering parameters
	var globalConfirmDialog = null;
	if( action === undefined ) {
		action = 'create';
	} else if( action instanceof ConfirmDialog ) {
		globalConfirmDialog = action;
		action = 'create';
	}
	// Resolving actions
	switch( action ) {
		case 'create': {
			var individualDialog = !globalConfirmDialog;
			this.each(function () {
				var $button = $(this);
				if( $button.data("confirmdialog") ) {
					return;
				}
				let confirmDialog;
				var lazyDialog = false;
				if( individualDialog ) {
					lazyDialog = !!$button.data("confirm_lazy");
					if( !lazyDialog ) {
						confirmDialog = ConfirmDialog.buildFromDOMElement($button);
					}
				} else {
					confirmDialog = globalConfirmDialog
				}
				if( lazyDialog ) {
					$button.click(function () {
						confirmDialog = ConfirmDialog.buildFromDOMElement($button);
						confirmDialog.open();
					});
					
				} else {
					$button.data("confirmdialog", confirmDialog);
					$button.click(function () {
						confirmDialog.open();
						return false;
					});
				}
			});
			break;
		}
		
	}
	// Returning default
	return this;
};

$(function () {
	$('[data-confirm_message]').requireConfirm();
	// Lazy confirm, build it each time
	$(document).on('click', '[data-toggle="confirm"]', function () {
		let $button = $(this);
		let confirmDialog = ConfirmDialog.buildFromDOMElement($button);
		confirmDialog.open();
	});
	
});
