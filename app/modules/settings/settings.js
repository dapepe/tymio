initView(function (gui) {

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

	function getPropertyValue(property) {
		if ( !property.Values )
			return ( property.Type === 'bool' ? false : '' );

		for (var i = 0; i < property.Values.length; i++) {
			if ( (property.Values[i].UserId == null) &&
			     (property.Values[i].DomainId == null) )
				return property.Values[i].Value;
		}

		return property.DefaultValue;
	}

	var ui = {
		_current: null,

		reload: function () {
			AccountAPI.details(function (data) {
				var account = data.result;

				details.form.reset();

				ui._current = account.Id;

				details.form.general.setValues(account);
				details.form.contact.setValues(account.Address);

				details.fields.clear();

				PropertyAPI.list.toApiPromise(PropertyAPI, [ null ]).then(function (properties) {
					var newPropertySelect = new gx.bootstrap.Select(null, {
						'default'        : null,
						'localOptions'   : properties.filter(function (item, index) {
							return !( item.Name in account.Properties );
						}),
						'listFormat'     : function (item) {
							return item.Name;
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

								addPropertyField(selection.Name, selection.Type, getPropertyValue(selection));

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

					for (var propertyName in account.Properties) {
						if ( !account.Properties.hasOwnProperty(propertyName) ||
							 !account.Properties[propertyName] ||
							 (typeof(account.Properties[propertyName].Type) !== 'string') )
							continue;

						switch ( account.Properties[propertyName].Type ) {
							case 'bool':
								var checkbox = details.fields.addField(propertyName, 'checkbox', {
									'label'  : propertyName,
									'default': 1
								}).getInput();

								if ( account.Properties[propertyName].Value )
									checkbox.setProperty('checked', 'checked');

								break;

							case 'user':
								var userSelection = details.fields.addField(propertyName, 'gxselect', {
									'icon'          : 'user',
									'label'         : propertyName,
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

								userSelection.set(account.Properties[propertyName].Value);

								break;

							default:
								details.fields.addField(propertyName, 'text', {
									'label'  : propertyName,
									'default': account.Properties[propertyName].Value
								});

								break;
						}
					}
				});
			});
		},
		open: function () {
			this.reload();
			details.tabbox.openTab('general');
		},
		save: function () {
			var accountData = details.form.general.getValues();
			initParam(accountData, 'Address', details.form.contact.getValues());
			initParam(accountData, 'Properties', details.form.details.getValues());

			var callback = function (data) {
				if ( data.result ) {
					gui.msg.addMessage(_('message.update', {'model': _('entity.account.singular') + ' "' + accountData.Name + '"'}), 'success');
					ui.reload();
				} else if ( typeOf(data.warnings) == 'object' ) {
					details.form.setHighlights(data.warnings, 'error');
				}
			};

			details.form.setHighlights();

			if ( ui._current )
				AccountAPI.update(ui._current, accountData, callback);
			else
				AccountAPI.add(accountData, callback);
		}
	};

	/* Details
	----------------------------------------------------------- */
	var details = {};

	details.form = Factory.FormCollection();
	details.form.general = new gx.bootstrap.Form();
	details.form.general.addFieldset({
		'title': _('section.general'),
		'fields': {
			'Name':        { 'type': 'text', 'label': _('field.name') }
		}
	});

	details.form.contact = new gx.bootstrap.Form();
	details.form.contact.addFieldset({
		'title': _('section.contact'),
		'fields': {
			'Company'  : { 'type': 'text', 'label': _('field.company') },
			'Firstname': { 'type': 'text', 'label': _('field.firstname') },
			'Lastname' : { 'type': 'text', 'label': _('field.lastname') },
			'Address'  : { 'type': 'text', 'label': _('field.address') },
			'Zipcode'  : { 'type': 'text', 'label': _('field.zipcode') },
			'City'     : { 'type': 'text', 'label': _('field.city') },
			'State'    : { 'type': 'text', 'label': _('field.state') },
			'Province' : { 'type': 'text', 'label': _('field.province') },
			'Country'  : { 'type': 'text', 'label': _('field.country') },
			'Phone'    : { 'type': 'text', 'label': _('field.phone') },
			'Fax'      : { 'type': 'text', 'label': _('field.fax') },
			'Website'  : { 'type': 'text', 'label': _('field.website') },
			'Email'    : { 'type': 'text', 'label': _('field.email') },
			'Vatid'    : { 'type': 'text', 'label': _('field.vatid') }
		}
	});

	details.form.details = new gx.bootstrap.Form();
	details.fields = new gx.bootstrap.Fieldset(null, {
		'title': _('section.details')
	});
	details.form.details.addFieldset(details.fields);

	/* Tabbox
	============================================================== */
	details.tabbox = new gx.bootstrap.Tabbox('tabbox', {
		'frames': [
			{ 'name': 'general', 'title': _('section.general'), 'content': $(details.form.general) },
			{ 'name': 'contact', 'title': _('section.contact'), 'content': $(details.form.contact) },
			{ 'name': 'details', 'title': _('section.details'), 'content': $(details.form.details) }
		]
	});

	details.form.setTabbox(details.tabbox);

	$('save').addEvent('click', function (event) {
		ui.save();
	});

	ui.open();
});
