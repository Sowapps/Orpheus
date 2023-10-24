/**
 * Require domService
 *
 * @param title
 * @param message
 * @param submitName
 * @param submitValue
 * @constructor
 */
class ConfirmDialog {
	
	static ACTION = {
		FORM_POST: 'form-post',
		BUTTON_EVENT: 'button-event',
	};
	static $dialog;
	static modal;
	static dialogTemplate;
	
	constructor($button, title, message, action, submitName, submitValue) {
		if( !ConfirmDialog.$dialog ) {
			// Initialize class dialog
			ConfirmDialog.$dialog = domService.castElement(ConfirmDialog.dialogTemplate);
			document.body.append(ConfirmDialog.$dialog);
			ConfirmDialog.modal = new bootstrap.Modal(ConfirmDialog.$dialog);
		}
		if( action === undefined ) {
			// form-post or button-event
			action = ConfirmDialog.ACTION.FORM_POST;
		}
		if( submitValue === undefined ) {
			submitValue = 1;
		}
		this.$button = $button;
		this.$dialog = ConfirmDialog.$dialog;
		this.$form = this.$dialog.querySelector('form');
		this.modal = ConfirmDialog.modal;
		this.title = title;
		this.message = message;
		this.action = action;
		this.submitName = submitName || "submitValidate";
		this.submitValue = submitValue || 1;
		// const widget = this;
		
		this.previous = null;
		this.imageLink = null;
		this.bindEvents();
	}
	
	bindEvents() {
		if( this.action === ConfirmDialog.ACTION.BUTTON_EVENT ) {
			this.$form.addEventListener('submit', event => {
				event.preventDefault();
				console.log("Submit confirm event", event);
				// debugger;
				domService.dispatchEvent(this.$button, 'confirmed');
				this.close();
			});
		}
	}
	
	open() {
		// widget.previous && widget.previous.hide();
		this.$dialog.querySelector(".confirm-title").innerText = this.title;
		this.$dialog.querySelector(".confirm-message").innerText = this.message;
		const $imageWrapper = this.$dialog.querySelector(".image-wrapper");
		if( this.imageLink ) {
			$imageWrapper.querySelector("img").src = this.imageLink;
			$imageWrapper.hidden = false;
		} else {
			$imageWrapper.hidden = true;
		}
		this.$dialog.querySelectorAll(".confirm-validate")
			.forEach($confirmButton => {
				// Set dynamically the name and the value of the confirm button
				$confirmButton.name = this.submitName;
				$confirmButton.value = this.submitValue;
			})
		
		this.modal.show();
	};
	
	// this.$dialog.on("hidden.bs.modal", function () {
	// 	widget.previous && widget.previous.show();
	// });
	
	validate() {
		this.close();
	};
	
	close() {
		this.modal.hide();
		// Suppress dialog, recreate each time
		this.$dialog.remove();
	};
	
	static buildFromDOMElement($element) {
		return new ConfirmDialog($element, $element.dataset.confirmTitle, $element.dataset.confirmMessage, $element.dataset.confirmSubmit, $element.dataset.confirmSubmitName, $element.dataset.confirmSubmitValue);
	}
	
}

Object.freeze(ConfirmDialog.ACTION);

ready(() => {
	ConfirmDialog.dialogTemplate = '\
	<div id="OrpheusConfirmDialog" class="modal fade">\
	<div class="modal-dialog">\
		<div class="modal-content">\
		<form method="POST">\
			<div class="modal-header">\
				<h4 class="modal-title confirm-title"></h4>\
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="' + t('close') + '"></button>\
			</div>\
			<div class="modal-body">\
				<p class="confirm-message mb10"></p>\
				<div class="row image-wrapper"><div class="col-lg-8 col-lg-offset-2"><img class="img-responsive img-thumbnail"/></div></div>\
			</div>\
			<div class="modal-footer">\
				<button type="button" class="btn btn-outline-secondary confirm_cancel" data-bs-dismiss="modal">' + t('cancel') + '</button>\
				<button type="submit" class="btn btn-primary confirm-validate" value="1">' + t('ok') + '</button>\
			</div>\
		</form>\
		</div>\
	</div>\
</div>';
});

/*
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
*/

ready(() => {
	// Lazy confirm, build it each time
	domService.on(document, 'click', '[data-toggle="confirm"]', function () {
		const $button = this;
		const confirmDialog = ConfirmDialog.buildFromDOMElement($button);
		confirmDialog.open();
	});
	
});
