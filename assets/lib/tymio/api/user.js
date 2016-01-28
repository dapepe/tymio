var UserAPI;

initView(function (gui) {

	/* API Calls for "User"
	----------------------------------------------------------- */
	UserAPI = {
		list: function (filter, callback) {
			var params = {
				'api' : 'user',
				'do'  : 'list'
			};
			if ( filter != null ) {
				initParam(params, 'orderby', filter.id);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'search', filter.search);
				initParam(params, 'showdeleted', filter.showdeleted ? 1 : 0);
				initParam(params, 'domain', filter.domain);
			}
			gui.request(params, function (res) {
				callback(res);
			}, 'array');
		},
		details: function (id, callback) {
			var params = {
				'api' : 'user',
				'do'  : 'details'
			};

			if ( id != null )
				params.id = id;

			gui.request(params, callback, 'object');
		},
		update: function (id, data, callback) {
			gui.request({
				'api' : 'user',
				'do'  : 'update',
				'id'  : id,
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		add: function (data, callback) {
			gui.request({
				'api' : 'user',
				'do'  : 'add',
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		remove: function (id, callback) {
			gui.request({
				'api' : 'user',
				'do'  : 'remove',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},
		restore: function (id, callback) {
			gui.request({
				'api' : 'user',
				'do'  : 'restore',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		}
	};

});
