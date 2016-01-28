initView(function (gui) {
	selFilter = $('selFilter');

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_current: null,
		_ready  : false,

		inSave  : false,

		clockingTable: new ClockingTable('tabClocking', {
			'showUser': AUTHENTICATED_USER.IsAdmin,
			'onFilter': function (column, widget) {
				ui.list();
			},
			'onOpen': function (clocking, widget) {
				ui.open(clocking);
			},
			'onDblclick': function (clocking, widget) {
				ui.open(clocking);
			}
		}),

		list: function () {
			if ( !this._ready )
				return;

			var filter = ui.clockingTable.getFilter();
			filter.showdeleted  = chkShowDel.get();
			filter.showbooked   = selFilter.get('value');
			filter.user         = selUser.getId();

			var dateRangeFilter = {
				'start': monthPicker.getStart(),
				'end'  : monthPicker.getEnd()
			};

			var user = selUser.getSelected();

			this.clockingTable.setData(user, ClockingAPI.list.toApiPromise(ClockingAPI, [ Object.append(filter, dateRangeFilter) ]));

			TransactionAPI.types(null, null, null, null, function (data) {
				if ( !data || !data.result || (typeOf(data.result) !== 'array') )
					return;

				var result = {};

				for (var i = 0; i < data.result.length; i++) {
					var bookingType = data.result[i];
					if ( bookingType.Id != null )
						result[bookingType.Id] = bookingType;
				}

				transactionDetails.setBookingTypesById(result);
			});
		},

		open: function (clocking) {
			ui._current = clocking;

			viewDetails.clockingId.value = clocking.Id;

			viewDetails.selUser.set(clocking.User);
			viewDetails.clockingType.set(clocking.Type);

			viewDetails.selApprovalStatus.set(ClockingManager.approvalStatusToItem(clocking.ApprovalStatus));

			viewDetails.dbxStart.set(new Date(clocking.Start * 1000));
			viewDetails.dbxEnd.set(new Date(clocking.End * 1000));
			viewDetails.tbxBreak.set(clocking.Breaktime);
			$('clocking_edit_comment').value = clocking.Comment;
			viewDetails.removeButton.show();

			if ( clocking.Booked || clocking.Frozen ) {
				viewDetails.selApprovalStatus.disable();
				viewDetails.selUser.disable();
				viewDetails.clockingType.disable();
				viewDetails.dbxStart.setReadOnly(true);
				viewDetails.dbxEnd.setReadOnly(true);
				viewDetails.tbxBreak.disable();
				$('clocking_edit_comment').setProperty('readonly', 'readonly');
				$('clocking_save').setProperty('disabled', 'disabled');
				viewDetails.removeButton.setProperty('disabled', 'disabled');

			} else {
				if ( AUTHENTICATED_USER.IsAdmin )
					viewDetails.selApprovalStatus.enable();
				else
					viewDetails.selApprovalStatus.disable();

				viewDetails.selUser.enable();
				viewDetails.clockingType.enable();
				viewDetails.dbxStart.setReadOnly(false);
				viewDetails.dbxEnd.setReadOnly(false);
				viewDetails.tbxBreak.enable();
				$('clocking_edit_comment').erase('readonly');
				$('clocking_save').erase('disabled');
				viewDetails.removeButton.erase('disabled');

			}

			var dateFormat = _('time.format.default');
			var creationDateText = (
				clocking.Creationdate == null
				? ''
				: _T('clocking.details.creationdate', { 'date': new Date(1000 * clocking.Creationdate).format(dateFormat) })
			);
			viewDetails.dateStatus.set({
				'title': creationDateText,
				'text' : (
					clocking.LastChanged == null
					? creationDateText
					: _T('clocking.details.lastchanged', { 'date': new Date(1000 * clocking.LastChanged).format(dateFormat) })
				)
			});

			viewDetails.popup.show();
		},

		add: function () {
			ui._current = null;

			var now = new Date();

			viewDetails.clockingId.value   = '';

			viewDetails.selUser
				.set( selUser.getSelected() || AUTHENTICATED_USER )
				.enable();
			viewDetails.clockingType
				.set(null)
				.enable();
			viewDetails.selApprovalStatus
				.set(ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_PRELIMINARY))
				.enable();

			if ( AUTHENTICATED_USER.IsAdmin )
				viewDetails.selApprovalStatus.enable();
			else
				viewDetails.selApprovalStatus.disable();

			viewDetails.dbxStart
				.set(now)
				.setReadOnly(false);
			viewDetails.dbxEnd
				.set(now)
				.setReadOnly(false);
			viewDetails.tbxBreak
				.set(0)
				.enable();

			$('clocking_edit_comment')
				.erase('readonly')
				.value = '';

			$('clocking_save').erase('disabled');
			viewDetails.removeButton.hide();

			viewDetails.popup.show();
		},

		save: function () {
			if ( this.inSave )
				return;

			this.inSave = true;

			ClockingAPI.update(viewDetails.clockingId.value, {
				'UserId'        : viewDetails.selUser.getId(),
				'TypeId'        : viewDetails.clockingType.getId(),
				'Start'         : viewDetails.dbxStart.get('%s'),
				'End'           : viewDetails.dbxEnd.get('%s'),
				'Breaktime'     : viewDetails.tbxBreak.get(),
				'Comment'       : $('clocking_edit_comment').value,
				'ApprovalStatus': viewDetails.selApprovalStatus.getId()
			}, function (data) {
				ui.inSave = false;

				// "data.result" can be true or an ID
				if ( data && (typeof(data) === 'object') && data.result ) {
					viewDetails.popup.hide();
					ui.list();
				}
			}, true);
		},

		getSelectedClockingIds: function () {
			var checkboxes  = $$('.clocking_checkbox:checked');
			return ( checkboxes.length === 0 ? [] : checkboxes.getProperty('value') );
		},

		approve: function (clockingIds, status) {
			if ( clockingIds.length <= 0 )
				return;

			var promises = [];

			for (var i = 0; i < clockingIds.length; i++)
				promises.push(this.approveClocking(clockingIds[i], status));

			(function (results) {
				viewDetails.popup.hide();
				ui.list();
			}).future().apply(ui, promises);
		},

		approveClocking: function (clockingId, status) {
			var promise = new Promise();

			ClockingAPI.approve(clockingId, status, function (data) {
				promise.deliver(!(data instanceof Error));
			}, true);

			return promise;
		},

		remove: function (clockingIds) {
			if ( (clockingIds.length <= 0) ||
			     !confirm(_('clocking.remove.prompt')) )
				return;

			var promises = [];

			for (var i = 0; i < clockingIds.length; i++) {
				var promise = new Promise();
				promises.push(promise);
				ClockingAPI.remove(clockingIds[i], function (data) {
					promise.deliver(!(data instanceof Error));
				}, true);
			}

			(function (results) {
				viewDetails.popup.hide();
				ui.list();
			}).future()(promises);
		},

		restore: function () {
		}
	};

	/* Details Popup
	----------------------------------------------------------- */

	var viewDetails = {};

	viewDetails.clockingId   = $('clocking_edit_id');
	viewDetails.clockingType = new gx.bootstrap.Select('clocking_edit_type', {
		'icon'          : 'tag',
		'label'         : {
			'text'      : _('field.type'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'requestData'   : {
			'api'       : 'clocking',
			'do'        : 'types'
		},
		'requestParam'  : 'search',
		'listFormat'    : function (item) {
			return ( _('clocking.type.'+item.Identifier) || item.Label || item.Identifier );
		},
		'formatID'      : function (item) {
			return item ? item.Id : false;
		},
		'onSelect'      : function (item) {
			if ( item.ApprovalRequired )
				viewDetails.selApprovalStatus.set(ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_REQUIRED));

			var dateFormat = item.WholeDay ? '%a %Y-%m-%d' : '%a %Y-%m-%d %H:%M';

			viewDetails.dbxStart.options.picker.startView = ( item.WholeDay ? 'days' : 'time' );
			viewDetails.dbxStart.setFormat(dateFormat, !item.WholeDay);

			viewDetails.dbxEnd.options.picker.startView = ( item.WholeDay ? 'days' : 'time' );
			viewDetails.dbxEnd.setFormat(dateFormat, !item.WholeDay);

			$(viewDetails.tbxBreak)[ item.WholeDay ? 'hide' : 'show' ]();
		}
	});

	viewDetails.selUser = new gx.bootstrap.Select('viewDetailsUser', {
		'icon'          : 'user',
		'label'         : {
			'text'      : _('entity.user.singular'),
			'class'     : 'dialog_label'
		},
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
		'formatID'      : function (item) {
			return ( item ? item.Id : false );
		}
	});

	viewDetails.selApprovalStatus = new gx.bootstrap.Select('viewDetailsApprovalStatus', {
		'icon'          : '',
		'label'         : {
			'text'      : _('clocking.status.caption'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'default'       : null,
		'localOptions'  : [
			ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_PRELIMINARY),
			ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_REQUIRED),
			ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_DENIED),
			ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_CONFIRMED),
			ClockingManager.approvalStatusToItem(ClockingAPI.APPROVAL_STATUS_AS_IS),
		],
		'searchFilter'  : function (items, searchText) {
			if ( searchText == null )
				return items;

			searchText = searchText.toLowerCase();

			var result = [];

			for (var i = 0; i < items.length; i++) {
				if ( items[i].label.toLowerCase().indexOf(searchText) >= 0 )
					result.push(items[i]);
			}

			return result;
		},
		'listFormat'    : function (item) {
			return item.label;
		},
		'formatID'      : function (item) {
			return item.value
		},
		'onSelect'      : function () {
		}
	});

	viewDetails.dbxStart = new gx.bootstrap.DatePicker('clocking_edit_start', {
		'icon'          : 'play',
		'label'         : {
			'text'      : _('field.start'),
			'class'     : 'dialog_label'
		},
		'picker'        : {
			'startView' : 'time'
		},
		'width'         : '140px',
		'format'        : '%a %Y-%m-%d %H:%M',
		'return_format' : '%Y-%m-%d %H:%M:%S'
	});
	viewDetails.dbxEnd = new gx.bootstrap.DatePicker('clocking_edit_end', {
		'icon'          : 'stop',
		'label'         : {
			'text'      : _('field.end'),
			'class'     : 'dialog_label'
		},
		'picker'        : {
			'startView' : 'time'
		},
		'width'         : '140px',
		'format'        : '%a %Y-%m-%d %H:%M',
		'return_format' : '%Y-%m-%d %H:%M:%S'
	});

	viewDetails.tbxBreak = new gx.bootstrap.Timebox('clocking_edit_break', {
		'label'         : 'Pause',
		'prefix'        : false,
		'seconds'       : false
	});

	viewDetails.removeButton = new Element('input', {
		'type'          : 'button',
		'class'         : 'btn btn-danger m2_l',
		'value'         : _('action.delete'),
		'id'            : 'clocking_remove'
	})
		.addEvent('click', function (event) {
			if ( ui._current )
				ui.remove([ ui._current.Id ]);
		});

	viewDetails.dateStatus = new Element('div', { 'class': 'clocking_details_last_changed' });

	viewDetails.popup = new gx.bootstrap.Popup({
		'width'         : 600,
		'content'       : $('clocking_popup_details'),
		'title'         : 'Details',
		'footer'        : __({ 'children': {
			'btnClose'  : { 'tag': 'input', 'type': 'button', 'class': 'btn f_l', 'value': 'Close', 'onClick': function () {
				viewDetails.popup.hide();
			} },
			'status'    : viewDetails.dateStatus,
			'btnRemove' : viewDetails.removeButton,
			'btnOk'     : { 'tag': 'input', 'type': 'button', 'class': 'btn btn-primary m2_l', 'value': _('action.save'), 'id': 'clocking_save', 'onClick': function () {
				ui.save();
			} }
		}}),
		'closable'      : true
	});

	var transactionDetails = new TransactionWizard(gui)
		.addEvent('save', ui.list.bind(ui));

	/* Filters
	----------------------------------------------------------- */

	var chkShowDel = new gx.bootstrap.CheckButton('chkShowDel', {
		'label'   : _('filter.deleted'),
		'onChange': function () {
			ui.list();
		}
	});

	var monthPicker = new gx.bootstrap.MonthPicker('mprRange', {
		'onSelect': function (date) {
			ui.list();
		}
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
		'formatID'      : function (item) {
			return item ? item.Id : false;
		},
		'onNoselect'    : function () {
			ui.list();
		},
		'onSelect'      : function () {
			ui.list();
		}
	});

	/* Options menu
	----------------------------------------------------------- */

	var btnNewWorktime = $('btnNewWorktime')
		.addEvent('click', ui.add.bind(ui));
	var btnOptions = new gx.bootstrap.MenuButton('btnOptions', {
		'label'      : _('field.options'),
		'style'      : 'primary',
		'orientation': 'right'
	});
	var optRefresh = btnOptions.add('Aktualisieren', 'refresh').addEvent('click', ui.list.bind(ui));

	var optRemove = btnOptions.add(_('action.delete'), 'trash').addEvent('click', function () {
		ui.remove(ui.getSelectedClockingIds());
	});

	var btnTransaction = $('btnNewTransaction')
		.addEvent('click', function () {
			var clockingIds = ui.getSelectedClockingIds();
			if ( clockingIds.length === 0 ) {
				transactionDetails.show(selUser.getSelected() || AUTHENTICATED_USER, []);
				return;
			}

			var checkboxes = $$('.clocking_checkbox:checked');
			var clockings  = ( checkboxes.length === 0 ? [] : checkboxes.retrieve('tymio-clocking') );
			var user       = ( clockings.length > 0 ? clockings[0].User : selUser.getSelected() );

			transactionDetails.show(user, clockings);
		});

	var optApprove = btnOptions.add(_('action.approve'), 'ok').addEvent('click', function () {
		ui.approve(ui.getSelectedClockingIds(), ClockingAPI.APPROVAL_STATUS_CONFIRMED);
	});

	var optDeny = btnOptions.add(_('action.deny'), 'remove').addEvent('click', function () {
		ui.approve(ui.getSelectedClockingIds(), ClockingAPI.APPROVAL_STATUS_DENIED);
	});

	if ( !AUTHENTICATED_USER.IsAdmin ) {
		$(btnTransaction).hide();
		$(optApprove).hide();
		$(optDeny).hide();
	}

	/* Automatically adapt window height */
	var h = 130;
	var l = 200;

	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if ( ht < l )
			ht = l;
		ui.clockingTable.setHeight(ht+'px');
	}

	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	ui._ready = true;
	ui.list();

	selFilter.addEvent('change', ui.list.bind(ui));
});
