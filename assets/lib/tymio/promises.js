/**
 * Function extensions.
 */
Function.implement({

	callWithArgs: function (parameters) {
		return this.apply(this, parameters);
	}

});

/**
 * Promise extensions.
 */
if ( typeof(Promise) !== 'undefined' ) {

	Promise.implement({

		/*
		  Function: initReq
			*private*
			Initialize the request. If successful, calls yield the value
			received from the server after applying all operations on the value
			first. On request failure, yields undefined and fires an error
			event passing itself as data.

		  Parameters:
			req - MooTools Request object.
		*/
		initReq: function(req) {
			this.__req = req;

			var successHandler = function (r) {
				var json = (typeOf(r) == 'object') ? r : ((!req.options.bare) ? JSON.decode(r) : r),
					v;
				if ( Promise.deref !== null && req.options.bare !== true && !json.error ) {
					var temp = $get.apply(null, [json].concat(Promise.deref.split(".")));
					if( temp !== null && temp !== undefined )
						v = temp;
				} else {
					v = json;
				}
				this.deliver(this.applyOps(v));
			}.bind(this);

			req.addEvent('onSuccess', successHandler);
			req.addEvent('onFailure', function(responseText) {
				if ( responseText && (typeof(responseText) === 'object') && (responseText.status === 0) ) {
					successHandler(responseText.response);
					return;
				}

				this.deliver(undefined);
				this.fireEvent('error', this);
			}.bind(this));
		},

		/**
		 * Wrapper to chain / concatenate asynchronous calls.
		 *
		 * <code>
		 *     new Promise(new Request({
		 *         'url': 'http://example.com',
		 *         'bare': true
		 *     })).then(function (xhr) {
		 *         // This may itself return a promise for chaining.
		 *     }).then(function () {
		 *         // ...
		 *     });
		 * </code>
		 *
		 * @param Function callback
		 * @returns Returns a promise.
		 * @type Promise
		 */
		then: function (callback) {
			var args = Array.clone(arguments);

			// Replace "callback" parameter with the promise
			args[0] = this;

			var result = callback.future().callWithArgs(args);
			if ( result instanceof Promise )
				return result;

			// Call "Promise.deliver()" explicitly instead of passing the result
			// to the constructor to support undefined results.
			var p = new Promise();
			p.deliver(result);
			return p;
		}

	});

}
