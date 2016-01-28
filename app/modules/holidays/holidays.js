initView(function (gui) {
	var selFilterDomain = $('selFilterDomain');

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_selection: false,
		_current: false,
		list: function () {
			var filter = tabHolidays.getFilter();
			filter.search = txtSearch.get('value');
			filter.domain = selFilterDomain.get('value');
			HolidayAPI.list(filter, function (res) {
				tabHolidays.setData(res.result);
			});
		},
		open: function (id) {
			HolidayAPI.details(id, function (res) {
				details.form.reset();
				details.form.setValue('Date', new Date());

				if ( res.result.Date != null )
					res.result.Date = res.result.Date * 1000;

				var domainValues = [];
				if ( res.result.Domains != null ) {
					res.result.Domains.each(function (domain) {
						domainValues.push(String(domain.Id));
					});
				}
				res.result.Domains = domainValues;

				details.form.setValues(res.result);
				ui._current = res.result.Id;

				details.popup.show();
			});
		},
		update: function () {
			var data = details.form.getValues();
			details.form.setHighlights();

			var callback = function (res) {
				if ( res.result ) {
					gui.msg.addMessage(_('message.update', {'model': _('entity.holiday.singular') + ' "' + data.Name + '"'}), 'success');
					details.popup.hide();
					ui.list();
				} else if ( typeOf(res.warnings) == 'object' ) {
					details.form.setHighlights(res.warnings, 'error');
				}
			};

			if ( ui._current )
				HolidayAPI.update(ui._current, data, callback);
			else
				HolidayAPI.add(data, callback)

		},
		add: function () {
			details.form.reset();
			// details.tabbox.openTab('general');
			ui._current = false;
			details.popup.show();
		},
		remove: function () {
			if ( this._selection == false && this._current == false ) {
				gui.msg.addMessage(_('error.noselection', {'model': _('entity.holiday.singular')}), 'success');
				return;
			}

			HolidayAPI.erase(this._current ? this._current : this._selection, function (res) {
				gui.msg.addMessage(_('message.remove', {'model': _('entity.holiday.singular')}), 'success');
				this._selection = false;
				ui.list();
			});
		}
	};

	/* Details Popup
	----------------------------------------------------------- */
	var details = {};

	// Initialize the domain list
	var domains = [];
	selFilterDomain.getElements('option').each(function (option) {
		if ( option.get('value') != '' )
			domains.push({'label': option.get('html'), 'value': option.get('value')});
	});

	details.form = Factory.FormCollection();
	details.form.general = new gx.bootstrap.Form();
	details.form.general.addFieldset({
		'fields': {
			'Name':    { 'type': 'text',      'label': _('field.name') },
			'Date':    { 'type': 'date',      'label': _('field.date') },
			'Domains': { 'type': 'checklist', 'label': _('entity.domain.plural'), 'search': false, 'data': domains }
		}
	});

	details.popup = new gx.bootstrap.Popup({
		'width': 600,
		'content': details.form.general.display(),
		'title': 'Details',
		'footer': __({'children': {
			'btnClose': {'tag': 'input', 'type': 'button', 'class': 'btn m2_r', 'value': _('action.close'), 'onClick': function () {
				details.popup.hide();
			}},
			'btnOk': {'tag': 'input', 'type': 'button', 'class': 'btn btn-primary', 'value': _('action.save'), 'onClick': function () {
				ui.update();
			}}
		}}),
		'closable': true
	});

	/* Table
	----------------------------------------------------------- */
	var tabHolidays = new gx.bootstrap.Table('tabHolidays', {
		'cols' : [
			{'label': '<i class="icon-check"></i>', 'id': 'check', 'width': '20px', 'filterable': 'false', 'clickable': false},
			{'label' : _('field.name'), 'id' : 'Name'},
			{'label' : _('field.date'), 'id' : 'Date', 'filter' : 'desc'},
			{'label' : _('entity.domain.plural'), 'id': 'Domain'}
		],
		'onFilter' : function (col) {
			ui.list();
		},
		'onStart' : function () {
			ui._selection = [];
		},
		'onClick' : function (row, event) {
			if ( typeOf(event.target) == 'element' && event.target.get('tag') == 'td' ) {
				row.checkbox.checked = !row.checkbox.checked;
				ui._selection = row.checkbox.checked ? row.Id : false;
			}
		},
		'onDblclick' : function (row, event) {
			event.stop();
			deselect();
			ui.open(row.Id);
		},
		'structure' : function (row) {
			row.checkbox = new Element('input', {'type': 'radio', 'value': row.Id, 'name': 'selDomain'});
			row.checkbox.addEvent('click', function (event) {
				ui._selection = row.checkbox.checked ? row.Id : false;
			});

			var link = new Element('a', {'html': row.Name}).addEvent('click', function () { ui.open(row.Id); });
			var domainlist = [];
			row.Domains.each(function (domain) {
				domainlist.push(domain.Name);
			});

			return [
				row.checkbox,
				link,
				new Date(row.Date * 1000).format(_('date.format.default')),
				domainlist.join(', ')
			];
		}
	});

	/* Options menu
	----------------------------------------------------------- */
	var btnOptions = new gx.bootstrap.MenuButton('btnOptions', {
		'label': _('field.options'),
		'style': 'primary',
		'orientation': 'right'
	});
	var optNew = btnOptions.add(_('action.add'), 'plus').addEvent('click', function () {
		ui.add();
	});
	var optRemove = btnOptions.add(_('action.remove'), 'trash').addEvent('click', function () {
		ui.remove();
	});
	var optRefresh = btnOptions.add(_('action.refresh'), 'refresh').addEvent('click', function () {
		ui.list();
	});

	txtSearch = $('txtSearch');
	txtSearch.addEvent('keydown', function (event) {
		if ( event.key == 'enter' )
			ui.list();
	});

	selFilterDomain.addEvent('change', function () {
		ui.list();
	});

	/* Auto-resize
	----------------------------------------------------------- */
	var h = 150;
	var l = 200;
	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if ( ht < l )
			ht = l;
		tabHolidays.setHeight(ht+'px');
	}
	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	/* Load the list
	----------------------------------------------------------- */
	ui.list();
});