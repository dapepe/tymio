var DomainAPI;

initView(function (gui) {

	/* API Calls for "Domain"
	----------------------------------------------------------- */
	DomainAPI = {
		list: function (filter, callback) {
			var params = {
				'api' : 'domain',
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
		details: function (id, callback) {
			gui.request({
				'api' : 'domain',
				'do'  : 'details',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'object');
		},
		update: function (id, data, callback) {
			gui.request({
				'api' : 'domain',
				'do'  : 'update',
				'id'  : id,
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		add: function (data, callback) {
			gui.request({
				'api' : 'domain',
				'do'  : 'add',
				'data': data
			}, function (res) {
				callback(res);
			});
		},
		remove: function (id, callback) {
			gui.request({
				'api' : 'domain',
				'do'  : 'remove',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},
		restore: function (id, callback) {
			gui.request({
				'api' : 'domain',
				'do'  : 'restore',
				'id'  : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		}
	};

});
