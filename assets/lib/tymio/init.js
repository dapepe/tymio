/**
 * Returns localized text for the specified identifier.
 *
 * @param {String} name The locale string identifier.
 * @param {Object} parameters Optional. The parameters to insert into the
 *     localized string. Default is null.
 * @returns Returns the localized string or the upper-cased identifier
 *     prepended by a "%". Returns null if the identifier cannot be resolved.
 * @type String|null
 */
function _T(name, parameters) {
	var d = $LANG;
	if ( d == null )
		return null;

	var parts = name.split('.');
	for (var i = 0; i < parts.length; i++) {
		d = d[parts[i]];
		if ( d == null )
			return null;
	}

	if ( parameters )
		return String(d).substitute(parameters);
	else
		return d;
}

/**
 * Returns localized text for the specified identifier.
 *
 * @param {String} name The locale string identifier.
 * @param {Object} parameters Optional. The parameters to insert into the
 *     localized string. Default is null.
 * @returns Returns the localized string or the upper-cased identifier
 *     prepended by a "%". Returns null if the identifier cannot be resolved.
 * @type String|null
 */
function _(name, parameters) {
	if ( parameters ) {
		parameters = Object.clone(parameters);
		for (var i in parameters) {
			if ( parameters.hasOwnProperty(i) )
				parameters[i] = String(parameters[i]).htmlSpecialChars();
		}
	}

	return _T(name, parameters);
}

/**
 * Initializes a view
 */
function initView(fct) {
	window.addEvent('guiready', fct);
}

window.addEvent('domready', function () {
	var gui = {
		'_url'      : './index.php',
		'_busy'     : {},
		'_loader'   : $('loader'),
		'_unloading': false
	};

	window.addEvent('beforeunload', function () {
		gui._unloading = true;
	});

	gui.msg = new gx.bootstrap.Message($(document.body), {
		'messageWidth': 400,
		'duration': 3000
	});

	gui.initResult = function (json, callback, resulttype, callOnFailure) {
		var errorMessage = null;

		res = JSON.decode(json);
		if ( typeOf(res) !== 'object' )
			errorMessage = 'Invalid response: '+String(json).htmlSpecialChars();
		else if ( res.error != null )
			errorMessage = 'Server error: '+String(res.error).htmlSpecialChars().replace(/\n/g, '<br />');

		if ( errorMessage != null ) {
			if ( !gui._unloading )
				gui.msg.addMessage(errorMessage, 'error', true, false, false);

			if ( callOnFailure && (typeof(callback) === 'function') )
				callback(new Error(errorMessage));

			return undefined;
		}

		if ( resulttype != null ) {
			var t = typeOf(res.result);
			if ( (t != resulttype) &&
			     ((t !== 'array') || (resulttype !== 'object') || (res.result.length > 0)) ) {
				gui.msg.addMessage('Invalid server response! Server returned "' + String(t).htmlSpecialChars() + '", "' + resulttype + '" expected!', 'error');
				return undefined;
			}
		}

		if ( typeof(callback) === 'function' )
			callback(res); // Call
		else
			return res.result;

		return undefined;
	}

	function updateRequestCounter(indicatorElement, increment) {
		var counter = Number(indicatorElement.getProperty('data-request-counter')) + increment;
		if ( counter < 0 )
			counter = 0;

		indicatorElement.setProperty('data-request-counter', counter);
		return counter;
	}

	gui.request = function (data, callback, resulttype, callOnFailure) {
		var req = new Request({
			'url'       : gui._url,
			'data'      : data,
			'method'    : 'post',
			'onRequest' : function () {
				gui._loader.addClass('active');
				updateRequestCounter(gui._loader, 1);
			},
			'onComplete' : function () {
				if ( updateRequestCounter(gui._loader, -1) === 0 )
					gui._loader.removeClass('active');
			},
			'onFailure' : function () {
				if ( !gui._unloading )
					gui.msg.addMessage('Connection error! Could not retrieve data from server!', 'connection');
			}
		});

		if ( typeof(callback) === 'function' ) {
			var callbackHandler = function (json) {
				gui.initResult(json, callback, resulttype, callOnFailure);
			};
			req.addEvents({
				'success': callbackHandler,
				'failure': callbackHandler
			});
		} else if ( isObject(callback) ) {
			for (evtType in callback)
				req.addEvent(evtType, callback[evtType]);
		}

		req.send();
	};

	// Disable quirks mode with readonly/disabled settings
	gx.com.Timebox.legacyMode = false;

	window.fireEvent('guiready', [ gui ]);
});
