initView(function (gui) {
	var selFilterDomain = $('selFilterDomain');

	function addPropertyField(name, type, value) {
		switch ( type ) {
			case'bool':
				var checkbox = details.fields.addField(name, 'checkbox', {
					'label'  : name,
					'default': 1
				}).getInput();

				if ( value )
					checkbox.setProperty('checked', 'checked');

				break;

			case 'user':
				var userSelection = details.fields.addField(name, 'gxselect', {
					'icon'          : 'user',
					'label'         : name,
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
						return ( item ? item.Name : false );
					}
				}).getInput();

				userSelection.set(value);

				break;

			default:
				details.fields.addField(name, 'text', {
					'label'  : name,
					'default': value
				});

				break;
		}
	}

	function getPropertyValue(userId, domainId, property) {
		if ( !property.Values )
			return ( property.Type === 'bool' ? false : '' );

		var value = property.DefaultValue;

		for (var i = 0; i < property.Values.length; i++) {
			if ( property.Values[i].UserId === userId )
				return property.Values[i].Value;
			else if ( property.Values[i].DomainId === domainId )
				value = property.Values[i].Value;
		}

		return value;
	}

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_selection: false,
		_current: false,
		list: function () {
			var filter = tabUsers.getFilter();
			filter.search = txtSearch.get('value');
			filter.showdeleted = chkShowDel.get();
			filter.domain = selFilterDomain.get('value');
			UserAPI.list(filter, function (res) {
				tabUsers.setData(res.result);
			});
		},
		open: function (id) {
			UserAPI.details(id, function (data) {
				var user = data.result;

				details.form.reset();
				details.form.setHighlights();
				details.tabbox.openTab('general');

				PropertyAPI.list.toApiPromise(PropertyAPI, [ null ]).then(function (properties) {
					details.form.setValues(user);

					details.btnRestore.setStyle('display', user.Deleted ? 'inline-block' : 'none');
					ui._current = user.Id;

					details.fields.clear();

					var newPropertySelect = new gx.bootstrap.Select(null, {
						'default'        : null,
						'localOptions'   : properties.filter(function (item, index) {
							return !( item.Name in user.Properties );
						}),
						'listFormat'     : function (option) {
							return option.Name;
						}
					});

					details.fields.addField('__addproperty', new Element('div').adopt(
						$(newPropertySelect),
						new Element('a', { 'class': 'btn m_l', 'text': '+' })
							.addEvent('click', function (event) {
								event.stop();

								var selection = newPropertySelect.getSelected();
								if ( !selection ||
								     details.fields.hasField(selection.Name) )
									return;

								addPropertyField(selection.Name, selection.Type, getPropertyValue(id, user.DomainId, selection));

								newPropertySelect.options.localOptions = newPropertySelect.options.localOptions.filter(function (item, index) {
									return ( item.Name !== selection.Name );
								});
								newPropertySelect
									.search()
									.set(null);
							})
					), {
						'label': _('property.add')
					});

					for (var propertyName in user.Properties) {
						if ( !user.Properties[propertyName] ||
							 (typeof(user.Properties[propertyName].Type) !== 'string') )
							continue;

						addPropertyField(propertyName, user.Properties[propertyName].Type, user.Properties[propertyName].Value);
					}
				});

				details.popup.show();
			});
		},
		add: function () {
			details.form.reset();
			details.form.setHighlights();
			details.tabbox.openTab('general');

			details.btnRestore.setStyle('display', 'none');
			ui._current = false;

			details.popup.show();
		},
		save: function () {
			var userData = Object.append({},
				details.form.general.getValues(),
				details.form.contact.getValues()
			);
			initParam(userData, 'Properties', details.form.details.getValues());

			details.form.setHighlights();

			if ( userData.Password != userData.Password2 ) {
				details.form.setHighlights({'Password': _('error.password_no_match'), 'Password2': true}, 'warning');
				return;
			}

			var callback = function (res) {
				if ( res.result ) {
					gui.msg.addMessage(_('message.update', {'model': _('entity.user.singular') + ' "' + userData.Name + '"'}));
					details.popup.hide();
					ui.list();
				} else if ( typeOf(res.warnings) == 'object' ) {
					details.form.setHighlights(res.warnings, 'error');
				}
			};

			if ( ui._current )
				UserAPI.update(ui._current, userData, callback);
			else
				UserAPI.add(userData, callback);
		},
		remove: function () {
			if ( this._selection == false ) {
				gui.msg.addMessage(_('error.noselection', {'model': _('entity.user.singular')}));
				return;
			}

			UserAPI.remove(this._selection, function (res) {
				gui.msg.addMessage(_('message.remove', {'model': _('entity.user.singular')}));
				this._selection = false;
				ui.list();
			});
		},
		restore: function () {
			if ( ui._current ) {
				UserAPI.restore(ui._current, function (res) {
					gui.msg.addMessage(_('message.restore', {'model': _('entity.user.singular')}));
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
			'Name':   {'type': 'text', 'label': _('field.username')},
			'DomainId': {'field': $('selDetailsDomain'), 'label': _('entity.domain.singular')},
			'Role':   {'type': 'text', 'label': _('field.role')},
			'Email':  {'type': 'text', 'label': _('field.email')}
		}
	});
	details.form.general.addFieldset({
		'title': _('section.security'),
		'fields': {
			'Password':  {'type': 'password', 'label': _('field.password')},
			'Password2': {'type': 'password', 'label': _('field.passwordrep')}
		}
	});

	details.form.contact = new gx.bootstrap.Form();
	details.form.contact.addFieldset({
		'title': _('section.contact'),
		'fields': {
			'Firstname': {'type': 'text', 'label': _('field.firstname')},
			'Lastname': {'type': 'text', 'label': _('field.lastname')},
			'Phone': {'type': 'text', 'label': _('field.phone')}
		}
	});

	details.form.details = new gx.bootstrap.Form();
	details.fields = new gx.bootstrap.Fieldset(null, {
		'title': _('section.details')
	});
	details.form.details.addFieldset(details.fields);

	details.tabbox = new gx.bootstrap.Tabbox(new Element('div'), {'frames': [
		{ 'name': 'general', 'title': _('section.general'), 'content': $(details.form.general) },
		{ 'name': 'contact', 'title': _('section.contact'), 'content': $(details.form.contact) },
		{ 'name': 'details', 'title': _('section.details'), 'content': $(details.form.details).setStyle('min-height', '200px') }
	]});

	details.form.setTabbox(details.tabbox);

	details.btnRestore = new Element('input', {'type': 'button', 'class': 'btn btn-success f_l', 'value': _('action.restore')});
	details.btnRestore.addEvent('click', function () {
		ui.restore();
	});

	details.popup = new gx.bootstrap.Popup({
		'width': 500,
		'content': details.tabbox.display(),
		'title': 'Details',
		'footer': __({'children': {
			'btnClose': {'tag': 'input', 'type': 'button', 'class': 'btn m2_r', 'value': _('action.close'), 'onClick': function () {
				details.popup.hide();
			}},
			'btnOk': {'tag': 'input', 'type': 'button', 'class': 'btn btn-primary', 'value': _('action.save'), 'onClick': function () {
				ui.save();
			}},
			'btnRestore': details.btnRestore
		}}),
		'closable': true
	});

	/* Table
	----------------------------------------------------------- */
	var tabUsers = new gx.bootstrap.Table('tabUsers', {
		'cols' : [
			{'label': '<i class="icon-check"></i>', 'id': 'check', 'width': '20px', 'filterable': 'false', 'clickable': false},
			{'label' : _('field.username'), 'id' : 'Name', 'filter' : 'asc'},
			{'label' : _('field.number'), 'id' : 'Number'},
			{'label' : _('field.lastname'), 'id': 'Lastname'},
			{'label' : _('field.firstname'), 'id': 'Firstname'},
			{'label' : _('entity.domain.singular'), 'id': 'Domain'},
			{'label' : _('field.status'), 'id': 'Status', 'filterable': false} // Deleted, Manager, Active
		],
		'onFilter' : function (col) {
			ui.list();
		},
		'onStart' : function () {
			ui._selection = [];
		},
		'onClick' : function (row, event) {
			if ( typeOf(event.target) == 'element' && event.target.get('tag') == 'td' ) {
				row._checkbox.checked = !row._checkbox.checked;
				ui._selection = row._checkbox.checked ? row.Id : false;
			}
		},
		'onDblclick' : function (row, event) {
			event.stop();
			deselect();
			ui.open(row.Id);
		},
		'structure' : function (row) {
			row._checkbox = new Element('input', {'type': 'radio', 'value': row.Id, 'name': 'selDomain'});
			row._checkbox.addEvent('click', function (event) {
				ui._selection = row._checkbox.checked ? row.Id : false;
			});

			var cssDeleted = ( row.Deleted ? 'deleted' : '' );

			var link = new Element('a', { 'html': row.Name, 'class': cssDeleted })
				.addEvent('click', function () {
					ui.open(row.Id);
				});

			if ( row.Deleted )
				link = new Element('div').adopt([ Factory.DeletedBadge(), link ])

			return [
				row._checkbox,
				link,
				new Element('span', { 'text': row.Number, 'class': cssDeleted }),
				new Element('span', { 'text': row.Lastname, 'class': cssDeleted }),
				new Element('span', { 'text': row.Firstname, 'class': cssDeleted }),
				new Element('span', { 'text': row.Domain.Name, 'class': cssDeleted }),
				'S'
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
		tabUsers.setHeight(ht+'px');
	}
	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	/* Load the list
	----------------------------------------------------------- */
	ui.list();
});