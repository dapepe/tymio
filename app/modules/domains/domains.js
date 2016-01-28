initView(function (gui) {

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_selection: false,
		_current: false,
		list: function () {
			var filter = tabDomains.getFilter();
			filter.search = txtSearch.get('value');
			filter.showdeleted = chkShowDel.get();
			DomainAPI.list(filter, function (data) {
				tabDomains.setData(data.result);
			});
		},
		open: function (id) {
			DomainAPI.details(id, function (data) {
				var domain = data.result;

				details.form.reset();
				details.tabbox.openTab('general');

				details.btnRestore.setStyle('display', data.result.Valid ? 'none' : 'inline-block' );
				ui._current = domain.Id;

				details.form.setValues(domain);

				details.fields.clear();
				for (var propertyName in domain.Properties) {
					if ( !domain.Properties.hasOwnProperty(propertyName) ||
					     !domain.Properties[propertyName] ||
					     (typeof(domain.Properties[propertyName].Type) !== 'string') )
						continue;

					if ( domain.Properties[propertyName].Type === 'bool' ) {
						var checkbox = details.fields.addField(propertyName, 'checkbox', {
							'label'  : propertyName,
							'default': 1
						}).getInput();

						if ( domain.Properties[propertyName].Value )
							checkbox.setProperty('checked', 'checked');
					} else {
						details.fields.addField(propertyName, 'text', {
							'label'  : propertyName,
							'default': domain.Properties[propertyName].Value
						});
					}
				}

				details.popup.show();
			});
		},
		add: function () {
			details.form.reset();
			details.tabbox.openTab('general');

			details.btnRestore.setStyle('display', 'none');
			ui._current = null;

			details.fields.clear();

			details.popup.show();
		},
		save: function () {
			var domainData = details.form.general.getValues();
			initParam(domainData, 'Properties', details.form.details.getValues());

			var callback = function (data) {
				if ( data.result ) {
					gui.msg.addMessage(_('message.update', {'model': _('entity.domain.singular') + ' "' + domainData.Name + '"'}), 'success');
					details.popup.hide();
					ui.list();
				} else if ( typeOf(data.warnings) == 'object' ) {
					details.form.setHighlights(data.warnings, 'error');
				}
			};

			details.form.setHighlights();

			if ( ui._current )
				DomainAPI.update(ui._current, domainData, callback);
			else
				DomainAPI.add(domainData, callback);
		},
		remove: function () {
			if ( this._selection == false ) {
				gui.msg.addMessage(_('error.noselection', {'model': _('entity.domain.singular')}), 'success');
				return;
			}

			DomainAPI.remove(this._selection, function (res) {
				gui.msg.addMessage(_('message.remove', {'model': _('entity.domain.singular')}), 'success');
				this._selection = false;
				ui.list();
			});
		},
		restore: function () {
			if ( ui._current ) {
				DomainAPI.restore(ui._current, function (res) {
					gui.msg.addMessage(_('message.restore', {'model': _('entity.domain.singular')}), 'success');
					details.popup.hide();
					ui._current = false;
					ui.list();
				});
			}
		}
	};

	/* Details Popup
	----------------------------------------------------------- */
	var details = {};

	details.form = Factory.FormCollection();
	details.form.general = new gx.bootstrap.Form();
	details.form.general.addFieldset({
		'title': _('section.general'),
		'fields': {
			'Name':        {'type': 'text', 'label': _('field.name')},
			'Number':      {'field': $('selDetailsDomain'), 'label': _('field.number')},
			'Description': {'type': 'text', 'label': _('field.description')}
		}
	});

	details.form.details = new gx.bootstrap.Form();
	details.fields = new gx.bootstrap.Fieldset(null, {
		'title': _('section.details')
	});
	details.form.details.addFieldset(details.fields);

	details.tabbox = new gx.bootstrap.Tabbox(new Element('div'), {'frames': [
		{'name': 'general', 'title': _('section.general'), 'content': $(details.form.general)},
		{'name': 'details', 'title': _('section.details'), 'content': $(details.form.details)}
	]});

	details.form.setTabbox(details.tabbox);

	details.btnRestore = new Element('input', {'type': 'button', 'class': 'btn btn-success f_l', 'value': _('action.restore')});
	details.btnRestore.addEvent('click', function () {
		ui.restore();
	});

	details.popup = new gx.bootstrap.Popup({
		'width'   : 500,
		'content' : $(details.tabbox),
		'title'   : 'Details',
		'footer'  : __({'children': {
			'btnClose'  : {'tag': 'input', 'type': 'button', 'class': 'btn m2_r', 'value': _('action.close'), 'onClick': function () {
				details.popup.hide();
			}},
			'btnOk'     : {'tag': 'input', 'type': 'button', 'class': 'btn btn-primary', 'value': _('action.save'), 'onClick': function () {
				ui.save();
			}},
			'btnRestore': details.btnRestore
		}}),
		'closable': true
	});


	/* Table
	----------------------------------------------------------- */
	var tabDomains = new gx.bootstrap.Table('tabDomains', {
		'cols' : [
			{'label': '<i class="icon-check"></i>', 'id': 'check', 'width': '20px', 'filterable': 'false', 'clickable': false},
			{'label' : _('field.name'), 'id' : 'Name', 'filter' : 'asc'},
			{'label' : _('field.number'), 'id' : 'Number'},
			{'label' : _('field.description'), 'id': 'Description'}
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
			if ( !row.Valid ) {
				link.addClass('deleted');
				link = new Element('div').adopt([link, new Element('span', {'class': 'm2_l label label-important', 'html': _('field.deleted')})])
			}

			return [
				row.checkbox,
				link,
				row.Number,
				row.Description
			];
		}
	});

	/* Options menu
	----------------------------------------------------------- */
	txtSearch = $('txtSearch');
	txtSearch.addEvent('keydown', function (event) {
		if ( event.key == 'enter' )
			ui.list();
	});
	$('btnRefresh').addEvent('click', function () {
		ui.list();
	});
	$('btnRemove').addEvent('click', function () {
		ui.remove();
	});
	$('btnAdd').addEvent('click', function () {
		ui.add();
	});

	var chkShowDel = new gx.bootstrap.CheckButton('chkShowDel', {'label': _('filter.deleted')});
	chkShowDel.addEvent('change', function () {
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
		tabDomains.setHeight(ht+'px');
	}
	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	/* Load the list
	----------------------------------------------------------- */
	ui.list();
});
