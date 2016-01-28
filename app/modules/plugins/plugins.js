initView(function (gui) {
	var CODE_MIRROR_OPTIONS = {
		'indentUnit'         : 4,
		'smartIndent'        : true,
		'tabSize'            : 4,
		'indentWithTabs'     : true,
		'electricChars'      : true,
		'autoClearEmptyLines': true,
		'lineNumbers'        : true,
		'matchBrackets'      : true
	};

	var xmlTemplate = '<?xml version="1.0" encoding="UTF-8"?>'+"\n"+'<ixml>'+"\n\n"+'</ixml>';
	var activateSyntax = true;

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_selection: false,
		_current: false,
		list: function () {
			var filter = tabPlugins.getFilter();
			filter.search = txtSearch.get('value');
			filter.showinactive = chkShowDel.get();
			PluginAPI.list(filter, function (res) {
				tabPlugins.setData(res.result);
			});
		},
		open: function (id) {
			PluginAPI.details(id, function (res) {
				details.form.reset();
				details.form.setValues(res.result);
				details.form.modified = false;

				details.btnActivate.setStyle('display', 'inline-block');

				if ( res.result.Active ) {
					details.btnActivate.addClass('btn-warning');
					details.btnActivate.removeClass('btn-success');
					details.btnActivate.set('value', _('action.deactivate'));
				} else {
					details.btnActivate.addClass('btn-success');
					details.btnActivate.removeClass('btn-warning');
					details.btnActivate.set('value', _('action.activate'));
				}

				ui._current = res.result;

				details.popup.show();
			});
		},
		add: function () {
			details.form.reset();
			details.form.Code.set('value', xmlTemplate);
			details.form.modified = true;

			details.btnActivate.setStyle('display', 'none');
			ui._current = false;

			details.popup.show();
		},
		save: function () {
			var data = details.form.getValues();

			var callback = function (res) {
				if ( res.result ) {
					details.form.modified = false;
					gui.msg.addMessage(_('message.update', {'model': _('entity.plugin.singular') + ' "' + data.Name + '"'}), 'success');
					ui.list();
					ui.open(res.result);
				} else if ( typeOf(res.warnings) == 'object' ) {
					var msg = _('message.error');
					msg += '<ul>';
					for (i in res.warnings)
						msg += '<li>' + res.warnings[i] + '</li>';
					msg += '</ul>';

					gui.msg.addMessage(msg, 'error', true, false, false);
				}
			};

			if ( ui._current )
				PluginAPI.update(ui._current.Id, data, callback);
			else
				PluginAPI.add(data, callback);
		},
		activate: function () {
			PluginAPI.activate(ui._current.Id, function () {
				if ( res.result ) {
					gui.msg.addMessage(_('message.activate', {'model': _('entity.plugin.singular') + ' "' + ui._current.Name + '"'}), 'success');
					details.popup.hide();
					ui.list();
				}
			});
		},
		deactivate: function () {
			PluginAPI.deactivate(ui._current.Id, function () {
				if ( res.result ) {
					gui.msg.addMessage(_('message.deactivate', {'model': _('entity.plugin.singular') + ' "' + ui._current.Name + '"'}), 'success');
					details.popup.hide();
					ui.list();
				}
			});
		},
		remove: function () {
			if ( this._selection == false && this._current == false ) {
				gui.msg.addMessage(_('error.noselection', {'model': _('entity.plugin.singular')}), 'success');
				return;
			}

			PluginAPI.erase(this._current ? this._current.Id : this._selection, function (res) {
				gui.msg.addMessage(_('message.remove', {'model': _('entity.plugin.singular')}), 'success');
				this._selection = false;
				ui.list();
			});
		},
		execute: function () {
			var idOrCode = (
				details.form.modified
				? details.form.getValues().Code
				: this._current.Id
			);

			console.log('Executing plugin', idOrCode);

			var data = {};

			var token = $('execution_token');
			if ( token )
				data[token.name] = token.value;

			PluginAPI.execute(idOrCode, data, true, function (res) {
				console.log('PLUGIN OUTPUT: ', res.result.output);
				console.log('PLUGIN LOG: ', res.result.log);
			});
		}
	};

	function formatTimeSegment(value) {
		if ( value < 0 )
			value = -value;

		value = Math.floor(value);
		return ( value < 10 ? '0'+value : String(value) );
	}

	function formatTime(time) {
		return ''+formatTimeSegment(time / 3600 % 24)+':'+formatTimeSegment(time / 60 % 60)+':'+formatTimeSegment(time % 60);
	}

	/* Details Popup
	----------------------------------------------------------- */
	var details = {};
	details.form = {};

	details.form.modified = false;

	details.form.CodeMirrorOptions = Object.append({
		'saveFunction': ui.save,
		'onChange'    : function () {
			details.form.modified = true;
		}
	}, CODE_MIRROR_OPTIONS);

	// Form elements
	details.form.Name       = new Element('input', { 'type': 'text', 'styles': { 'width': '250px' } });
	details.form.Identifier = new Element('input', { 'type': 'text', 'styles': { 'width': '250px' } });
	details.form.Priority   = new Element('input', { 'type': 'text', 'styles': { 'width': '250px' } });
	details.form.Start      = new Element('input', { 'type': 'time', 'styles': { 'width': '250px' } });
	details.form.Interval   = new Element('input', { 'type': 'number', 'styles': { 'width': '250px' } });

	details.form.Code = new Element('textarea', { 'class': 'CodeArea', 'value': xmlTemplate, 'styles': {
		'width' : '100%',
		'height': '300px'
	} });

	// Form functions
	details.form.reset = function () {
		details.form.selEntity.reset();
		details.form.selEvent.reset();
		details.form.Name.erase('value');
		details.form.Identifier.erase('value');
		details.form.Priority.value = '';
		details.form.Code.set('value', xmlTemplate);
		if ( details.form.CodeMirror != null ) {
			details.form.CodeMirror.setValue(xmlTemplate);
			details.form.CodeMirror.clearHistory();
			details.form.CodeMirror.refresh();
		}
	};
	details.form.setValues = function (values) {
		details.form.selEntity.reset();
		details.form.selEvent.reset();

		if ( values.Entity != null ) {
			details.form.selEntity.set(values.Entity);

			if ( values.Event != null )
				details.form.selEvent.set(values.Event);
		}

		details.form.Name.value       = ( values.Name == null ? '' : values.Name );
		details.form.Identifier.value = ( values.Identifier == null ? '' : values.Identifier );
		details.form.Priority.value   = ( values.Priority || '' );
		details.form.Code.value       = ( values.Code == null ? xmlTemplate : values.Code );
		details.form.Start.value      = ( values.Start ? formatTime(values.Start) : '00:00:00' );
		details.form.Interval.value   = ( values.Interval || 0 );

		if ( details.form.CodeMirror != null ) {
			details.form.CodeMirror.setValue(values.Code == null ? xmlTemplate : values.Code);
			details.form.CodeMirror.clearHistory();
		}
	};
	details.form.getValues = function () {
		var res = {
			'Name'      : details.form.Name.value,
			'Identifier': details.form.Identifier.value,
			'Priority'  : ( details.form.Priority.value || '' ),
			'Code'      : details.form.Code.value,
			'Entity'    : details.form.selEntity.getId(),
			'Event'     : details.form.selEvent.getId(),
			'Start'     : details.form.Start.value,
			'Interval'  : details.form.Interval.value
		};

		if ( details.form.CodeMirror != null ) {
			res.Code = details.form.CodeMirror.getValue();
		}

		return res;
	};

	details.btnActivate = new Element('input', {'type': 'button', 'class': 'btn btn-success f_r m2_r', 'value': _('action.restore')});
	details.btnActivate.addEvent('click', function () {
		if ( ui._current.Active )
			ui.deactivate();
		else
			ui.activate();
	});
	details.btnToggle = new gx.bootstrap.CheckButton(new Element('div', {'class': 'f_l'}), {'label': _('filter.syntax'), 'size': 'mini', 'value': activateSyntax});
	details.btnToggle.addEvent('change', function () {
		if ( this._checked && details.form.CodeMirror == null ) {
			details.form.CodeMirror = CodeMirror.fromTextArea(details.form.Code, details.form.CodeMirrorOptions);
		} else if ( !this._checked && details.form.CodeMirror != null ) {
			details.form.CodeMirror.toTextArea();
			delete details.form.CodeMirror;
		}
	});

	details.form.selEvent = new gx.bootstrap.Select(new Element('div'), {
		'width'         : '250px',
		'icon'          : 'bell',
		'label'         : {
			'text'      : _('entity.event.singular'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'requestData'   : {
			'api'       : 'plugin',
			'do'        : 'list_events',
			'entity'    : false
		},
		'requestParam'  : 'search',
		'listFormat'    : function (elem) {
			return _('plugin.event.' + elem);
		},
		'formatID'      : function (elem) {
			return elem;
		}
	});

	details.form.selEntity = new gx.bootstrap.Select(new Element('div'), {
		'width'         : '250px',
		'icon'          : 'tasks',
		'label'         : {
			'text'      : _('entity.entity.singular'),
			'class'     : 'dialog_label'
		},
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'requestData'   : {
			'api'       : 'plugin',
			'do'        : 'list_entities'
		},
		'requestParam'  : 'search',
		'listFormat'    : function (elem) {
			return _('entity.' + elem + '.singular');
		},
		'formatID'      : function (elem) {
			return elem;
		},
		'onSelect'      : function (selected) {
			if ( details.form.selEvent.options.requestData.entity != selected ) {
				details.form.selEvent.reset();
				details.form.selEvent.search();
				details.form.selEvent.options.requestData.entity = selected;
			}
		}
	});

	details.popup = new gx.bootstrap.Popup({
		'width': 850,
		'content': __({'children': [
			new gx.ui.HGroup([
				{
					'width'  : '50%',
					'content': new Element('div').adopt(
						Factory.InputPrepend({ 'icon': 'bookmark', 'label': _('field.name'), 'labelClasses': [ 'dialog_label' ], 'content': details.form.Name }),
						Factory.InputPrepend({ 'icon': 'tag', 'label': _('field.identifier'), 'labelClasses': [ 'dialog_label' ], 'content': details.form.Identifier }),
						Factory.InputPrepend({ 'icon': 'star', 'label': _('field.priority'), 'labelClasses': [ 'dialog_label' ], 'content': details.form.Priority })
					)
				},
				{
					'content': new Element('div').adopt(
						details.form.selEntity,
						details.form.selEvent,
						Factory.InputPrepend({ 'icon': 'bell', 'label': _('field.start'), 'labelClasses': [ 'dialog_label' ], 'content': details.form.Start }),
						Factory.InputPrepend({ 'icon': 'refresh', 'label': _('field.interval'), 'labelClasses': [ 'dialog_label' ], 'content': details.form.Interval })
					)
				}
			]),
			{'child': details.form.Code}
		]}),
		'title': 'Details',
		'footer': __({'children': {
			'btnOk': {'tag': 'input', 'type': 'button', 'class': 'f_r btn btn-primary', 'value': _('action.save'), 'onClick': function () {
				ui.save();
			}},
			'btnClose': {'tag': 'input', 'type': 'button', 'class': 'f_r btn m2_r', 'value': _('action.close'), 'onClick': function () {
				details.popup.hide();
			}},
			'btnExecute': {'tag': 'input', 'type': 'button', 'class': 'f_r btn btn-inverse m2_r', 'value': _('action.execute'), 'onClick': function () {
				ui.execute();
			}},
			'btnActivate': details.btnActivate,
			'btnToggle': details.btnToggle,
			'clear': {'class': 'clear'}
		}}),
		'closable': true
	});
	details.popup.addEvent('show', function () {
		if ( details.form.CodeMirror != null )
			details.form.CodeMirror.refresh();
	});

	if ( activateSyntax )
		details.form.CodeMirror = CodeMirror.fromTextArea(details.form.Code, details.form.CodeMirrorOptions);

	/* Table
	----------------------------------------------------------- */
	var tabPlugins = new gx.bootstrap.Table('tabPlugins', {
		'cols' : [
			{'label': '<i class="icon-check"></i>', 'id': 'check', 'width': '20px', 'filterable': 'false', 'clickable': false},
			{'label' : _('field.name'), 'id' : 'Name', 'filter' : 'asc'},
			{'label' : _('field.identifier'), 'id' : 'Identifier'},
			{'label' : _('entity.entity.singular'), 'id': 'Entity'},
			{'label' : _('entity.event.singular'), 'id': 'Event'}
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
		'onDblclick': function (row, event) {
			event.stop();
			deselect();
			ui.open(row.Id);
		},
		'structure' : function (row) {
			row.checkbox = new Element('input', {'type': 'radio', 'value': row.Id, 'name': 'selPlugin'});
			row.checkbox.addEvent('click', function (event) {
				ui._selection = row.checkbox.checked ? row.Id : false;
			});

			var link = new Element('a', {'html': row.Name}).addEvent('click', function () { ui.open(row.Id); });
			if ( !row.Active ) {
				link.addClass('deleted');
				link = new Element('div').adopt(
					new Element('span', {'class': 'm2_r label label-important', 'html': _('field.inactive')}),
					link
				);
			}

			return [
				row.checkbox,
				link,
				row.Identifier,
				_('entity.'+row.Entity+'.singular'),
				_('plugin.event.'+row.Event)
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
	var optActivate = btnOptions.add(_('action.activate'), 'play-circle').addEvent('click', function () {
		ui.activate();
	});
	var optDeactivate = btnOptions.add(_('action.deactivate'), 'off').addEvent('click', function () {
		ui.deactivate();
	});
	var optRefresh = btnOptions.add(_('action.refresh'), 'refresh').addEvent('click', function () {
		ui.list();
	});

	txtSearch = $('txtSearch');
	txtSearch.addEvent('keydown', function (event) {
		if ( event.key == 'enter' )
			ui.list();
	});

	var chkShowDel = new gx.bootstrap.CheckButton('chkShowDel', {'value': true, 'label': _('filter.inactive')});
	chkShowDel.addEvent('change', function () {
		ui.list();
	});

	if ( !AUTHENTICATED_USER.IsAdmin ) {
		$(optNew).hide();
		$(optRemove).hide();
		$(optActivate).hide();
		$(optDeactivate).hide();
	}

	/* Auto-resize
	----------------------------------------------------------- */
	var h = 150;
	var l = 200;
	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if ( ht < l )
			ht = l;
		tabPlugins.setHeight(ht+'px');
	}
	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	/* Load the list
	----------------------------------------------------------- */
	ui.list();
});