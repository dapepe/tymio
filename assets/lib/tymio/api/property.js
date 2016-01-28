var UserAPI;

initView(function (gui) {

	/* API Calls for "Property"
	----------------------------------------------------------- */
	PropertyAPI = {
		list: function (filter, callback) {
			var params = {
				'api' : 'property',
				'do'  : 'list'
			};
			if ( filter != null ) {
				initParam(params, 'orderby', filter.id);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'search', filter.search);
				initParam(params, 'domain', filter.domain);
			}
			gui.request(params, function (res) {
				callback(res);
			}, 'array');
		}
	};

});
