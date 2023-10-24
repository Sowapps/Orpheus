const _Translations = {};

function t(key) {
	return _Translations && _Translations[key] ? _Translations[key] : key;
}

function provideTranslations(translations) {
	Object.assign(_Translations, translations);
}

provideTranslations({
	'ok': "OK",
	'cancel': "Cancel",
});

function debug(t) {
	for (let i in arguments) {
		console.log(arguments[i]);
	}
}

function clone(obj) {
	const target = {};
	for (let i in obj) {
		if( obj.hasOwnProperty(i) ) {
			target[i] = obj[i];
		}
	}
	return target;
}

function basename(string) {
	string = string.replace(/\\/g, '/');
	return string.substring(string.lastIndexOf('/') + 1);
}

function nl2br(str, is_xhtml) {
	//	discuss at: http://phpjs.org/functions/nl2br/
	const breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br/>' : '<br>';
	return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function formatDouble(n) {
	return ("" + n).replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

function isDefined(value) {
	return value !== undefined;
}

function isSet(value) {
	return isDefined(value) && value !== null;
}

function isScalar(value) {
	return (/string|number|boolean/).test(typeof value);
}

function isString(value) {
	return typeof (value) === 'string';
}

function isObject(value) {
	return value != null && typeof (value) === 'object';
}

function isPureObject(value) {
	return isObject(value) && value.constructor === Object;
}

function isArray(value) {
	return isObject(value) && value.constructor === Array;
}

function isFunction(value) {
	return typeof (value) === 'function';
}

function isDomElement(value) {
	return isObject(value) && value instanceof HTMLElement;
}

function isJquery(value) {
	return isObject(value) && typeof (value.jquery) !== 'undefined';
}

function notJquery(value) {
	return isObject(value) && typeof (value.jquery) === 'undefined';
}

function daysInMonth(year, month) {
	return new Date(year, month, 0).getDate();
}

function str2date(value) {
	if( !value ) {
		return null;
	}
	const d = value.split("/");
	if( !d || !d.length || d.length < 3 ) {
		return false;
	}
	return new Date(d[2], d[1] - 1, d[0]);
}

function leadZero(value) {
	value = value * 1;
	return value < 10 ? '0' + value : value;
}

function getLocation(uri) {
	const link = document.createElement("a");
	link.href = uri;
	return link;
}

function checkFlag(value, reference) {
	//console.log(value+" & "+reference+" => "+(value & reference));
	return ((value & reference) === reference);
}

function number_format(number, decimals, dec_point, thousands_sep) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	let n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
//	s = '',
		toFixedFix = function (n, prec) {
			const k = Math.pow(10, prec);
			return '' + Math.floor(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	const s = toFixedFix(n, prec).split('.');
//	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if( s[0].length > 3 ) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if( (s[1] || '').length < prec ) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

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
	for (let key in data) {
		if( !data.hasOwnProperty(key) ) {
			continue;
		}
		const newKey = pattern === undefined ? key : pattern.replace('%s', key);
		
		const value = data[key];
		if( typeof value === 'object' ) {
			flatData(value, newKey + childSuffix, target);
		} else {
			target[newKey] = value;
		}
	}
	return target
}

const cache = {};

String.prototype.capitalize = function () {
	if( typeof this != "string" ) {
		return this;
	}
	return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
};

Date.prototype.getFullDay = function () {
	return "" + this.getFullYear() + leadZero(this.getMonth()) + leadZero(this.getDate());
};

// Source: http://stackoverflow.com/questions/3954438/remove-item-from-array-by-value
Array.prototype.remove = function () {
	let what, a = arguments, L = a.length, ax;
	while (L && this.length) {
		what = a[--L];
		while ((ax = this.indexOf(what)) !== -1) {
			this.splice(ax, 1);
		}
	}
	return this;
};
if( !Array.prototype.indexOf ) {
	Array.prototype.indexOf = function (what, i) {
		i = i || 0;
		const L = this.length;
		while (i < L) {
			if( this[i] === what ) {
				return i;
			}
			++i;
		}
		return -1;
	};
}

// Cookie Object
function Cookie(name) {
	
	this.name = name;
	this.value = "";
	this.path = "/";
	this.expires = null;
	
	this.getValue = function () {
		return this.value;
	};
	
	this.setValue = function (value) {
		this.value = value + "";
		return this;
	};
	
	this.setPath = function (path) {
		this.path = path;
		return this;
	};
	
	this.setExpires = function (date) {
		this.expires = isObject(date) ? date.toUTCString() : date;
		return this;
	};
	
	this.expireInDays = function (days) {
		const date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		this.setExpires(date);
		return this;
	};
	
	this.remove = function () {
		this.setValue("").expireInDays(-1);
		return this;
	};
	
	this.save = function () {
		document.cookie = this.name + "=" + this.value + (this.expires ? "; expires=" + this.expires : "") + "; path=" + this.path;
	};
	
	//http://ppk.developpez.com/tutoriels/javascript/gestion-cookies-javascript/
	this.get = function () {
		const n = this.name + "=";
		const ca = document.cookie.split(';');
		for (let i = 0; i < ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) === ' ') {
				c = c.substring(1, c.length);
			}
			if( c.indexOf(n) === 0 ) {
				return c.substring(n.length, c.length);
			}
		}
		return "";
	};
	
	this.load = function () {
		this.value = this.get();
	};
	this.load();
	
}

Cookie.prototype.toString = function chienToString() {
	return this.getValue();
};

// Set TimeZone Cookie
(function () {
	const offset = new Date().getTimezoneOffset();
	new Cookie("orpheus_timezone").setValue((offset > 0 ? "-" : "+") + leadZero(Math.abs(parseInt(offset / 60))) + ':' + leadZero(Math.abs(offset % 60))).expireInDays(7).save();
})();

/*
// ES implementation required
ready(() => {

$(":button[data-submittext]").each(function () {
	const button = $(this);
	const form = $(this).closest("form");
	const listener = function () {
		if( !button.data("submitted") ) {
			button.data("submitted", 1);
			button.data("submitold", button.html());
			button.text(button.data("submittext"));
		}
		if( !form.data("inputsdisabled") ) {
			form.data("inputsdisabled", 1);
			form.disableInputs();
		}
		$(form).one("cancelsubmit", function () {
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

$("input[data-preview]").change(function () {
	const input = $(this);
	const oFReader = new FileReader();
	oFReader.readAsDataURL(this.files[0]);
	oFReader.onload = function (oFREvent) {
		$(input.data('preview')).attr('src', oFREvent.target.result);
	};
});
});

/* Orpheus Widget & JS Plugins * /

let escapeHTML;
(function ($) {// Preserve our jQuery
	
	escapeHTML = function (str) {
		return $('<p></p>').text(str).html();
	}
	
	$.expr[':'].parents = function (a, i, m) {
		return $(a).parents(m[3]).length < 1;
	};
	
	$.fn.disableInputs = function () {
		return $(this).setFieldsReadonly().filter(":button").addClass("disabled");
	};
	
	$.fn.enableInputs = function () {
		return $(this).setFieldsWritable().filter(":button").removeClass("disabled");
	};
	
	$.fn.setFieldsReadonly = function () {
		return $(this).find(':input').prop("readonly", true);
	};
	
	$.fn.setFieldsWritable = function () {
		return $(this).find(':input').prop("readonly", false);
	};
	
	$.fn.disableFields = function () {
		return $(this).find(':input').prop("disabled", true);
	};
	$.fn.enableFields = function () {
		return $(this).find(':input').prop("disabled", false);
	};
	
})(jQuery);
*/
