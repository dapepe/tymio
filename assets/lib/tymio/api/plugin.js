var PluginManager;
var PluginAPI;

initView(function (gui) {

	PluginManager = new Class({

		$pluginParameters: undefined,
		$pluginFrame     : undefined,
		$pluginForm      : undefined,
		$pluginId        : undefined,

		initialize: function () {
			this.$pluginParameters = new Element('input', { 'type': 'hidden', 'name': 'parameters' });
			this.$pluginId         = new Element('input', { 'type': 'hidden', 'name': 'id' });

			this.$pluginFrame = new Element('iframe', {
				'name' : 'plugin_frame',
				'src'  : 'about:blank',
				'style': 'display:none; border:0; width:0; height:0;'
			});

			this.$pluginForm = new Element('form', {
				'action': new URI(window.location.href)
					.clearData()
					.toString(),
				'method': 'post',
				'target': 'plugin_frame',
				'style' : 'display:none;'
			})
				.adopt(
					new Element('input', { 'type': 'hidden', 'name': 'api', 'value': 'plugin' }),
					new Element('input', { 'type': 'hidden', 'name': 'do' , 'value': 'execute' }),
					this.$pluginId,
					this.$pluginParameters
				);

			$(document.body).adopt(this.$pluginFrame, this.$pluginForm);
		},

		setData: function (data) {
			this.$pluginParameters.value = JSON.encode(data);
			return this;
		},

		execute: function (id, data) {
			this.$pluginId.value = id;
			this.$pluginForm.submit();
		}

	});

	/* API Calls for "Plugins"
	----------------------------------------------------------- */
	PluginAPI = {

		/**
		 * @param {Object} filter An object with any of these properties:
		 *     - search
		 *     - showinactive
		 *     - entity
		 *     - event
		 *     - ordermode
		 *     - orderby
		 * @param {Function} callback
		 */
		list: function (filter, callback) {
			var params = {
				'api': 'plugin',
				'do' : 'list'
			};
			if ( filter != null ) {
				initParam(params, 'orderby', filter.id);
				initParam(params, 'ordermode', filter.mode);
				initParam(params, 'search', filter.search);
				initParam(params, 'showinactive', ( filter.showinactive ? 1 : 0 ));
				initParam(params, 'entity', filter.entity);
				initParam(params, 'event', filter.event);
			}
			gui.request(params, function (res) {
				callback(res);
			}, 'array');
		},

		details: function (id, callback) {
			gui.request({
				'api': 'plugin',
				'do' : 'details',
				'id' : id
			}, function (res) {
				callback(res);
			}, 'object');
		},

		update: function (id, data, callback) {
			gui.request({
				'api' : 'plugin',
				'do'  : 'update',
				'id'  : id,
				'data': data
			}, function (res) {
				callback(res);
			});
		},

		add: function (data, callback) {
			gui.request({
				'api' : 'plugin',
				'do'  : 'add',
				'data': data
			}, function (res) {
				callback(res);
			});
		},

		deactivate: function (id, callback) {
			gui.request({
				'api': 'plugin',
				'do' : 'deactivate',
				'id' : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},

		activate: function (id, callback) {
			gui.request({
				'api': 'plugin',
				'do' : 'activate',
				'id' : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},

		erase: function (id, callback) {
			gui.request({
				'api': 'plugin',
				'do' : 'erase',
				'id' : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		},

		execute: function (id, data, asJson, callback) {
			gui.request(Object.append({
				'api'  : 'plugin',
				'do'   : 'execute',
				'id'   : id,
				'debug': ( asJson ? 1 : 0 )
			}, data || {}), function (res) {
				callback(res);
			}, 'object');
		}

	};

});
