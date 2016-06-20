
// A PART OF THIS CODE IS WAITING FOR TRANSFER TO OFFICIAL ORPHEUS.JS
(function($) {
$(function() {

	if( typeof moment !== "undefined" ) {
//		console.log("Set moment locale to "+$('html').attr("lang"));
		moment.locale($('html').attr("lang"));
	}
	
	// Mask input
	if( $.fn.mask ) {
		$.mask.definitions['s']='[0-6]';
		$("input[data-mask]").each(function() {
			var options = {};
//			console.log("mask", this, $(this).data("mask-autoclear"));
			if( $(this).data("mask-autoclear") !== undefined ) {
				options.autoclear	= $(this).data("mask-autoclear");
//				console.log("options", options);
			}
			$(this).mask($(this).data("mask")+"", options);
		});
	}
	
	$("[data-form-group]").each(function() {
		$(this).find(":input").attr('data-parsley-group', $(this).data('form-group'));
	});

});
})(jQuery);

function removeSystemNotification(channel) {
	var notification = $('#SystemNotification-'+channel);
	if( notification.is(":hidden") ) {
		return;
	}
	var notificationContainer = $('#SystemNotificationContainer');
	if( notificationContainer.find(".system_notification:visible").length > 1 ) {
		notification.slideUp(200);
	} else {
		notificationContainer.slideUp(300, function() {
			notification.hide();
		});
	}
}
var systemNotificationExpires = {};
function addSystemNotification(channel, text, type, expire) {
	if( !type ) {
		type = 'info';
	}
	var notificationContainer = $('#SystemNotificationContainer');
	if( !notificationContainer.length ) {
// 		notificationContainer = $('<div id="SystemNotificationContainer"></div>').hide();
// 		$("body").prepend(notificationContainer);
		$("body").prepend($('<div id="SystemNotificationWrapper"><div id="SystemNotificationContainer"></div></div>'));
		notificationContainer = $('#SystemNotificationContainer').hide();
	}
	var notification = $('#SystemNotification-'+channel);
	if( !notification.length ) {
		notificationNotification = $('<div id="SystemNotification-'+channel+'" class="system_notification alert" role="alert"></div>');
		notificationContainer.append(notificationNotification.hide());
	} else {
		notificationNotification.removeClass('alert-'+notificationNotification.data("type"));
	}
	notificationNotification.html(text).addClass('alert-'+type).data("type", type);
	if( notificationContainer.is(":hidden") ) {
		notificationNotification.show();
		notificationContainer.slideDown(300);
// 		notificationContainer.show("drop", {direction: "up"}, 10000);
	} else {
		notificationNotification.slideDown(200);
	}
	if( expire > 0 ) {
		if( systemNotificationExpires[channel] ) {
			// Existing timeout in progress
			clearTimeout(systemNotificationExpires[channel]);
		}
		systemNotificationExpires[channel] = setTimeout(function() {
			removeSystemNotification(channel);
		}, expire);
	}
}
