
function b64_encode(input) {
	if( !input ) {
		debug("b64_encode() Error : Invalid input ");
//		debug("caller is "+arguments.callee.caller.toString());
		return "";
	}
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	//input = escape(input);
	var output="", chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	for( var i = 0; i < input.length; ) {
		//chr1 = chr2 = chr3 = enc1 = enc2 = enc3 = enc4 = "";
		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if( isNaN(chr2) ) {
		   enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
		   enc4 = 64;
		}
		output += keyStr.charAt(enc1)+keyStr.charAt(enc2)+keyStr.charAt(enc3)+keyStr.charAt(enc4);
	}
	return output;
}

function b64_decode(input) {
	if( !input ) {
		debug("b64_decode() Error : Invalid input ");
//		debug("caller is "+arguments.callee.caller.toString());
		return "";
	}
	var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var output="", chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	var base64test = /[^A-Za-z0-9\+\/\=]/g;
	if( base64test.exec(input) ) {
		debug("There were invalid base64 characters in the input text.\nValid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\nExpect errors in decoding.");
	}
	input = input.replace(base64test, "");

	for( var i = 0; i < input.length; ) {
		//chr1 = chr2 = chr3 = enc1 = enc2 = enc3 = enc4 = "";
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output += String.fromCharCode(chr1);

		if (enc3 != 64) {
			output += String.fromCharCode(chr2);
		}
		if (enc4 != 64) {
			output += String.fromCharCode(chr3);
		}
	}
	//return unescape(output);
	return output;
}