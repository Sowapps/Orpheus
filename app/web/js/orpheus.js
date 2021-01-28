
var _Translations = {};

function t(key) {
	return _Translations && _Translations[key] ? _Translations[key] : key;
}

function provideTranslations(translations) {
	$.extend(_Translations, translations);
}

provideTranslations({
	'ok' : "OK",
	'cancel' : "Cancel",
});

function ConfirmDialog(title, message, submitName, submitValue) {
	
	// Initialize class dialog
	if( !ConfirmDialog.dialog ) {
		ConfirmDialog.dialog = $(ConfirmDialog.dialogTemplate);
		$("body").append(ConfirmDialog.dialog);
		ConfirmDialog.dialog.modal({"show": false});
	}
	if( submitValue === undefined ) {
		submitValue	= 1;
	}
	this.dialog = ConfirmDialog.dialog;
	this.title = title;
	this.message = message;
	this.submitName = submitName || "submitValidate";
	this.submitValue = submitValue || 1;
	var widget = this;

	this.previous = null;
	this.imageLink = null;
	
	this.open = function() {
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
	}
	
	this.dialog.on("hidden.bs.modal", function() {
		widget.previous && widget.previous.show();
	});
	
	this.getForm = function() {
		return this.dialog.find("form");
	}
	
	this.validate = function () {
		this.dialog.modal('hide');
	}
	
	this.close = function () {
		this.dialog.modal('hide');
	}
	
}

$(function () {
	ConfirmDialog.dialogTemplate = '\
	<div id="OrpheusConfirmDialog" class="modal fade">\
	<div class="modal-dialog">\
		<div class="modal-content">\
		<form method="POST">\
			<div class="modal-header">\
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
				<h4 class="modal-title confirm_title"></h4>\
			</div>\
			<div class="modal-body">\
				<p class="confirm_message mb10"></p>\
				<div class="row image_wrapper"><div class="col-lg-8 col-lg-offset-2"><img class="img-responsive img-thumbnail"/></div></div>\
			</div>\
			<div class="modal-footer">\
				<button type="button" class="btn btn-default confirm_cancel" data-dismiss="modal">'+t('cancel')+'</button>\
				<button type="submit" class="btn btn-primary confirm_validate" value="1">'+t('ok')+'</button>\
			</div>\
		</form>\
		</div>\
	</div>\
</div>';
});

//$(function() {
//	debug("Doc ready");
//	Dialog.confirm(function() { console.log("Confirmed") }, "Do you confirm what you are doing ?", "This require your attention and you should take care about what we are asking for.");
//});

$(function() {
	
	$("[data-confirm_message]").each(function() {
		var _ = $(this);
		if( _.data("confirmdialog") ) {
			return;
		}
		var confirmDialog = new ConfirmDialog(_.data("confirm_title"), _.data("confirm_message"), _.data("confirm_submit_name"), _.data("confirm_submit_value"));
		_.data("confirmdialog", confirmDialog);
		_.click(function() {
			confirmDialog.open();
			return false;
		});
	});
});

function clone(obj) {
	var target = {};
	for( var i in obj ) {
		if( obj.hasOwnProperty(i) ) {
			target[i] = obj[i];
		}
	}
	return target;
}

