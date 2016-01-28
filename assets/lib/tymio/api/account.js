var AccountAPI;

initView(function (gui) {

	/* API Calls for "Account"
	----------------------------------------------------------- */
	AccountAPI = {
		list: function(filter, callback) {
			var params = {
				'api' : 'account',
				'do'  : 'list'
			};
			if (filter != null) {
				initParam(params, 'orderby', filter.id);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'search', filter.search);
				initParam(params, 'showdeleted', filter.showdeleted);
			}
			gui.request(params, function (res) {
				callback(res);
			}, 'array');
		},
		details: function(callback) {
			gui.request({
				'api' : 'account',
				'do'  : 'details'
			}, function(res) {
				callback(res);
			}, 'object');
		},
		add: function(data, callback) {
		},
		update: function(id, data, callback) {
			gui.request({
				'api' : 'account',
				'do'  : 'update',
				'id'  : id,
				'data': data
			}, function(res) {
				callback(res);
			});
		},
		remove: function(id, callback) {
			gui.request({
				'api' : 'account',
				'do'  : 'remove',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},
		restore: function(id, callback) {
			gui.request({
				'api' : 'account',
				'do'  : 'restore',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		}
	};

});
