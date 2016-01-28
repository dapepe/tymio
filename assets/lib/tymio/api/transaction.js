var TransactionAPI;

initView(function (gui) {

	/* API Calls for "Transaction"
	----------------------------------------------------------- */
	TransactionAPI = {

		types: function (userId, start, end, includeDeleted, callback) {
			var params = {
				'api' : 'transaction',
				'do'  : 'types'
			};

			if ( userId != null )
				initParam(params, 'user', userId);
			if ( start != null )
				initParam(params, 'start', start);
			if ( end != null )
				initParam(params, 'end', end);
			if ( includeDeleted != null )
				initParam(params, 'deleted', ( includeDeleted ? 1 : 0 ));

			gui.request(params, callback, 'array');
		},

		list: function (filter, callback) {
			var params = {
				'api' : 'transaction',
				'do'  : 'list'
			};

			if ( filter != null ) {
				initParam(params, 'user', filter.user);
				initParam(params, 'start', filter.start);
				initParam(params, 'end', filter.end);
				initParam(params, 'showdeleted', filter.showdeleted);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'orderby', filter.id);
			}

			gui.request(params, callback, 'array');
		},

		details: function (id, callback) {
			gui.request({
				'api' : 'transaction',
				'do'  : 'details',
				'id'  : id
			}, callback, 'object');
		},

		add: function (clockingIds, commit, callback) {
			var params = {
				'api'      : 'transaction',
				'do'       : 'add'
			};

			initParam(params, 'clockings', clockingIds);
			initParam(params, 'commit', commit);

			gui.request(params, callback, ( commit ? 'array' : 'object' ));
		},

		create: function (transaction, bookings, clockings, callback) {
			var clockingIds = [];
			for (var i = 0; i < clockings.length; i++)
				clockingIds.push( clockings[i] && (typeof(clockings[i]) === 'object') ? clockings[i].Id : clockings[i] );

			var params = {
				'api'      : 'transaction',
				'do'       : 'create'
			};

			initParam(params, 'transaction', {
				'UserId' : transaction.UserId,
				'Start'  : transaction.Start,
				'End'    : transaction.End,
				'Comment': transaction.Comment
			});
			initParam(params, 'bookings', bookings);
			initParam(params, 'clockings', clockingIds);

			gui.request(params, callback);
		},

		remove: function (id, callback) {
			gui.request({
				'api' : 'transaction',
				'do'  : 'remove',
				'id'  : id
			}, callback, 'boolean');
		},

		restore: function (id, callback) {
			gui.request({
				'api' : 'transaction',
				'do'  : 'restore',
				'id'  : id
			}, callback, 'boolean');
		},

		listBookings: function (filter, callback) {
			var params = {
				'api' : 'transaction',
				'do'  : 'list_bookings'
			};

			if ( filter != null ) {
				initParam(params, 'user', filter.user);
				initParam(params, 'start', filter.start);
				initParam(params, 'end', filter.end);
				initParam(params, 'types', filter.types);
				initParam(params, 'showdeleted', filter.showdeleted);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'orderby', filter.id);
			}

			gui.request(params, callback, 'array');
		}

	};

});