function nl2br(str, is_xhtml) {
	//	discuss at: http://phpjs.org/functions/nl2br/
	var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br/>' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function formatDouble(n) {
	return (""+n).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

function getAllProp(Object, dispFunc) {
	var allProp = "";
	for( var key in Object ) {
		if( dispFunc || typeof(Object[key]) != "function" ) {
			allProp += key+ ": " +Object[key]+ "\n";
		}
	}
	return allProp;
}

function isDefined(v) {
	return v !== undefined;
}

function isSet(v) {
	return isDefined(v) && v !== null;
}

function isScalar(obj) {
	return (/string|number|boolean/).test(typeof obj);
}

function isString(v) {
	return typeof(v) === 'string';
}

function isObject(v) {
	return v != null && typeof(v) === 'object';
}

function isPureObject(v) {
	return isObject(v) && v.constructor === Object;
}

function isArray(v) {
	return isObject(v) && v.constructor === Array;
}

function isFunction(v) {
	return typeof(v) === 'function';
}

function isJquery(v) {
	return isObject(v) && typeof(v.jquery) !== 'undefined';
}

function notJquery(v) {
	return isObject(v) && typeof(v.jquery) === 'undefined';
}

function daysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

function str2date(val) {
	if( !val ) { /*debug(val);*/ return null; }
	var d = val.split("/");
	if( !d || !d.length || d.length < 3 ) { return false; }
	return new Date(d[2], d[1]-1, d[0]);
}

function leadZero(val) {
	val = val*1;
	return val < 10 ? '0'+val : val;
}

var getLocation = function(uri) {
	var l = document.createElement("a");
	l.href = uri;
	return l;
};

function bintest(value, reference) {
	return checkFlag(value, reference);
}
function checkFlag(value, reference) {
	//console.log(value+" & "+reference+" => "+(value & reference));
	return ( (value & reference) == reference);
}

function number_format(number, decimals, dec_point, thousands_sep) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
	prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
//	s = '',
	toFixedFix = function (n, prec) {
		var k = Math.pow(10, prec);
		return '' + Math.floor(n * k) / k;
	};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = toFixedFix(n, prec).split('.');
//	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if( (s[1] || '').length < prec ) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

var cache = {};
function requestAutocomplete(what, term, response) {
	if( cache[what] && term in cache[what] ) {
		response(cache[what][term]);
		return;
	}
	if( !cache[what] ) {
		cache[what] = {};
	}
	lastXhr = $.getJSON("remote-search-what="+what+"&term="+term+".json", function( data, status, xhr ) {
		if( xhr === lastXhr && data.code == "ok" ) {
			response(data.other);
			return;
		}
		response([]);
	});
}

String.prototype.capitalize = function () {
	if( typeof this != "string" ) {
		return this;
	}
	return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
};

Date.prototype.getFullDay = function() {
	return ""+this.getFullYear()+leadZero(this.getMonth())+leadZero(this.getDate());
};

var pageScrolled;
function PageScrollTo(sel, paddingTop) {
//	console.log("PageScrollTo()", sel);
	if( pageScrolled ) { return; }
	sel	= $(sel).first();
	if( !sel.length ) { return; }
//	debug("Go scroll");
	pageScrolled=1;
	if( paddingTop === undefined ) {
		paddingTop	= 10;
	}
	var offset = $(sel).offset();
	if( offset && offset.top ) {
		var to = offset.top - paddingTop;
	 	$("html").scrollTop(to);// Firefox 
	 	$("body").animate({scrollTop: to}, 0);// Chrome, Safari 
	}
}

// Cookie Object
function Cookie(name) {
	
	this.name = name;
	this.value = "";
	this.path = "/";
	this.expires = null;
	
	this.getValue = function() {
		return this.value;
	};
	
	this.setValue = function(value) {
		this.value = value+"";
		return this;
	};
	
	this.setPath = function(path) {
		this.path = path;
		return this;
	};
	
	this.setExpires = function(date) {
		this.expires = isObject(date) ? date.toUTCString() : date;
		return this;
	};
	
	this.expireInDays = function(days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		this.setExpires(date);
		return this;
	};
	
	this.remove = function(date) {
		this.setValue("").expireInDays(-1);
		return this;
	};
	
	this.save = function() {
		document.cookie = this.name+"="+this.value+(this.expires ? "; expires="+this.expires : "")+"; path="+this.path;
	};
	
	//http://ppk.developpez.com/tutoriels/javascript/gestion-cookies-javascript/
	this.get = function() {
		var n = this.name + "=";
		var ca = document.cookie.split(';');
		for( var i=0; i < ca.length; i++ ) {
			var c = ca[i];
			while( c.charAt(0) == ' ' ) {
				c = c.substring(1, c.length);
			}
			if( c.indexOf(n) == 0 ) {
				return c.substring(n.length, c.length);
			}
		}
		return "";
	};
	
	this.load = function() {
		this.value = this.get();
	};
	this.load();
	
}
Cookie.prototype.toString = function chienToString() {
	return this.getValue();
};

// Set TimeZone Cookie
(function() {
	var offset = new Date().getTimezoneOffset();
	new Cookie("orpheus_timezone").setValue((offset > 0 ? "-" : "+")+leadZero(Math.abs(parseInt(offset/60)))+':'+leadZero(Math.abs(offset%60))).expireInDays(7).save();
})();

if( $ ) {
	
	/**
	 * Flat given data using pattern
	 *
	 * @param data
	 * @param pattern
	 * @param target The object to store data, or new one is created
	 * @param childSuffix String to append to the key to generate children pattern
	 * @returns {{}}
	 */
	function flatData(data, pattern, target, childSuffix) {
		if( !target ) {
			target = {};
		}
		if( !childSuffix ) {
			childSuffix = '[%s]';
		}
		for( var key in data ) {
			if( !data.hasOwnProperty(key) ) {
				continue;
			}
			var newKey = pattern === undefined ? key : pattern.replace('%s', key);
			
			var value = data[key];
			if( typeof value === 'object' ) {
				flatData(value, newKey + childSuffix, target);
			} else {
				target[newKey] = value;
			}
		}
		return target
	}
	
	/**
	 * Fill all children of the current element with this data using the key and prefix to set the value
	 *
	 * @param prefix
	 * @param data
	 */
	$.fn.fill = function (prefix, data) {
		$(this).each(function () {
			var container = $(this);
			$.each(flatData(data, prefix + '_%s', null, '_%s'), function (key, value) {
				container.find("." + key).each(function () {
					$(this).assignValue(value);
				});
			});
		});
		return $(this);
	};
	
	/**
	 * Fill input using name pattern
	 *
	 * @param data
	 * @param pattern
	 */
	$.fn.fillByName = function (data, pattern = null) {
		$(this).each(function () {
			var container = $(this);
			$.each(flatData(data, pattern), function (key, value) {
				container.find(':input[name="' + key + '"]').each(function () {
					$(this).assignValue(value);
				});
			});
		});
		return $(this);
	};
	
	$.fn.assignValue = function (value) {
		var $element = $(this);
		if( $element.is('img') || $element.is('iframe') ) {
			$element.attr('src', value);
		} else if( $element.is('a') ) {
			$element.attr('href', value);
		} else if( $element.is(':checkbox') ) {
			if( $element.val().toLowerCase() !== 'on' ) {
				// Not default browser value
				$element.prop('checked', $element.val() === value);
			} else {
				// + to convert to int, !! to convert to boolean
				$element.prop('checked', !!+value);
			}
		} else if( $element.is(':input') ) {
			// Fix issue in some dynamic forms
			// input was filled but the change event not called
			$element.val(value).change();
		} else {
			$element.text(value);
		}
		return $(this);
	}
	
	/**
	 * Extract data from all children input using the data-field
	 */
	$.fn.extract = function () {
		var data = {};
		$(this).find(":input[data-field]").each(function () {
			data[$(this).data("field")] = $(this).val();
		});
		return data;
	};

	/**
	 * Reset a form
	 */
	$.fn.resetForm = function() {
		$(this).each(function() {
			var form = $(this).is("form") ? $(this) : $(this).closest("form");
			if( !form.length ) {
				form = $(this).find("form").first();
			}
			if( !form.length ) {
				throw "Unable to reset form, form not found";
			}
			form.get(0).reset();
			form.find(":input[type='hidden']").removeAttr("value");
		});
	};
	
	$(":button[data-submittext]").each(function() {
		var button	= $(this);
		var form	= $(this).closest("form");
		var listener = function() {
			if( !button.data("submitted") ) {
				button.data("submitted", 1);
				button.data("submitold", button.html());
				button.text(button.data("submittext"));
			}
			if( !form.data("inputsdisabled") ) {
				form.data("inputsdisabled", 1);
				form.disableInputs();
			}
			$(form).one("cancelsubmit", function() {
				if( !button.data("submitted") ) {
					return;
				}
				button.html(button.data("submitold"));
				form.data("inputsdisabled", 0);
				form.enableInputs();
			});
		};
		button.click(listener);
		form.submit(listener);
	});
	
	$("input[data-preview]").change(function() {
		var input = $(this);
		var oFReader = new FileReader();
		oFReader.readAsDataURL(this.files[0]);
		oFReader.onload	= function(oFREvent) {
			$(input.data('preview')).attr('src', oFREvent.target.result);
		};
	});
}

// Source: http://stackoverflow.com/questions/3954438/remove-item-from-array-by-value
Array.prototype.remove = function() {
	var what, a = arguments, L = a.length, ax;
	while( L && this.length ) {
		what = a[--L];
		while( (ax = this.indexOf(what)) !== -1 ) {
			this.splice(ax, 1);
		}
	}
	return this;
};
if( !Array.prototype.indexOf ) {
	Array.prototype.indexOf = function(what, i) {
		i = i || 0;
		var L = this.length;
		while (i < L) {
			if(this[i] === what) return i;
			++i;
		}
		return -1;
	};
}

function centerInViewport(el) {
	el = $(el);
	debug(el);
	var viewportWidth = jQuery(window).width(),
	viewportHeight = jQuery(window).height(),
	elWidth = el.width(),
	elHeight = el.height(),
	elOffset = el.offset();
	jQuery(window)
	.scrollTop(elOffset.top + (elHeight/2) - (viewportHeight/2))
	.scrollLeft(elOffset.left + (elWidth/2) - (viewportWidth/2));
}

function moveOnMouse(el, e) {
	el = $(el);
	var elHeight = el.height(),
	elOffset = el.offset();
	var scrollTop = jQuery(window).scrollTop()-((e.pageY - (elHeight/2)) - elOffset.top);
	$(window).scrollTop(scrollTop);
}

if( typeof KeyEvent == "undefined" ) {
    var KeyEvent = {
        DOM_VK_CANCEL: 3,
        DOM_VK_HELP: 6,
        DOM_VK_BACK_SPACE: 8,
        DOM_VK_TAB: 9,
        DOM_VK_CLEAR: 12,
        DOM_VK_RETURN: 13,
        DOM_VK_ENTER: 14,
        DOM_VK_SHIFT: 16,
        DOM_VK_CONTROL: 17,
        DOM_VK_ALT: 18,
        DOM_VK_PAUSE: 19,
        DOM_VK_CAPS_LOCK: 20,
        DOM_VK_ESCAPE: 27,
        DOM_VK_SPACE: 32,
        DOM_VK_PAGE_UP: 33,
        DOM_VK_PAGE_DOWN: 34,
        DOM_VK_END: 35,
        DOM_VK_HOME: 36,
        DOM_VK_LEFT: 37,
        DOM_VK_UP: 38,
        DOM_VK_RIGHT: 39,
        DOM_VK_DOWN: 40,
        DOM_VK_PRINTSCREEN: 44,
        DOM_VK_INSERT: 45,
        DOM_VK_DELETE: 46,
        DOM_VK_0: 48,
        DOM_VK_1: 49,
        DOM_VK_2: 50,
        DOM_VK_3: 51,
        DOM_VK_4: 52,
        DOM_VK_5: 53,
        DOM_VK_6: 54,
        DOM_VK_7: 55,
        DOM_VK_8: 56,
        DOM_VK_9: 57,
        DOM_VK_SEMICOLON: 59,
        DOM_VK_EQUALS: 61,
        DOM_VK_A: 65,
        DOM_VK_B: 66,
        DOM_VK_C: 67,
        DOM_VK_D: 68,
        DOM_VK_E: 69,
        DOM_VK_F: 70,
        DOM_VK_G: 71,
        DOM_VK_H: 72,
        DOM_VK_I: 73,
        DOM_VK_J: 74,
        DOM_VK_K: 75,
        DOM_VK_L: 76,
        DOM_VK_M: 77,
        DOM_VK_N: 78,
        DOM_VK_O: 79,
        DOM_VK_P: 80,
        DOM_VK_Q: 81,
        DOM_VK_R: 82,
        DOM_VK_S: 83,
        DOM_VK_T: 84,
        DOM_VK_U: 85,
        DOM_VK_V: 86,
        DOM_VK_W: 87,
        DOM_VK_X: 88,
        DOM_VK_Y: 89,
        DOM_VK_Z: 90,
        DOM_VK_CONTEXT_MENU: 93,
        DOM_VK_NUMPAD0: 96,
        DOM_VK_NUMPAD1: 97,
        DOM_VK_NUMPAD2: 98,
        DOM_VK_NUMPAD3: 99,
        DOM_VK_NUMPAD4: 100,
        DOM_VK_NUMPAD5: 101,
        DOM_VK_NUMPAD6: 102,
        DOM_VK_NUMPAD7: 103,
        DOM_VK_NUMPAD8: 104,
        DOM_VK_NUMPAD9: 105,
        DOM_VK_MULTIPLY: 106,
        DOM_VK_ADD: 107,
        DOM_VK_SEPARATOR: 108,
        DOM_VK_SUBTRACT: 109,
        DOM_VK_DECIMAL: 110,
        DOM_VK_DIVIDE: 111,
        DOM_VK_F1: 112,
        DOM_VK_F2: 113,
        DOM_VK_F3: 114,
        DOM_VK_F4: 115,
        DOM_VK_F5: 116,
        DOM_VK_F6: 117,
        DOM_VK_F7: 118,
        DOM_VK_F8: 119,
        DOM_VK_F9: 120,
        DOM_VK_F10: 121,
        DOM_VK_F11: 122,
        DOM_VK_F12: 123,
        DOM_VK_F13: 124,
        DOM_VK_F14: 125,
        DOM_VK_F15: 126,
        DOM_VK_F16: 127,
        DOM_VK_F17: 128,
        DOM_VK_F18: 129,
        DOM_VK_F19: 130,
        DOM_VK_F20: 131,
        DOM_VK_F21: 132,
        DOM_VK_F22: 133,
        DOM_VK_F23: 134,
        DOM_VK_F24: 135,
        DOM_VK_NUM_LOCK: 144,
        DOM_VK_SCROLL_LOCK: 145,
        DOM_VK_COMMA: 188,
        DOM_VK_PERIOD: 190,
        DOM_VK_SLASH: 191,
        DOM_VK_BACK_QUOTE: 192,
        DOM_VK_OPEN_BRACKET: 219,
        DOM_VK_BACK_SLASH: 220,
        DOM_VK_CLOSE_BRACKET: 221,
        DOM_VK_QUOTE: 222,
        DOM_VK_META: 224
    };
}
var Modifier = {
	CONTROL : 1,
	SHIFT : 2,
	ALT : 4,
	META : 8,
}

/* Orpheus Widget & JS Plugins */

var escapeHTML;
(function ($) {// Preserve our jQuery
	
	escapeHTML	= function(str) {
		return $('<p></p>').text(str).html();
	}
	
	$.expr[':'].parents = function(a,i,m){
	    return $(a).parents(m[3]).length < 1;
	};
	
	$.fn.disableInputs = function() {
		return $(this).setFieldsReadonly().filter(":button").addClass("disabled");
	};
	
	$.fn.enableInputs = function() {
		return $(this).setFieldsWritable().filter(":button").removeClass("disabled");
	};
	
	$.fn.setFieldsReadonly = function() {
		return $(this).find(':input').prop("readonly", true);
	};
	
	$.fn.setFieldsWritable = function() {
		return $(this).find(':input').prop("readonly", false);
	};

	$.fn.disableFields = function() {
		return $(this).find(':input').prop("disabled", true);
	};
	$.fn.enableFields = function() {
		return $(this).find(':input').prop("disabled", false);
	};

	/*
	 * This function run completable if cond is true and pass it complete to set the complete callback, but if cond is false, it just calls the complete callback immediatly
	 */
	$.fn.cond = function(cond, completable, complete) {
		this.completable	= completable;
		this.complete		= complete;
		if( cond && ( cond == "boolean" || (cond instanceof jQuery && cond.length) ) ) {
			return this.completable(this.complete);
		}
		return this.complete();
	};
	$.cond = $.fn.cond;
	
	$.fn.showIf = function(cond) {
		cond ? $(this).show() : $(this).hide();
	};
	// Call when element is shown
	$.fn.shown = function(callback) {
		$(this).bind("shown", callback);
		if( $(this).is(":visible") ) {
			this.callback	= callback;
			this.callback();
		}
	};
	// Scroll to element
	$.fn.scrollTo = function(option, event) {
		if( !option ) {
			option = "center";
		}
		var el = $(this).first();
		if( !el.length ) {
			return;
		}
		var viewportWidth = $(window).width(), viewportHeight = $(window).height(),
			elWidth = $(el).width(), elHeight = $(el).height(), elOffset = $(el).offset();
		if( option === "top" ) {
			$(window).scrollTop(elOffset.top + elHeight / 2 - 100);
		} else {
			// Default is center
			$(window).scrollTop(elOffset.top + elHeight / 2 - viewportHeight / 2);
		}
		$(window).scrollLeft(elOffset.left + elWidth / 2 - viewportWidth / 2);
	};
	
	// Apply on load and on change
	$.fn.watch	= function(cb, sel) {
		sel ? $(this).on("change", sel, cb) : $(this).change(cb);
		$(this).each(function() {
			this.cb	= cb;
			this.cb();
		});
	};
	
	$.fn.pressEnter	= function(cb) {
		return $(this).keydown(function (e) {
			if( e.which === KeyEvent.DOM_VK_RETURN ) {
				e.preventDefault();
				this.callback = cb;
				return this.callback(e);
			}
		});
	};
	
	$.fn.pressKey = function(key, modifiers, cb) {
		if( !cb ) {
			cb = modifiers;
			modifiers = 0;
		}
		return $(this).keydown(function(e) {
			if( e.which === key ) {
				if( modifiers ) {
					if( bintest(modifiers, Modifier.CONTROL) && !e.ctrlKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.ALT) && !e.altKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.SHIFT) && !e.shiftKey ) {
						return;
					}
					if( bintest(modifiers, Modifier.META) && !e.metaKey ) {
						return;
					}
				}
				e.preventDefault();
				this.callback	= cb;
				return this.callback(e);
			}
		});
	};
	
	$.fn.outerHTML = function() {
		return $('<div />').append(this.eq(0).clone()).html();
	};

	$(function() {
		$("input.autocomplete.auto").shown(function() {
			if( $(this).data("autocomplete-auto") ) { return; }
			$(this).data("autocomplete-auto", 1);
			var _ = $(this);
			_.autocomplete({
				minLength:	2,
				source:		function(request, response) {
					var query = _.data("query") ? "&"+_.data("query") : "";
					// Targetting the autocomplete itself
					requestAutocomplete(_.data('what'), request.term+query, response, _.data("label"));
				}
			});
		});
		
		$('input[type=url]').watch(function() {
			var value = $(this).val();
			if( value.length && value.indexOf("://") < 0 ) {
				$(this).val(value="http://"+value);
			}
			$($(this).data("linkbtn") || $(this).next("a")).attr("href", value);
		});
	});
})(jQuery);
