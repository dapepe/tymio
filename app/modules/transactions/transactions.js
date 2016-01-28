initView(function (gui) {

	var pluginManager = new PluginManager();

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_current: null,
		_ready  : false,

		list: function () {
			if ( !this._ready )
				return;

			pluginManager.setData(null);
			tabTransaction.empty();

			var showDeleted    = chkShowDel.get();

			var filter = tabTransaction.getFilter();
			filter.showdeleted = showDeleted;
			filter.user        = selUser.getId();

			filter.start       = monthPicker.getStart();
			filter.end         = monthPicker.getEnd();

			this.updateList.future()(
				TransactionAPI.list.toApiPromise(TransactionAPI, [ filter ]),
				TransactionAPI.types.toApiPromise(TransactionAPI, [ null, null, null, showDeleted ])
			);
		},
		updateList: function (transactions, bookingTypes) {
			if ( typeOf(bookingTypes) !== 'array' )
				return;

			viewDetails.setBookingTypesById(bookingTypes.reindex('Id'));

			pluginManager.setData({
				'user'        : selUser.getSelected(),
				'monthRange'  : {
					'Start'   : monthPicker.getStart(),
					'End'     : monthPicker.getEnd()
				},
				'transactions': transactions
			});
			tabTransaction.setData(transactions);
			tabTransaction.updateExpansionControl();
		},
		open: function (transaction) {
			viewDetails.show(transaction);
		},
		add: function () {
			wizard.show(selUser.getSelected() || AUTHENTICATED_USER, []);
		},
		remove: function () {
			var checkboxes = $(tabTransaction).getElements('.transaction_checkbox:checked');
			if ( !checkboxes.length ||
			     !confirm(_('transaction.remove.prompt', { 'n': checkboxes.length })) )
				return;

			for (var i = 0; i < checkboxes.length; i++)
				TransactionAPI.remove(checkboxes[i].value, ui.bound.list);
		},
		rebook: function () {
			rebookPopup.popup.show();
		},
		restore: function () {
		}
	};

	ui.bound = {
		'list'   : ui.list.bind(ui),
		'open'  : ui.open.bind(ui),
		'remove': ui.remove.bind(ui),
		'rebook': ui.rebook.bind(ui)
	};

	function getCompatibleRebookTypes(type) {
		if ( type.Unit == null )
			return [];

		var types = [];

		var sourceTypes = rebookFromType.options.localOptions;
		for (var i = 0; i < sourceTypes.length; i++) {
			if ( (sourceTypes[i].Id !== type.Id) &&
			     (sourceTypes[i].Unit === type.Unit) )
				types.push(sourceTypes[i]);
		}

		return types;
	}

	function updateTypeSelectionList(widget, type) {
		var selectedId = widget.getId();

		var types = getCompatibleRebookTypes(type);

		widget.options.localOptions = types;
		widget.buildList(types);
		widget.set(null);

		for (var i = 0; i < types.length; i++) {
			if ( types[i].Id === selectedId ) {
				widget.set(types[i]);
				return;
			}
		}

		if ( types[0] )
			widget.set(types[0]);
	}

	function rebook() {
		var user     = rebookPopup.user.getSelected();
		var fromType = rebookPopup.fromType.getSelected();
		var toType   = rebookPopup.toType.getSelected();

		if ( !user ||
		     (fromType == null) || (toType == null) ||
			 (fromType.Unit !== toType.Unit) )
			return;

		var rawDuration = Number(rebookPopup.duration.value.toBookingValue(fromType.DisplayUnit));
		if ( isNaN(rawDuration) )
			return;

		var duration = rawDuration.convertDuration(fromType.DisplayUnit, fromType.Unit)
		if ( !isFinite(duration) )
			return;

		var transaction = {
			'UserId'           : user.Id,
			'Start'            : rebookPopup.startDate.get('%s'),
			'End'              : rebookPopup.endDate.get('%s'),
			'Comment'          : rebookPopup.comment.value
		};

		var bookings = [
			{ // Source type booking
				'BookingTypeId': fromType.Id,
				'Label'        : rebookPopup.comment.value,
				'Value'        : -duration
			},
			{ // Target type booking
				'BookingTypeId': toType.Id,
				'Label'        : rebookPopup.comment.value,
				'Value'        : duration
			}
		];

		TransactionAPI.create(transaction, bookings, [], function (result) {
			if ( result.result === true ) {
				rebookPopup.popup.hide();
				ui.list();
			}
		}.bind(this));
	}

	function createPluginButton(btnOptions, plugin) {
		var optPlugin = btnOptions.add(plugin.Name || plugin.Identifier, '')
			.addEvent('click', function () {
				pluginManager.execute(plugin.Id);
			});
	}

	function createPluginButtons(btnOptions) {
		PluginAPI.list({
			'entity' : 'menu',
			'event'  : 'transactions',
			'orderby': 'Priority'
		}, function (result) {
			if ( !result || result.error )
				return;

			var plugins = result.result;
			if ( (typeOf(plugins) !== 'array') || !plugins.length )
				return;

			// Create divider
			btnOptions.add();

			for (var i = 0; i < plugins.length; i++)
				createPluginButton(btnOptions, plugins[i]);
		});
	}

	/* Details popup
	----------------------------------------------------------- */

	var viewDetails = new TransactionPopup(gui);
	var wizard      = new TransactionWizard(gui)
		.addEvent('save', ui.bound.list);

	var tabTransaction = new TransactionTable('tabTransaction', {
		'measuringRow'   : [
			'50px',
			'180px',
			'100px',
			'250px',
			'200px',
			''
		],
		'onFilter'       : ui.bound.list,
		'onDblclick'     : ui.bound.open,
		'onUserclick'    : ui.bound.open
	});

	/* Rebook popup
	----------------------------------------------------------- */

	var rebookUser = new gx.bootstrap.Select('rebook_user', {
		'width'         : '180px',
		'icon'          : 'user',
		'label'         : {
			'text'      : _('entity.user.singular'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'requestData'   : {
			'api'       : 'user',
			'do'        : 'list'
		},
		'requestParam'  : 'search',
		'listFormat'    : getFullName,
		'formatID'      : function (user) {
			return ( user ? user.Id : '' );
		},
		'onSelect'      : function (user) {
			rebookPopup.fromType.options.localOptions = [];
			rebookPopup.fromType.buildList(rebookPopup.fromType.options.localOptions);
			rebookPopup.fromType.set(null);

			TransactionAPI.types(user.Id, null, null, false, function (result) {
				if ( !result || result.error || !result.result )
					return;

				var types = result.result;

				rebookPopup.fromType.options.localOptions = types;
				rebookPopup.fromType.buildList(types);
				rebookPopup.fromType.set(types[0]);
			});
		}
	});

	var rebookFromType = new gx.bootstrap.Select('rebook_from_type', {
		'icon'          : 'tag',
		'label'         : {
			'text'      : _('transaction.rebook.from_type'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'localOptions'  : [],
		//'requestParam'  : 'search',
		'listFormat'    : function (type) {
			return ( _('clocking.type.'+type.Identifier) || type.Label || type.Identifier );
		},
		'formatID'      : function (type) {
			return ( type ? type.Id : false );
		},
		'onSelect'      : function (type) {
			updateTypeSelectionList(rebookPopup.toType, type);

			var duration = Number(type.Balance);
			rebookPopup.duration.value = ( isFinite(duration) ? duration : 0 ).formatDurationFrom(type.Unit, type.DisplayUnit);
		}
	});

	var rebookToType = new gx.bootstrap.Select('rebook_to_type', {
		'icon'          : 'tag',
		'label'         : {
			'text'      : _('transaction.rebook.to_type'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'localOptions'  : [],
		//'requestParam'  : 'search',
		'listFormat'    : function (item) {
			return ( _('clocking.type.'+item.Identifier) || item.Label || item.Identifier );
		},
		'formatID'      : function (item) {
			return item ? item.Id : false;
		},
		'onSelect'      : function (item) {
		}
	});

	var rebookPopup = {
		'user'              : rebookUser,
		'fromType'          : rebookFromType,
		'toType'            : rebookToType,
		'startDate'         : new gx.bootstrap.DatePicker('rebook_start', {
			'icon'          : 'play',
			'label'         : {
				'text'      : _('field.start'),
				'class'     : 'dialog_label'
			},
			'width'         : '160px',
			'timePicker'    : false,
			'format'        : '%a %Y-%m-%d',
			'return_format' : '%Y-%m-%d %H:%M:%S'
		}),
		'endDate'           : new gx.bootstrap.DatePicker('rebook_end', {
			'icon'          : 'stop',
			'label'         : {
				'text'      : _('field.end'),
				'class'     : 'dialog_label'
			},
			'width'         : '160px',
			'timePicker'    : false,
			'format'        : '%a %Y-%m-%d',
			'return_format' : '%Y-%m-%d %H:%M:%S'
		}),
		'duration'          : $('rebook_duration'),
		'comment'           : $('rebook_comment'),
		'popup'             : new gx.bootstrap.Popup({
			'width'         : 600,
			'content'       : $('rebook_popup_details'),
			'title'         : _('action.rebook'),
			'footer'        : __({ 'children': {
				'btnClose'  : { 'tag': 'input', 'type': 'button', 'class': 'btn f_l', 'value': 'Close', 'onClick': function () {
					rebookPopup.popup.hide();
				} },
				'btnOk'     : { 'tag': 'input', 'type': 'button', 'class': 'btn btn-primary m2_l', 'value': _('action.rebook'), 'id': 'rebook_save', 'onClick': function () {
					rebook();
				} }
			}}),
			'closable'      : true,
			'onShow'        : function () {
				var user = selUser.getSelected();
				rebookPopup.user.set( user || rebookPopup.user.getSelected() );
			}
		})
	};

	/* Filters
	----------------------------------------------------------- */

	var chkShowDel = new gx.bootstrap.CheckButton('chkShowDel', {
		'label'   : _('filter.deleted'),
		'onChange': ui.bound.list
	});

	var monthPicker = new gx.bootstrap.MonthPicker('mprRange', {
		'onSelect': ui.bound.list
	});

	var selUser = new gx.bootstrap.Select('selUser', {
		'width'         : '130px',
		'icon'          : 'user',
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'allowEmpty'    : '--- '+_('field.pleaseselect')+' ---',
		'requestData'   : {
			'api'       : 'user',
			'do'        : 'list'
		},
		'requestParam'  : 'search',
		'listFormat'    : getFullName,
		'formatID'      : function (user) {
			return ( user ? user.Id : '' );
		},
		'onNoselect'    : ui.bound.list,
		'onSelect'      : ui.bound.list
	});

	/* Options menu
	----------------------------------------------------------- */

	var btnNewTransaction = $('btnNewTransaction')
		.addEvent('click', ui.add.bind(ui));
	var btnOptions = new gx.bootstrap.MenuButton('btnOptions', {
		'label'      : _('field.options'),
		'style'      : 'primary',
		'orientation': 'right'
	});
	var optRemove      = btnOptions.add(_('action.remove'), 'trash').addEvent('click', ui.bound.remove);
	var optRebook      = btnOptions.add(_('action.rebook'), 'book').addEvent('click', ui.bound.rebook);
	var optExpandAll   = btnOptions.add(_('action.expandall'), 'plus').addEvent('click', tabTransaction.expandAll.bind(tabTransaction));
	var optCollapseAll = btnOptions.add(_('action.collapseall'), 'minus').addEvent('click', tabTransaction.collapseAll.bind(tabTransaction));

	createPluginButtons(btnOptions);

	if ( !AUTHENTICATED_USER.IsAdmin ) {
		btnNewTransaction.hide();
		$(optRemove).hide();
		$(optRebook).hide();
	}

	/* Automatically adapt window height */
	var h = 130;
	var l = 200;
	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if ( ht < l )
			ht = l;
		tabTransaction.setHeight(ht+'px');
	}
	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	ui._ready = true;
	ui.list();
});
