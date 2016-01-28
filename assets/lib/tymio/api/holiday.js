var HolidayAPI     = null;
var HolidayManager = null;

initView(function (gui) {

	/* API Calls for "Vacation"
	----------------------------------------------------------- */
	HolidayAPI = {
		list: function (filter, callback, callOnFailure) {
			var params = {
				'api' : 'holiday',
				'do'  : 'list'
			};
			if ( filter != null ) {
				initParam(params, 'orderby', filter.id);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'search', filter.search);
				initParam(params, 'showdeleted', filter.showdeleted ? 1 : 0);
				initParam(params, 'domain', filter.domain);
				initParam(params, 'start', filter.start);
				initParam(params, 'end', filter.end);
			}
			gui.request(params, function (res) {
				callback(res);
			}, 'array', callOnFailure);
		},
		details: function (id, callback) {
			gui.request({
				'api' : 'holiday',
				'do'  : 'details',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'object');
		},
		update: function (id, data, callback) {
			gui.request({
				'api' : 'holiday',
				'do'  : 'update',
				'id'  : id,
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		add: function (data, callback) {
			gui.request({
				'api' : 'holiday',
				'do'  : 'add',
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		erase: function (id, callback) {
			gui.request({
				'api' : 'holiday',
				'do'  : 'erase',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		}
	};

	/**
	 * Wrapper to retrieve and cache holidays.
	 */
	HolidayManager = {

		/**
		 * An object mapping JSON-encoded parameters to "HolidayAPI.list()" (domain, start and end times) to promises.
		 *
		 * @type Object
		 */
		holidayPromises: {},

		/**
		 * Removes all cached holiday entries.
		 */
		clear: function () {
			this.holidayPromises = {};
			return this;
		},

		map: function (holidays) {
			var result = {};

			for (var i = 0; i < holidays.length; i++) {
				var domains = holidays[i].Domains;

				for (var j = 0; j < domains.length; j++) {
					var domainId = domains[j].Id;
					if ( !result[domainId] )
						result[domainId] = {};

					result[domainId][holidays[i].Date] = holidays[i];
				}
			}

			return result;
		},

		/**
		 * Returns a list of holidays indexed by their UNIX timestamp dates.
		 *
		 * @param {Boolean} groupByDomain Optional. Default is false.
		 * @type Promise
		 */
		get: function (filter, groupByDomain) {
			// Assumes that "JSON.encode()" is deterministic
			var key = JSON.encode(filter);

			if ( this.holidayPromises[key] instanceof Promise )
				return this.holidayPromises[key];

			var promise = new Promise();
			this.holidayPromises[key] = promise;

			HolidayAPI.list(filter, function (data) {
				if ( (data instanceof Error) || !data.result ) {
					promise.deliver(null);

					// Remove promise for re-query
					delete this.holidayPromises[key];

					return;
				}

				var holidaysByDate = ( groupByDomain ? this.map(data.result) : data.result.reindex('Date') );
				promise.deliver(holidaysByDate);
			}.bind(this), true);

			return promise;
		},

		/**
		 * Checks if a clocking is a holiday.
		 *
		 * @type Boolean
		 */
		isHoliday: function (clocking, holidaysByDate) {
			if ( !holidaysByDate )
				return false;

			var start       = new Date(clocking.Start * 1000);
			var endString   = new Date(clocking.End * 1000).format('%Y-%m-%d');

			var startString = start.format('%Y-%m-%d');
			do {
				if ( holidaysByDate.hasOwnProperty(new Date().parse(startString).format('%s')) )
					return true;

				start.increment('day');
				startString = start.format('%Y-%m-%d');
			} while ( startString < endString );

			return false;
		}

	}

});
