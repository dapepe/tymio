Date.implement({

	isValid: function () {
		return ( !isNaN(this.getTime()) );
	}

});

Array.generate = function (element, length) {
	var result = [];
	for (var i = 0; i < length; i++)
		result.push(element);
	return result;
};

Array.implement({

	diff: function (items) {
		var itemsHash = {};
		for (var i = 0; i < items.length; i++)
			itemsHash[items[i]] = true;

		var result = [];

		for (var i = 0; i < this.length; i++) {
			if ( itemsHash[this[i]] !== true )
				result.push(this[i]);
		}

		return result;
	}

});

Number.range = function (from, to) {
	var result = [];

	if ( from <= to ) {
		for (var i = from; i < to + 1; i++)
			result.push(i);
	} else {
		for (var i = from; i >= to; i--)
			result.push(i);
	}

	return result;
};

if ( typeof window.atob !== 'function' ) {
	window.atob = function (s) {
		throw new Error('atob() not implemented.');
	}
}

if ( typeof window.btoa !== 'function' ) {
	window.btoa = function (s) {
		throw new Error('btoa() not implemented.');
	}
}

String.implement({

	/**
	 * Escapes special (X)HTML characters "<", ">", "&" and the double quotation marks '"'.
	 *
	 * @return {String}
	 */
	htmlSpecialChars: function () {
		// We use MooTools "stripTags()" to remove any additional stuff.
		// TODO: Check for overlong Unicode encodings... however, current browsers
		// seem to not support overlong encodings and thus should be safe.
		return this
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	},

	/**
	 * Inserts invisible breakable spaces into the string.
	 *
	 * @param {Number} maxLength Optional. Default is 10.
	 * @param {String} breakableString Optional. Default is "&#8203;".
	 *     Note that this will be evaluated as part of the regular expression
	 *     substitution string, i.e. a literal "$" and other special characters
	 *     should be escaped.
	 * @returns The wrappable string.
	 * @type String
	 */
	makeWrappable: function (maxLength, breakableString) {
		if ( maxLength == null )
			maxLength = 10;
		if ( breakableString == null )
			breakableString = '&#8203;';

		return this.replace(new RegExp('([^\s]{'+maxLength+'})', 'g'), '$1'+breakableString);
	},

	/**
	 * Converts a string to Base64 encoding.
	 *
	 * See https://developer.mozilla.org/en/DOM/window.btoa#Unicode_Strings on
	 * Character Out Of Range exceptions with UTF-8 and Base64 encoding/decoding.
	 *
	 * @returns The Base64 representation of the string.
	 * @type String
	 * @see fromBase64()
	 */
	toBase64: function () {
		return window.btoa(decodeURIComponent(encodeURIComponent(this)));
	},

	/**
	 * Decodes a Base64 representation to a string.
	 *
	 * @returns The decoded string.
	 * @type String
	 * @see toBase64()
	 */
	fromBase64: function () {
		return decodeURIComponent(encodeURIComponent(window.atob(this)));
	}

});

// Begin: Source/UI/CSSEvents.js
/*
---

name: CSSEvents

license: MIT-style

authors: [Aaron Newton]

requires: [Core/DomReady]

provides: CSSEvents
...
*/

Browser.Features.getCSSTransition = function(){
	Browser.Features.cssTransition = (function () {
		var thisBody = document.body || document.documentElement
			, thisStyle = thisBody.style
			, support = thisStyle.transition !== undefined || thisStyle.WebkitTransition !== undefined || thisStyle.MozTransition !== undefined || thisStyle.MsTransition !== undefined || thisStyle.OTransition !== undefined;
		return support;
	})();

	// set CSS transition event type
	if ( Browser.Features.cssTransition ) {
		Browser.Features.transitionEnd = false;
		if ( Browser.webkit || Browser.chrome ) {
			Browser.Features.transitionEnd = "webkitTransitionEnd";
		} else if ( Browser.firefox ) {
			Browser.Features.transitionEnd = "transitionend";
		} else if ( Browser.opera ) {
			Browser.Features.transitionEnd = "oTransitionEnd";
		}
	}
	Browser.Features.getCSSTransition = Function.from(Browser.Features.transitionEnd);
};

window.addEvent("domready", Browser.Features.getCSSTransition);
