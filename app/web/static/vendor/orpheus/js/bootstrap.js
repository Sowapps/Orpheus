/**
 * On ready listener to run code when the page is loaded
 * @param callback
 */
function ready(callback) {
	if( document.readyState !== 'loading' ) {
		callback();
		return;
	}
	document.addEventListener('DOMContentLoaded', callback);
}
