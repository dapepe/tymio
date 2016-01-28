//var error = '%error';
var error = '';
// var users = JSON.decode('%user_array');
var users = [];
// var access = {'_service': '%service_id', '_accesskey': '%service_key'};
var access = {};
// var force_user = '%force_user';
var force_user = ''; // If set to a username, switch to resticted mode for
// this user

var busy = false;
var lastStart = false;
var first = true;

var lastSelectedUser = null; // Share selected Users between dialogs

var urlBase = 'index.php';

function roundDec(num) {
	return Math.round(num * 100) / 100;
}
function parseDec(num) {
	return parseFloat(num, 10);
}
function parseB10(num) {
	return parseInt(num, 10);
}
function addZero(num) {
	return num < 10 ? ('0' + num) : num;
}
function formatTime(mins) {
	var prefix = '';
	if (mins == null)
		return '0:00';
	if (mins < 0) {
		mins = -mins;
		prefix = '-';
	}
	var timeInMinutes = Math.round(mins);
	var minutes = timeInMinutes % 60;
	var hours = Math.floor(timeInMinutes / 60);
	return prefix + hours + ':' + addZero(minutes) + 'h';
}
function getWeek(ts) {
	var a = new Date(ts * 1000);
	var b = new Date(a.getYear(), 1, 1);
	return Math.floor((a.UTC() - b.UTC()) / 604800000);
}
function getColorLabel(value1, value2) {
	if (value1 == null || value2 == null || value2 == 0)
		return '';

	var color = 'grey';
	if (value1 > value2)
		color = 'red';
	else if (value1 < value2)
		color = 'green';

	if (Math.abs(value2) < 1) {
		var sign = '';
		if (value2 < 0) {
			value2 = -value2;
			sign = '-';
		}
		seconds = Math.round(value2 * 60);
		return '<span class="bold ' + color + '">' + '[' + sign + seconds + 's]</span>'
	} else {
		return '<span class="bold ' + color + '">[' + formatTime(value2) + ']</span>';
	}
}

var types = [ 'REG', 'ÜAB', 'ÜAB/2', 'URL', 'URL/2', 'KRA', 'FEI', 'NEW', 'ACT', 'SCH' ];
var weekdays = [ 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' ];
var month = [ 'Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Jun.', 'Jul.', 'Aug.', 'Sept.', 'Okt.', 'Nov.', 'Dez.' ];
var monthlist = [ 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ];

function configureMonths(select) {
	if (select != null) {
		Array.each(month, function(name, index) {
			var option = new Element('option', {
				value : index,
				html : name
			});
			option.inject(select);
		});
	}
}
configureMonths($('txtClockingMonth'));
configureMonths($('txtDateMonth'));
configureMonths($('txtDateStatMonth'));

window.addEvent('domready', function() {
	var msg = new gx.groupion.Msgbox();
	if (error != '')
		msg.show(error, 'error');

	var btnClockingCalc = {
		'elem' : $('btnClockingCalc')
	};
	btnClockingCalc.enable = function() {
		if (btnClockingCalc.elem)
			btnClockingCalc.elem.erase('disabled');
	};
	btnClockingCalc.disable = function() {
		if (btnClockingCalc.elem)
			btnClockingCalc.elem.set('disabled', 'true');
	};
	if (btnClockingCalc.elem) {
		btnClockingCalc.elem.addEvent('click', function() {
			if (confirm('Möchten Sie wirklich alle Eintragungen automatisch vornehmen lassen?'))
				doClockingList(1);
		});
	}

	function sendForm(data, callback, loader, resulttype) {
		if (busy)
			return;

		if (typeOf(loader) == 'null')
			loader = $('loader');

		var req = new Request({
			'url' : urlBase,
			'data' : data,
			'method' : 'post',
			'onRequest' : function() {
				loader.addClass('active');
				busy = true;
			},
			'onComplete' : function() {
				loader.removeClass('active');
				busy = false;
			},
			'onFailure' : function() {
				msg.show('Connection error! Could not retrieve data from server!', 'connection');
			}
		});
		if (isFunction(callback)) {
			req.addEvent('success', function(json) {
				res = JSON.decode(json);
				if (typeOf(res) == 'object') {
					if (res.error != null) {
						msg.show('Server error: ' + res.error, 'error');
						return;
					}

					if (resulttype != null) {
						var t = typeOf(res.result);
						if (t != resulttype)
							msg.show('Invalid server response! Server returned "' + t + '", "' + resulttype + '" expected!', 'error');
						else {
							callback.apply(callback, [ res ]); // Call
						}
					} else {
						callback.apply(callback, [ res ]); // Call
					}
				} else
					msg.show('Invalid response: ' + json, 'error');
			});
		} else if (isObject(callback)) {
			for (evtType in callback)
				req.addEvent(evtType, callback[evtType]);
		}
		req.send();
	}

	/* -------------------- ACTIONS -------------------- */

	function doClockingList(autoCalc) {
		var filter = {
			'id' : 'start',
			'mode' : 'desc'
		};
		if (tabClocking)
			filter = tabClocking.getFilter();
		var user = txtClockingUsers.getSelected();
		sendForm({
			'api' : 'clocking',
			'do' : 'list',
			'month' : parseB10(txtClockingMonth.get('value')) + 1,
			'year' : txtClockingYear.get('value'),
			'userid' : user == null ? null : user.id,
			'showDel' : chkClockingShowDel.checked ? '1' : '0',
			'showBooked' : txtClockingShowBooked.get('value'),
			'autoCalc' : (autoCalc != null && autoCalc == true) ? 1 : 0,
			'orderby' : filter.id,
			'ordermode' : filter.mode
		}, function(res) {
			tabClocking.setData(res.result);
			if (res.mode != null && res.mode == 'calc')
				btnClockingCalc.enable();
			else
				btnClockingCalc.disable();

			if (viewBooking.frame != null) {
				if (res.last != null) {
					viewBooking.frame.setStyle('display', 'block');
					if (res.last.end || !res.last.start) {
						viewBooking.frame.removeClass('checked');
						viewBooking.frame.addClass('unchecked');
						viewBooking.label.set('html', 'Nicht angemeldet');
						viewBooking.btnAdd.set('value', 'Anmelden');
						viewBooking.mode = 'start';
						viewBooking.comment.set('value', '');
					} else {
						viewBooking.last = res.last;
						viewBooking.frame.removeClass('unchecked');
						viewBooking.frame.addClass('checked');
						viewBooking.label.set('html', 'Angemeldet seit ' + gx.util.printd('D.M., H:I', res.last.start));
						viewBooking.btnAdd.set('value', 'Abmelden');
						viewBooking.mode = 'end';
						viewBooking.comment.set('value', res.last.comment);
					}
				} else
					viewBooking.frame.setStyle('display', 'none');
			}

			if (user != null) {
				sendForm({
					'api' : 'clocking',
					'do' : 'stat',
					'year' : txtClockingYear.get('value'),
					'userid' : user.id
				}, function(res) {
					var text = res.used;// + '(' +
					// res.total +
					// ')';
					if (lblVacation) {
						lblVacation.set('html', text);
					}
				}, null, false);
			} else {
				if (lblVacation) {
					lblVacation.set('html', '<center>&ndash;</center>');
				}
			}

		}, null, 'array');
	}

	function doClockingSave(arg, onComplete) {
		sendForm(arg, function(res) {
			if (res.result != null) {
				msg.show('Eintrag erfolgreich gespeichert!', 'ok');
				if (typeOf(onComplete) == 'function')
					onComplete();
				doClockingList();
			} else if (res.error == null)
				msg.show('Eintrag konnte nicht gespeichert werden.', 'error');
		}, viewEdit.loader);
	}

	function doClockingRemove(ID, userid) {
		sendForm({
			'api' : 'clocking',
			'do' : 'delete',
			'id' : ID,
		}, function(res) {
			if (res.result == '1') {
				viewEdit.popup.hide();
				msg.show('Eintrag erfolgreich entfernt!', 'ok');
				doClockingList();
			} else if (res.error == null)
				msg.show('Eintrag konnte nicht gelöscht werden.', 'error');
		}, viewEdit.loader);
	}

	function doClockingStat() {
		var user = txtStatUsers.getSelected();
		var year = txtStatYear.get('value');
		var month = parseB10(txtStatMonth.get('value'));

		sendForm({
			'api' : 'clocking',
			'do' : 'stat',
			'month' : month + 1,
			'year' : year,
			'userid' : user == null ? null : user.id,
			'theuser' : user == null ? null : user.id
		}, function(res) {
			statBuildChart(res.result);
		}, null, 'array');
	}

	function doTransList() {
		var filter = {
			'id' : 'start',
			'mode' : 'desc'
		};
		if (tabTrans)
			filter = tabTrans.getFilter();
		var user = txtTransUsers.getSelected();
		sendForm({
			'api' : 'transaction',
			'do' : 'list',
			'userid' : user == null ? null : user.id,
			'orderby' : filter.id,
			'ordermode' : filter.mode,
			'type' : 0
		}, function(res) {
			tabTrans.setData(res.result);
		}, null, 'array');
	}

	function doTransClockings() {
		var user = viewTrans.username.getSelected();
		if (user == null) {
			return;
		}
		var userid = user.id;
		// var username = viewTrans.username.get('value');
		// if (username == -1)
		// return;

		sendForm({
			'api' : 'clocking',
			'do' : 'list',
			'month' : parseB10(viewTrans.month.get('value')) + 1,
			'year' : parseB10(viewTrans.year.get('value')),
			'userid' : userid,
			'showDel' : '0'
		}, function(res) {
			viewTrans.tabClockings.setData(res.result);
		}, viewTrans.loader, 'array');
	}

	function doTransSave() {
		var user = viewTrans.username.getSelected();
		if (user == null) {
			return;
		}
		sendForm({
			'api' : 'transaction',
			'do' : 'add',
			'month' : parseB10(viewTrans.month.get('value')) + 1,
			'year' : parseB10(viewTrans.year.get('value')),
			'userid' : user.id
		}, function(res) {
			if (res.result == '1') {
				viewTrans.popup.hide();
				msg.show('Eintrag erfolgreich gespeichert!', 'ok');
				doTransList();
			} else
				msg.show('Transaktion konnte nicht erstellt werden', 'error');
		}, viewTrans.loader);
	}

	function doVacationList() {
		var month = parseB10(txtDateMonth.get('value'));
		var year = parseB10(txtDateYear.get('value'));
		var user = txtAbsenceUsers.getSelected();
		var userid = user == null ? null : user.id;
		sendForm({
			'api' : 'clocking',
			'do' : 'vacations',
			'month' : month + 1,
			'year' : year,
			'userid' : userid
		}, function(res) {
			doVacationListCallback(month, year, res.result);
		}, null);
	}

	function doVacationListCallback(month, year, data) {
		var firstWeekday = new Date(year, month, 1).getDay();
		firstWeekday = (firstWeekday == 0 ? 7 : firstWeekday) - 1; // Convert
		// to
		// European
		// format
		var lastWeekday = new Date(year, month + 1, 0).getDay();
		lastWeekday = (lastWeekday == 0 ? 7 : lastWeekday) - 1; // Convert
		// to
		// European
		// format
		var monthDays = new Date(year, month + 1, 0).getDate();

		// Build up the table
		tabVacationBody.empty();
		var row = new Element('tr');
		var weekday = firstWeekday;

		// Add the first empty cells
		if (firstWeekday > 0) {
			for ( var i = 0; i < firstWeekday; i++) {
				row.adopt(new Element('td', {
					'class' : 'calDay calEmpty b_b b_r'
				}));
			}
		}
		for ( var day = 1; day <= monthDays; day++) {
			if (weekday > 6) {
				tabVacationBody.adopt(row);
				weekday = 0;
				var row = new Element('tr');
			}

			entries = [];
			if (data[day] != null) {
				// data[day].each(function(elem) {
				Object.each(data[day], function(elem) {
					if (elem.approved == 0) {
						entries.push('<div class="calEntry unapproved"' + '><span class="type' + elem.type + '">' + types[elem.type] + '</span> '
								+ (elem.realname != null && elem.realname != '' ? elem.realname : elem.title) + '</div>');
					} else {
						entries.push('<div class="calEntry"' + '><span class="type' + elem.type + '">' + types[elem.type] + '</span> '
								+ (elem.realname != null && elem.realname != '' ? elem.realname : elem.title) + '</div>');
					}
				});
			}
			var cell = new Element('td', {
				'class' : 'calDay b_b' + (weekday < 6 ? ' b_r' : ''),
				'html' : '<div class="calHead">' + day + '</div>' + entries.join('<div class="calLine"></div>')
			});
			cell.day = day;
			cell.addEvent('click', function(event) {
				var year = parseB10(txtDateYear.get('value'));
				var month = parseB10(txtDateMonth.get('value'));
				var date = new Date(year, month, this.day)
				viewAbsence.show(null, date, date);
			});
			row.adopt(cell);
			weekday++;
		}
		// Add the last empty cells
		for ( var i = weekday; i <= 6; i++) {
			row.adopt(new Element('td', {
				'class' : 'calDay calEmpty b_b' + (i < 6 ? ' b_r' : '')
			}));
		}
		tabVacationBody.adopt(row);
	}

	/* --------------------- VIEWS --------------------- */

	/* + + + + + + + + + + View: Booking popup + + + + + + + + + + */

	var viewBooking = {
		'body' : $('editBooking'),
		'btnClose' : $('bookingBtnClose'),
		'btnSave' : $('bookingBtnSave'),
		'comment' : $('bookingComment'),
		'arrow' : $('bookingArrow'),

		'dbxDate' : new gx.groupion.Datebox('bookingDbx', {
			'format' : [ 'd', '.', 'M', '.', 'y', '&nbsp;', 'h', ':', 'i' ],
			'month' : month
		}),
		'tbxBreak' : new gx.groupion.Timebox('bookingBreak', {
			'prefix' : false,
			'seconds' : false
		}),
		'fraBreak' : $('bookingBreakFrame'),

		'mode' : 'end',
		'last' : null,

		'frame' : $('statusFrame'),
		'label' : $('statusLabel'),
		'btnAdd' : $('statusBtn')
	};
	viewBooking.popup = new gx.groupion.Popup({
		'content' : viewBooking.body,
		'width' : 400,
		'closable' : false
	});
	if (viewBooking.btnClose)
		viewBooking.btnClose.addEvent('click', function() {
			viewBooking.popup.hide();
		});
	if (viewBooking.btnSave)
		viewBooking.btnSave.addEvent('click', function() {
			if (!force_user)
				alert('Nicht im User-Modus!');
			else if (confirm('Möchten Sie die Änderungen wirklich speichern?')) {
				var args = {
					'userid' : force_user,
					'comment' : viewBooking.comment.get('value'),
					'type' : 0,
					'api' : 'clocking'
				};
				if (viewBooking.mode == 'start') {
					args['do'] = 'add';
					args.start = viewBooking.dbxDate.get('seconds');
				} else {
					args['do'] = 'close';
					args['break'] = viewBooking.tbxBreak.get('minutes', 2);
					args.end = viewBooking.dbxDate.get('seconds');
				}

				doClockingSave(args, function() {
					viewBooking.popup.hide();
				});
			}
		});
	if (viewBooking.btnAdd)
		viewBooking.btnAdd.addEvent('click', function() {
			viewBooking.dbxDate.set(new Date().getTime());
			if (viewBooking.mode == 'start') {
				viewBooking.arrow.set('html', 'Kommt <div class="f_r arrow_right m2_l"></div>');
				viewBooking.fraBreak.setStyle('display', 'none');
			} else {
				viewBooking.tbxBreak.set();
				if (viewBooking.last != null && viewBooking.last.start != null) {
					var d = roundDec((viewBooking.dbxDate.get('seconds') - parseDec(viewBooking.last.start)) / 60);
					if (d > 390)
						viewBooking.tbxBreak.set(30, 'minutes');
					if (d > 570)
						viewBooking.tbxBreak.set(45, 'minutes');
				}
				viewBooking.arrow.set('html', 'Geht <div class="f_r arrow_left m2_l"></div>');
				viewBooking.fraBreak.setStyle('display', 'block');
			}
			viewBooking.popup.show();
		});

	/* + + + + + + + + + + View: Add new absence + + + + + + + + + + */

	var viewAbsence = {
		'body' : $('addAbsence'),
		'btnClose' : $('absenceBtnClose'),
		'btnSave' : $('absenceBtnSave'),
		'comment' : $('absenceComment'),
		'type' : $('absenceType'),
		'absenceStartDbx' : new gx.groupion.Datebox('absenceStartDbx', {
			'format' : [ 'd', '.', 'M', '.', 'y' ],
			'month' : month
		}),
		'absenceEndDbx' : new gx.groupion.Datebox('absenceEndDbx', {
			'format' : [ 'd', '.', 'M', '.', 'y' ],
			'month' : month
		}),

		'frame' : $('statusFrame'),
		'label' : $('statusLabel'),
		'btnAdd' : $('btnAbsenceAdd')
	};

	viewAbsence.username = new gx.groupion.Select('edtAbsenceUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function() {
			var selectedUser = viewAbsence.username.getSelected();
			if (selectedUser != null) {
				lastSelectedUser = selectedUser;
			}
		}
	});

	if (force_user) {
		viewAbsence.username.set(getUser(force_user));
		viewAbsence.username.disable();
	}

	viewAbsence.popup = new gx.groupion.Popup({
		'content' : viewAbsence.body,
		'width' : 400,
		'closable' : false
	});

	if (viewAbsence.btnClose)
		viewAbsence.btnClose.addEvent('click', function() {
			viewAbsence.popup.hide();
		});

	if (viewAbsence.btnSave)
		viewAbsence.btnSave.addEvent('click', function() {
			var user = null;
			if (force_user) {
				user = getUser(force_user);
			} else {
				user = viewAbsence.username.getSelected();
			}
			if (user == null) {
				alert('Bitte wählen Sie einen Benutzer.');
				return;
			}
			if (confirm('Möchten Sie die Änderungen wirklich speichern?')) {
				var start = viewAbsence.absenceStartDbx.get('seconds');
				var end = viewAbsence.absenceEndDbx.get('seconds');
				if (start > end) {
					alert('Der Start-Zeitpunkt darf nicht vor dem Endzeitpunkt liegen!');
					return;
				}

				var args = {
					'userid' : user.userid,
					'comment' : viewAbsence.comment.get('value'),
					'type' : viewAbsence.type.get('value'),
					'api' : 'clocking',
					'do' : 'absence.add',
					'start' : start,
					'end' : end,
					'approved' : 0
				};
				doClockingSave(args, function() {
					viewAbsence.popup.hide();
					tabs.updateCurrent();
				});
			}
		});

	if (viewAbsence.btnAdd) {
		viewAbsence.btnAdd.addEvent('click', function() {
			viewAbsence.absenceStartDbx.set(new Date().getTime());
			viewAbsence.absenceEndDbx.set(new Date().getTime());
			if (lastSelectedUser != null) {
				viewAbsence.username.set(lastSelectedUser);
			}
			viewAbsence.popup.show();
		});
	}

	viewAbsence.show = function(user, start, end) {
		var startDate = (start == null) ? new Date() : start;
		var endDate = (end == null) ? new Date() : end;
		viewAbsence.absenceStartDbx.set(startDate.getTime());
		viewAbsence.absenceEndDbx.set(endDate.getTime());
		if (force_user) {
			user = getUser(force_user);
			viewAbsence.username.disable();
		} else {
			viewAbsence.username.enable();
		}
		viewAbsence.username.set(user);
		viewAbsence.popup.show();
	};

	/* + + + + + + + + + + View: Clocking details + + + + + + + + + + */

	var viewEdit = {
		'body' : $('editView'),
		'loader' : $('editHeader'),
		'title' : $('editTitle'),
		'comment' : $('editComment'),
		'dbxStart' : new gx.groupion.Datebox('editDateStart', {
			'format' : [ 'd', '.', 'M', '.', 'y', '&nbsp;', 'h', ':', 'i' ],
			'month' : month
		}),
		'dbxEnd' : new gx.groupion.Datebox('editDateEnd', {
			'format' : [ 'd', '.', 'M', '.', 'y', '&nbsp;', 'h', ':', 'i' ],
			'month' : month
		}),
		'tbxBreak' : new gx.groupion.Timebox('editBreak', {
			'prefix' : false,
			'seconds' : false
		}),
		'tbxFlexitime' : new gx.groupion.Timebox('editFlexitime', {
			'prefix' : true,
			'seconds' : false
		}),
		'sugFlexitime' : $('editFlexitimeSuggest'),
		'tbxOvertime' : new gx.groupion.Timebox('editOvertime', {
			'prefix' : true,
			'seconds' : false
		}),
		'sugOvertime' : $('editOvertimeSuggest'),
		'tbxDenied' : new gx.groupion.Timebox('editDenied', {
			'prefix' : false,
			'seconds' : false
		}),
		'sugDenied' : $('editDeniedSuggest'),
		'tbxRegular' : new gx.groupion.Timebox('editRegular', {
			'prefix' : false,
			'seconds' : false,
			'readonly' : true
		}),
		'sugBreak' : $('editBreakSuggest'),
		'checked' : $('editChecked'),
		'approved' : $('editApproved'),
		'selType' : $('editType'),
		'btnSave' : $('editBtnSave'),
		'btnApprove' : $('editBtnApprove'),
		'btnRemove' : $('editBtnRemove'),
		'btnClose' : $('editBtnClose'),
		'btnCheck' : $('editBtnCheck'),
		'mode' : 'add',
	};
	viewEdit.optRegular = viewEdit.selType.getFirst();
	viewEdit.popup = new gx.groupion.Popup({
		'content' : viewEdit.body,
		'width' : 515,
		'closable' : false
	});
	viewEdit.btnClose.addEvent('click', function() {
		viewEdit.popup.hide();
	});
	if (viewEdit.checked) {
		viewEdit.checked.addEvent('click', function(event) {
			event.stopPropagation();
		});
	}
	if (viewEdit.btnCheck && viewEdit.checked) {
		viewEdit.btnCheck.addEvent('click', function() {
			if (viewEdit.checked.get('checked'))
				viewEdit.checked.erase('checked');
			else
				viewEdit.checked.set('checked', 'checked');
		});
	}
	if (viewEdit.approved) {
		viewEdit.approved.addEvent('click', function(event) {
			event.stopPropagation();
		});
	}
	if (viewEdit.btnApprove && viewEdit.approved) {
		viewEdit.btnApprove.addEvent('click', function() {
			if (viewEdit.approved.get('checked'))
				viewEdit.approved.erase('checked');
			else
				viewEdit.approved.set('checked', 'checked');
		});
	}
	viewEdit.dbxStart._fields.month.addEvent('change', function() {
		viewEdit.dbxEnd._fields.month.set('value', this.get('value'));
	});
	viewEdit.dbxStart._fields.day.addEvent('blur', function() {
		viewEdit.dbxEnd._fields.day.set('value', this.get('value'));
	});
	viewEdit.dbxStart._fields.year.addEvent('blur', function() {
		viewEdit.dbxStart._fields.year.set('value', this.get('value'));
	});

	function disableTypeFields() {
		var type = viewEdit.selType.get('value');
		if (type > 0) {
			viewEdit.dbxStart._fields.hour.set('disabled', 'disabled');
			viewEdit.dbxStart._fields.minute.set('disabled', 'disabled');
			viewEdit.dbxEnd._fields.hour.set('disabled', 'disabled');
			viewEdit.dbxEnd._fields.minute.set('disabled', 'disabled');
			viewEdit.tbxBreak.disable();
			viewEdit.tbxFlexitime.disable();
			viewEdit.tbxOvertime.disable();
			viewEdit.tbxDenied.disable();
			viewEdit.tbxRegular.disable();
			viewEdit.sugBreak.set('html', '');
			if (type == 2 || type == 4) {
				viewEdit.dbxEnd._fields.day.set('disabled', 'disabled');
				viewEdit.dbxEnd._fields.month.set('disabled', 'disabled');
				viewEdit.dbxEnd._fields.year.set('disabled', 'disabled');
			} else {
				viewEdit.dbxEnd._fields.day.erase('disabled');
				viewEdit.dbxEnd._fields.month.erase('disabled');
				viewEdit.dbxEnd._fields.year.erase('disabled');
			}
		} else {
			viewEdit.dbxStart._fields.hour.erase('disabled');
			viewEdit.dbxStart._fields.minute.erase('disabled');
			viewEdit.dbxEnd._fields.hour.erase('disabled');
			viewEdit.dbxEnd._fields.minute.erase('disabled');
			viewEdit.dbxEnd._fields.day.erase('disabled');
			viewEdit.dbxEnd._fields.month.erase('disabled');
			viewEdit.dbxEnd._fields.year.erase('disabled');

			viewEdit.tbxBreak.enable();
			if (!force_user) {
				viewEdit.tbxFlexitime.enable();
				viewEdit.tbxOvertime.enable();
				viewEdit.tbxDenied.enable();
				viewEdit.tbxRegular.enable();
			} else {
				viewEdit.tbxFlexitime.disable();
				viewEdit.tbxOvertime.disable();
				viewEdit.tbxDenied.disable();
				viewEdit.tbxRegular.disable();
			}
			updateWorkTime();
		}
	}

	viewEdit.selType.addEvent('change', function() {
		var type = viewEdit.selType.get('value');
		if (type == '1' || type == '3' || type == '5' || type == '9') {
			var start = new Date(viewEdit.dbxStart.get());
			start.setHours(0, 0, 0, 0);
			viewEdit.dbxStart.set(start.getTime());
			var end = new Date(viewEdit.dbxEnd.get());
			end.setHours(23, 59, 59, 0);
			viewEdit.dbxEnd.set(end.getTime());
		}
		disableTypeFields();
	});

	viewEdit.username = new gx.groupion.Select('editUser', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Bitte wählen)'
			},
			'noSelection' : '(Please select)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'formatID' : function(elem) {
			return elem ? elem.username : false;
		},
		'onSelect' : function() {
			var selectedUser = viewEdit.username.getSelected();
			if (selectedUser != null) {
				lastSelectedUser = selectedUser;
			}
		}
	});

	var btnClockingReset = $('btnClockingReset');
	if (btnClockingReset) {
		btnClockingReset.addEvent('click', function() {
			txtClockingUsers.set();
		});
	}

	function updateWorkTime(time) {
		if (viewEdit.selType.get('value') > 0) {
			viewEdit.tbxRegular.set(0, 'minutes');
			viewEdit.sugBreak.set('html', '');
			return;
		}

		if (time == null)
			time = viewEdit.dbxEnd.get() - viewEdit.dbxStart.get();

		var minutes = time / 60000; // Convert milliseconds to
		// minutes

		var brk = viewEdit.tbxBreak.get('minutes', 2);
		var type = viewEdit.selType.get('value');
		if (type == 0) {
			var brk2 = 0;
			if (minutes > 390)
				brk2 = 30;
			if (minutes > 570)
				brk2 = 45;
			if (brk < brk2)
				viewEdit.sugBreak.set('html', getColorLabel(brk, brk2));
			else
				viewEdit.sugBreak.set('html', '');
		}

		minutes = minutes - brk;
		viewEdit.tbxRegular.set(minutes, 'minutes');
	}
	viewEdit.dbxStart.addEvent('update', function() {
		updateWorkTime();
	});
	viewEdit.dbxEnd.addEvent('update', function() {
		updateWorkTime();
	});
	viewEdit.tbxBreak.addEvent('change', function() {
		updateWorkTime();
	});

	function getUser(userid) {
		var res = {
			'userid' : userid
		};
		if (users[userid] != null && users[userid] != userid)
			res.realname = users[username];
		return res;
	}
	// function getUser(userid) {
	// var res = {
	// 'userid' : userid
	// };
	// if (users[username] != null
	// && users[username] != username)
	// res.realname = users[username];
	// return res;
	// }

	viewEdit.set = function(data, hideOpt) {
		if (hideOpt != null)
			viewEdit.optRegular.dispose();
		else if (viewEdit.optRegular.getParent() == null)
			viewEdit.optRegular.inject(viewEdit.selType, 'top');

		if (viewEdit.btnApprove) {
			viewEdit.btnApprove.setStyle('display', 'none');
		}

		if (data == null) {
			var d = new Date();
			viewEdit.dbxStart.set(d.getTime());
			viewEdit.dbxEnd.set(d.getTime());

			viewEdit.comment.set('value', '');
			viewEdit.selType.set('value', 0);
			if (viewEdit.checked)
				viewEdit.checked.erase('checked');
			if (viewEdit.approved)
				viewEdit.checked.erase('checked');

			if (!force_user) {
				// viewEdit.username.reset();
				var selectedUser = txtClockingUsers.getSelected();
				if (selectedUser != null) {
					viewEdit.username.set(selectedUser);
				} else if (lastSelectedUser != null) {
					viewEdit.username.set(lastSelectedUser);
				}

				viewEdit.username.enable();
			} else {
				viewEdit.username.set(getUser(force_user));
				viewEdit.username.disable();
			}

			viewEdit.tbxBreak.set();
			viewEdit.tbxFlexitime.set();
			viewEdit.tbxOvertime.set();
			viewEdit.tbxDenied.set();
			viewEdit.sugFlexitime.set('html', '');
			viewEdit.sugOvertime.set('html', '');
			viewEdit.sugDenied.set('html', '');
			viewEdit.sugBreak.set('html', '');

			if (viewEdit.btnRemove)
				viewEdit.btnRemove.setStyle('display', 'none');
			if (viewEdit.btnSave)
				viewEdit.btnSave.erase('disabled');

			viewEdit.mode = 'add';
			viewEdit.ID = -1;
			updateWorkTime(0);
		} else {
			// Assign values
			var start = new Date(data.start * 1000);
			var end = new Date(data.end * 1000);

			viewEdit.dbxStart.set(start.getTime());
			viewEdit.dbxEnd.set(end.getTime());

			viewEdit.comment.set('value', data.comment);
			viewEdit.selType.set('value', data.type);

			if (viewEdit.checked && data.checked == 1)
				viewEdit.checked.set('checked', 'checked');
			else
				viewEdit.checked.erase('checked');

			if (viewEdit.approved) {
				if (data.approved == 1)
					viewEdit.approved.set('checked', 'checked');
				else
					viewEdit.approved.erase('checked');
			}

			viewEdit.username.set(getUser(data.username));
			viewEdit.username.disable();

			viewEdit.tbxBreak.set(data['break'], 'minutes');
			viewEdit.tbxFlexitime.set(data.flexitime, 'minutes');
			viewEdit.tbxOvertime.set(data.overtime, 'minutes');
			viewEdit.tbxDenied.set(data.denied, 'minutes');
			viewEdit.sugFlexitime.set('html', getColorLabel(data.flexitime, data.flexitime2));
			viewEdit.sugOvertime.set('html', getColorLabel(data.overtime, data.overtime2));
			viewEdit.sugDenied.set('html', getColorLabel(data.denied, data.denied2));
			viewEdit.sugBreak.set('html', getColorLabel(data['break'], data.resttime2));

			if (viewEdit.btnRemove && viewEdit.btnSave) {
				viewEdit.btnRemove.setStyle('display', 'inline');
				if (data.visibility == 1) {
					viewEdit.btnSave.set('disabled', 'disabled');
					viewEdit.btnRemove.set('disabled', 'disabled');
				} else {
					viewEdit.btnSave.erase('disabled');
					viewEdit.btnRemove.erase('disabled');
				}
			}

			if (viewEdit.btnApprove) {
				if (data.needsApproval == 1) {
					viewEdit.btnApprove.setStyle('display', 'block');
				}
			}

			viewEdit.mode = 'update';
			viewEdit.ID = data.ID;

			updateWorkTime(end.getTime() - start.getTime());
		}
		disableTypeFields();
		
		if (viewEdit.btnClose) {
			viewEdit.btnClose.erase('disabled');
		}
	}

	if (viewEdit.btnSave) {
		viewEdit.btnSave.addEvent('click', function() {
			if (confirm('Möchten Sie die Änderungen wirklich speichern?')) {
				var start = viewEdit.dbxStart.get('seconds');
				var end = viewEdit.dbxEnd.get('seconds');
				var brk = viewEdit.tbxBreak.get('minutes', 2);
				var recType = viewEdit.selType.get('value');

				if (brk < 0) {
					alert('Die Pause darf nicht negativ sein!');
					return;
				}
				if (start > end) {
					alert('Der Start-Zeitpunkt darf nicht vor dem Endzeitpunkt liegen!');
					return;
				}
				if (recType == 0 && (end - start) <= brk) {
					alert('Die Pause darf nicht länger als die Arbeitszeit sein!');
					return;
				}
				var user = viewEdit.username.getSelected();
				if (user == null) {
					alert('Kein Benutzer ausgewählt!');
					return;
				}

				var args = {
					'api' : 'clocking',
					'userid' : user.id,
					'start' : start,
					'end' : end,
					'break' : brk,
					'comment' : viewEdit.comment.get('value'),
					'flexitime' : viewEdit.tbxFlexitime.get('minutes', 2),
					'overtime' : viewEdit.tbxOvertime.get('minutes', 2),
					'denied' : viewEdit.tbxDenied.get('minutes', 2),
					'checked' : viewEdit.checked != null ? (viewEdit.checked.get('checked') ? 1 : 0) : 0,
					'approved' : viewEdit.approved != null ? (viewEdit.approved.get('checked') ? 1 : 0) : 0,
					'type' : recType
				};

				if (viewEdit.mode == 'update') {
					args.ID = viewEdit.ID;
					args['do'] = 'update';
				} else
					args['do'] = 'add';

				doClockingSave(args, function() {
					viewEdit.popup.hide();
				});
			}
		});
	}

	if (viewEdit.btnRemove) {
		viewEdit.btnRemove.addEvent('click', function() {
			if (viewEdit.mode != 'update') {
				alert('Entfernen nur im Editier-Modus erlaubt!');
				return;
			}
			if (confirm('Möchten Sie diesen Eintrag wirklich entfernen?')) {
				doClockingRemove(viewEdit.ID, viewEdit.username.getID());
			}
		});
	}

	/* ---------------------- FORM --------------------- */

	var txtClockingMonth = $('txtClockingMonth');
	configureMonths(txtClockingMonth);

	var txtClockingYear = $('txtClockingYear');
	if (txtClockingYear) {
		var year = new Date().getFullYear();
		txtClockingYear.set('value', year);
	}

	var chkClockingShowDel = $('chkClockingShowDel');
	var txtClockingShowBooked = $('txtClockingShowBooked');
	txtClockingShowBooked.addEvent('change', function() {
		doClockingList();
	});

	/* + + + + + + + + + + UI: Clocking Users + + + + + + + + + */

	var txtClockingUsers = new gx.groupion.Select('txtClockingUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			doClockingList();
			var user = txtClockingUsers.getSelected();
			if (user != null) {
				lastSelectedUser = user;
			}
		},
		'onNoSelect' : function(sel) {
			doClockingList();
		}
	});
	if (force_user) {
		txtClockingUsers.set(getUser(force_user));
		txtClockingUsers.disable();
	}
	var btnClockingReset = $('btnClockingReset');
	if (btnClockingReset) {
		btnClockingReset.addEvent('click', function() {
			txtClockingUsers.set();
		});
	}

	/* + + + + + + + + + + UI: Date + + + + + + + + + + */

	$('btnClockingNext').addEvent('click', function() {
		var m = parseB10(txtClockingMonth.get('value'));
		if (m >= 11) {
			var y = parseB10(txtClockingYear.get('value'));
			txtClockingYear.set('value', y + 1);
			txtClockingMonth.set('value', 0);
		} else
			txtClockingMonth.set('value', m + 1);
		doClockingList();
	});
	$('btnClockingBack').addEvent('click', function() {
		var m = parseB10(txtClockingMonth.get('value'));
		if (m <= 0) {
			var y = parseB10(txtClockingYear.get('value'));
			txtClockingYear.set('value', y - 1);
			txtClockingMonth.set('value', 11);
		} else
			txtClockingMonth.set('value', m - 1);
		doClockingList();
	});
	txtClockingMonth.addEvent('change', function() {
		doClockingList();
	});
	txtClockingYear.addEvent('blur', function() {
		doClockingList();
	});
	chkClockingShowDel.addEvent('click', function() {
		doClockingList();
	});

	/* + + + + + + + + + + UI: Clocking Table + + + + + + + + + + */

	var lblTotal = $('lblTotal');
	var lblFlexitime = $('lblFlexitime');
	var lblFlexitimeMin = $('lblFlexitimeMin');
	var lblOvertime = $('lblOvertime');
	var lblOvertimeMin = $('lblOvertimeMin');
	var lblVacation = $('lblVacation');
	// var lblDenied = $('lblDenied');

	var intTotal = 0;
	var intFlexitime = 0;
	var intFlexitimeMin = 0;
	var intOvertime = 0;
	var intOvertimeMin = 0;
	var intDenied = 0;

	var tabClocking = new gx.groupion.Table('tabClocking', {
		'cols' : [ {
			'label' : 'Benutzer',
			'id' : 'username'
		}, {
			'label' : 'Tag',
			'id' : 'date',
			'filter' : 'desc'
		}, {
			'label' : 'Von - Bis (Zeit)',
			'id' : 'start'
		}, {
			'label' : 'Pause',
			'id' : 'break',
			'filterable' : false
		}, {
			'label' : 'Gleitzeit',
			'id' : 'flexitime'
		}, {
			'label' : 'Überstunden',
			'id' : 'overtime'
		}, {
			'label' : 'Verfallen',
			'id' : 'denied'
		}, {
			'label' : 'Aufgaben',
			'id' : 'effort',
			'filterable' : false
		}, {
			'label' : 'Typ',
			'id' : 'type'
		}, {
			'label' : 'Zeichen',
			'id' : 'booked',
			'width' : '50px',
			'filterable' : false
		} ],
		'onFilter' : function(col) {
			doClockingList();
		},
		'onStart' : function() {
			intTotal = 0;
			intFlexitime = 0;
			intFlexitimeMin = 0;
			intOvertime = 0;
			intOvertimeMin = 0;
			intDenied = 0;
		},
		'onComplete' : function() {
			lblTotal.set('html', formatTime(intTotal));
			lblFlexitime.set('html', formatTime(intFlexitime));
			lblFlexitimeMin.set('html', formatTime(intFlexitimeMin));
			lblOvertime.set('html', formatTime(intOvertime));
			lblOvertimeMin.set('html', formatTime(intOvertimeMin));
			// lblDenied.set('html',
			// formatTime(intDenied));
			if (txtClockingUsers.getSelected() == null) {
				var empty = '<center>&ndash;</center>';
				lblTotal.set('html', empty);
				lblFlexitime.set('html', empty);
				lblFlexitimeMin.set('html', empty);
				lblOvertime.set('html', empty);
				lblOvertimeMin.set('html', empty);
			} else {
				var user = txtClockingUsers.getSelected();
				sendForm({
					'api' : 'transaction',
					'do' : 'info',
					'userid' : user == null ? null : user.id,
					'month' : txtClockingMonth.get('value'),
					'year' : txtClockingYear.get('value')
				}, function(res) {
					lblTotal.set('html', formatTime(intTotal));
					lblFlexitime.set('html', formatTime(intFlexitime) + ' (' + formatTime(res.flexitime) + ')');
					lblFlexitimeMin.set('html', formatTime(intFlexitimeMin));
					lblOvertime.set('html', formatTime(intOvertime) + ' (' + formatTime(res.overtime) + ')');
					lblOvertimeMin.set('html', formatTime(intOvertimeMin));
				}, null, null);
			}
		},
		'onClick' : function(row) {
			viewEdit.set(row);
			viewEdit.popup.show();
		},
		'structure' : function(row) {
			var daytime = 0;
			if (row.type == 0 || row.type == 8)
				daytime = ((row.end - row.start) / 60) - row.resttime;

			var start = new Date(row.start * 1000);
			var end = new Date(row.end * 1000);
			var dayCount = row.dayCount;

			var critical = '';
			var colFlexitime = formatTime(row.flexitime);
			var colOvertime = formatTime(row.overtime);
			var colDenied = formatTime(row.denied);
			var colBreak = formatTime(row.resttime);

			if (row.flexitime2 != null && row.flexitime != row.flexitime2) {
				var critical = '<img title="Fehlerhaft" class="signal_no" />';
				colFlexitime = colFlexitime + ' ' + getColorLabel(row.flexitime, row.flexitime2);
			}
			if (row.overtime2 != null && row.overtime != row.overtime2)
				colOvertime = colOvertime + ' ' + getColorLabel(row.overtime, row.overtime2);
			if (row.denied2 != null && row.denied != row.denied2)
				colDenied = colDenied + ' ' + getColorLabel(row.denied, row.denied2);
			if (row.resttime2 != null && row.resttime != row.resttime2)
				colBreak = colBreak + ' ' + getColorLabel(row.resttime, row.resttime2);

			intTotal = intTotal + daytime;
			var ft = parseDec(row.flexitime);
			if (ft < 0)
				intFlexitimeMin = intFlexitimeMin + ft;
			else
				intFlexitime = intFlexitime + ft;

			var ot = parseDec(row.overtime);
			if (ot < 0)
				intOvertimeMin = intOvertimeMin + ot;
			else
				intOvertime = intOvertime + ot;
			intDenied = intDenied + parseDec(row.denied);

			var type = '<span class="type' + row.type + '">' + types[row.type] + '</span>';

			var day_label = weekdays[start.getDay()] + ', ' + addZero(start.getDate()) + '.' + addZero(start.getMonth() + 1) + '.' + start.getFullYear();
			var from_to_label = addZero(start.getHours()) + ':' + addZero(start.getMinutes()) + ' - ' + addZero(end.getHours()) + ':'
					+ addZero(end.getMinutes()) + ' (' + formatTime(daytime) + ')' + (dayCount > 1 ? (' - ' + dayCount + ' Tage') : '');

			var css_class_center = 'center';
			var css_class = '';
			if (row.approved == 0) {
				css_class = css_class + ' ' + 'unapproved';
				css_class_center = css_class_center + ' ' + 'unapproved';
			}

			if (row.type != 8) {
				result = [
						{
							'label' : row.username,
							'className' : css_class
						},
						{
							'label' : day_label,
							'className' : css_class
						},
						{
							'label' : from_to_label,
							'className' : css_class
						},
						{
							'label' : colBreak,
							'className' : css_class_center
						},
						{
							'label' : colFlexitime,
							'className' : css_class_center
						},
						{
							'label' : colOvertime,
							'className' : css_class_center
						},
						{
							'label' : colDenied,
							'className' : css_class_center
						},
						{
							'label' : row.task_count + ' (' + formatTime(row.task_sum) + ')',
							'className' : css_class_center
						},
						{
							'label' : type
						},
						(row.transaction ? '<img title="Gebucht" class="signal_booked" />'
								: (row.visibility == 1 ? '<img title="Gelöscht" class="signal_hold" />'
										: (row.checked == 1 ? '<img title="Geprüft" class="signal_ok" />' : critical))) ];
			} else {
				var empty = {
					'label' : '&ndash;',
					'className' : 'graybg center'
				};
				result = [ {
					'label' : row.username,
					'className' : 'graybg'
				}, {
					'label' : day_label,
					'className' : 'graybg'
				}, {
					'label' : from_to_label,
					'className' : 'graybg'
				}, empty, empty, empty, empty, empty, {
					'label' : type,
					'className' : 'graybg center'
				}, {
					'label' : '',
					'className' : 'graybg'
				} ];
			}

			return result;
		}
	});
	$('btnClockingView').addEvent('click', function() {
		doClockingList();
	});
	var btnClockingNew = $('btnClockingNew');
	if (btnClockingNew)
		btnClockingNew.addEvent('click', function() {
			viewEdit.set();
			viewEdit.popup.show();
		});

	if (force_user) {
		$('editFlexitime').set('disabled', 'disabled');
	}

	/*
	 * ======================== TRANSACTIONS ========================
	 */

	/* + + + + + + + + + + UI: Transaction Details + + + + + + + + + */

	var viewTrans = {
		'body' : $('transView'),
		'loader' : $('transHeader'),
		'title' : $('transTitle'),
		// 'username': $('transUser'),
		'month' : $('transMonth'),
		'year' : $('transYear'),
		'btnSave' : $('transBtnSave'),
		'btnClose' : $('transBtnClose'),
		'btnPrint' : $('transBtnPrint'),
		'lblTotal' : $('lblTransTotal'),
		'lblFlexitime' : $('lblTransFlexitime'),
		'lblFlexitimeMin' : $('lblTransFlexitimeMin'),
		'lblOvertime' : $('lblTransOvertime'),
		'lblOvertimeMin' : $('lblTransOvertimeMin'),
		'lblDenied' : $('lblTransDenied'),
		'lblConsistent' : $('lblTransConsistent'),
		'intTotal' : 0,
		'intFlexitime' : 0,
		'intOvertime' : 0,
		'intDenied' : 0,
		'bolConsistent' : true,
		'current' : null
	};
	viewTrans.popup = new gx.groupion.Popup({
		'content' : viewTrans.body,
		'width' : 700,
		'closable' : false
	});

	viewTrans.username = new gx.groupion.Select('transUser', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Bitte wählen)'
			},
			'noSelection' : '(Please select)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'formatID' : function(elem) {
			return elem ? elem.username : false;
		},
		'onSelect' : function(sel) {
			doTransClockings();
			var selectedUser = viewTrans.username.getSelected();
			if (selectedUser != null) {
				lastSelectedUser = selectedUser;
			}
		},
		'onNoSelect' : function(sel) {
			doTransClockings();
		}
	});

	// viewTrans.username.addEvent('change', function() {
	// alert('[change] --> doTransClockings');
	// doTransClockings();
	// });
	viewTrans.month.addEvent('change', function() {
		doTransClockings();
	});
	viewTrans.year.addEvent('blur', function() {
		doTransClockings();
	});
	viewTrans.btnClose.addEvent('click', function() {
		viewTrans.popup.hide();
	});
	viewTrans.btnSave.addEvent('click', function() {
		if (confirm('Möchten Sie diese Transaktion wirklich abschließen? Änderungen können nicht mehr vorgenommen werden!'))
			doTransSave();
	});
	viewTrans.btnPrint.addEvent('click', function() {
		if (viewTrans.current)
			window.open(urlBase + '?_service=' + access._service + '&_accesskey=' + access._accesskey + '&do=trans.print&ID=' + viewTrans.current.ID);
	});

	viewTrans.current = null;
	viewTrans.set = function(data) {
		var d;
		viewTrans.current = data;
		viewTrans.lblConsistent.set('html', '');
		viewTrans.lblConsistent.set('html', '-');
		viewTrans.lblTotal.set('html', formatTime(0));
		viewTrans.lblFlexitime.set('html', formatTime(0));
		viewTrans.lblFlexitimeMin.set('html', formatTime(0));
		viewTrans.lblOvertime.set('html', formatTime(0));
		viewTrans.lblOvertimeMin.set('html', formatTime(0));
		viewTrans.lblDenied.set('html', formatTime(0));

		if (data == null) {
			d = new Date();
			viewTrans.username.set('value', -1);

			viewTrans.btnSave.erase('disabled');
			viewTrans.username.enable();
			viewTrans.month.erase('disabled');
			viewTrans.year.erase('disabled');

			if (lastSelectedUser != null) {
				viewTrans.username.set(lastSelectedUser);
			}

		} else {
			d = new Date(data.date * 1000);
			viewTrans.username.set(getUser(data.username));
			viewTrans.btnSave.set('disabled', 'disabled');
			viewTrans.username.disable();
			viewTrans.month.set('disabled', 'disabled');
			viewTrans.year.set('disabled', 'disabled');
		}

		viewTrans.month.set('value', d.getMonth());
		viewTrans.year.set('value', d.getFullYear());

		if (data == null)
			viewTrans.tabClockings.empty();
		else
			doTransClockings();

		viewTrans.popup.show();
	};

	viewTrans.tabClockings = new gx.groupion.Table('tabTransClockings', {
		'cols' : [ {
			'label' : 'Tag',
			'id' : 'date',
			'filterable' : false
		}, {
			'label' : 'Von - Bis (Zeit)',
			'id' : 'start',
			'filterable' : false
		}, {
			'label' : 'Pause',
			'id' : 'break',
			'filterable' : false
		}, {
			'label' : 'Gleitzeit',
			'id' : 'flexitime',
			'filterable' : false
		}, {
			'label' : 'Überstunden',
			'id' : 'overtime',
			'filterable' : false
		}, {
			'label' : 'Verfallen',
			'id' : 'denied',
			'filterable' : false
		}, {
			'label' : 'Zeichen',
			'id' : 'booked',
			'width' : '20px',
			'filterable' : false
		} ],
		'onStart' : function() {
			viewTrans.intTotal = 0;
			viewTrans.intFlexitime = 0;
			viewTrans.intFlexitimeMin = 0;
			viewTrans.intOvertime = 0;
			viewTrans.intOvertimeMin = 0;
			viewTrans.intDenied = 0;
			viewTrans.bolConsistent = true;
		},
		'onComplete' : function() {
			var totaltime = formatTime(viewTrans.intTotal);
			var flexitime = formatTime(viewTrans.intFlexitime);
			var flexitimeMin = formatTime(viewTrans.intFlexitimeMin);
			var overtime = formatTime(viewTrans.intOvertime);
			var overtimeMin = formatTime(viewTrans.intOvertimeMin);
			var denied = formatTime(viewTrans.intDenied);

			if (!isNaN(viewTrans.current) && viewTrans.current != null) {
				if (viewTrans.current.time != roundDec(viewTrans.intTotal / 60))
					totaltime = totaltime + ' ' + getColorLabel(viewTrans.intTotal, viewTrans.current.time);
				if (viewTrans.current.flexitime != viewTrans.intFlexitime)
					flexitime = flexitime + ' ' + getColorLabel(viewTrans.intFlexitime, viewTrans.current.flexitime);
				if (viewTrans.current.overtime != viewTrans.intOvertime)
					overtime = overtime + ' ' + getColorLabel(viewTrans.intOvertime, viewTrans.current.overtime);
				if (viewTrans.current.denied != viewTrans.intDenied)
					denied = denied + ' ' + getColorLabel(viewTrans.intDenied, viewTrans.current.denied);
			}

			viewTrans.lblTotal.set('html', totaltime);
			viewTrans.lblFlexitime.set('html', flexitime);
			viewTrans.lblFlexitimeMin.set('html', flexitimeMin);
			viewTrans.lblOvertime.set('html', overtime);
			viewTrans.lblOvertimeMin.set('html', overtimeMin);
			viewTrans.lblDenied.set('html', denied);

			viewTrans.lblConsistent
					.set('html', viewTrans.bolConsistent ? '<div class="signal_ok m2_r"></div> Ok' : '<div class="signal_hold m2_r"></div> Nein');
		},
		'structure' : function(row) {
			var daytime = ((row.end - row.start) / 60) - (row['break']);

			var start = new Date(row.start * 1000);
			var end = new Date(row.end * 1000);

			var critical = '';
			var colFlexitime = formatTime(row.flexitime);
			var colOvertime = formatTime(row.overtime);
			var colDenied = formatTime(row.denied);

			if (row.flexitime2 != null) {
				viewTrans.bolConsistent = false;
				var critical = '<img title="Fehlerhaft" class="signal_no" />';
				colFlexitime = colFlexitime + ' ' + getColorLabel(row.flexitime, row.flexitime2);
			}
			if (row.overtime2 != null)
				colOvertime = colOvertime + ' ' + getColorLabel(row.overtime, row.overtime2);
			if (row.denied2 != null)
				colDenied = colDenied + ' ' + getColorLabel(row.denied, row.denied2);

			viewTrans.intTotal = viewTrans.intTotal + daytime;
			var ft = parseDec(row.flexitime);
			if (ft < 0)
				viewTrans.intFlexitimeMin = viewTrans.intFlexitimeMin + ft;
			else
				viewTrans.intFlexitime = viewTrans.intFlexitime + ft;
			var ot = parseDec(row.overtime);
			if (ot < 0)
				viewTrans.intOvertime = viewTrans.intOvertime + ot;
			else
				viewTrans.intDenied = viewTrans.intDenied + ot;

			return [
					weekdays[start.getDay()] + ', ' + addZero(start.getDate()) + '.' + addZero(start.getMonth() + 1) + '.' + start.getFullYear(),
					addZero(start.getHours()) + ':' + addZero(start.getMinutes()) + ' - ' + addZero(end.getHours()) + ':' + addZero(end.getMinutes()) + ' ('
							+ formatTime(daytime) + ')',
					new Element('center', {
						'html' : formatTime(row['break'])
					}),
					new Element('center', {
						'html' : colFlexitime
					}),
					new Element('center', {
						'html' : colOvertime
					}),
					new Element('center', {
						'html' : colDenied
					}),
					(row.transaction ? '<img title="Gebucht" class="signal_ok" />' : '')
							+ (row.visibility == 1 ? '<img title="Gelöscht" class="signal_hold" />' : '') + critical ];
		}
	});

	/* + + + + + + + + + + UI: Transaction Users + + + + + + + + + */

	var txtTransUsers = new gx.groupion.Select('txtTransUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			doTransList();
		},
		'onNoSelect' : function(sel) {
			doTransList();
		}
	});
	if (force_user) {
		txtTransUsers.set(getUser(force_user));
		txtTransUsers.disable();
	}
	var btnTransReset = $('btnTransReset');
	if (btnTransReset) {
		btnTransReset.addEvent('click', function() {
			txtTransUsers.set();
		});
	}

	/* + + + + + + + + + + UI: Transaction Table + + + + + + + + + + */

	var tabTrans = new gx.groupion.Table('tabTrans', {
		'cols' : [ {
			'label' : 'Benutzer',
			'id' : 'username'
		}, {
			'label' : 'Monat',
			'id' : 'date',
			'filter' : 'desc'
		}, {
			'label' : 'Arbeitszeit',
			'id' : 'time'
		}, {
			'label' : 'Gleitzeit',
			'id' : 'flexitime'
		}, {
			'label' : 'Überstunden',
			'id' : 'overtime'
		}, {
			'label' : 'Abgelehnt',
			'id' : 'denied'
		} ],
		'onFilter' : function(col) {
			doTransList();
		},
		'onClick' : function(row) {
			viewTrans.set(row);
		},
		'structure' : function(row) {
			var d = new Date(row.date * 1000);

			return [ row.username, month[d.getMonth()] + ' ' + d.getFullYear(), formatTime(row.time), formatTime(row.flexitime), formatTime(row.overtime),
					formatTime(row.denied) ];
		}
	});

	$('btnTransView').addEvent('click', function() {
		doTransList();
	});
	var btnTransNew = $('btnTransNew');
	if (btnTransNew) {
		btnTransNew.addEvent('click', function() {
			viewTrans.set();
		});
	}

	/* + + + + + + + + + + UI: Vacation Table + + + + + + + + + + */

	var tabVacationBody = $('tabVacationBody');
	var txtDateMonth = $('txtDateMonth');
	var txtDateYear = $('txtDateYear');

	configureMonths(txtDateMonth);

	$('btnVacationNew').addEvent('click', function() {
		viewEdit.set(null, true);
		viewEdit.selType.set('value', 1);
		viewEdit.popup.show();
	});

	txtDateMonth.addEvent('change', function() {
		doVacationList();
	});
	txtDateYear.addEvent('blur', function() {
		doVacationList();
	});
	$('btnDateBack').addEvent('click', function() {
		var month = parseB10(txtDateMonth.get('value'));
		if (month == 0) {
			txtDateMonth.set('value', 11);
			txtDateYear.set('value', parseB10(txtDateYear.get('value')) - 1);
		} else
			txtDateMonth.set('value', month - 1);

		doVacationList();
	});
	$('btnDateNext').addEvent('click', function() {
		var month = parseB10(txtDateMonth.get('value'));
		if (month == 11) {
			txtDateMonth.set('value', 0);
			txtDateYear.set('value', parseB10(txtDateYear.get('value')) + 1);
		} else
			txtDateMonth.set('value', month + 1);

		doVacationList();
	});

	var txtAbsenceUsers = new gx.groupion.Select('txtAbsenceUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			doVacationList();
		},
		'onNoSelect' : function(sel) {
			doVacationList();
		}
	});
	if (force_user) {
		txtAbsenceUsers.set(getUser(force_user));
		txtAbsenceUsers.disable();
	}
	var btnAbsenceReset = $('btnAbsenceReset');
	if (btnAbsenceReset) {
		btnAbsenceReset.addEvent('click', function() {
			txtAbsenceUsers.set();
		});
	}

	/* + + + + + + + + + + UI: Statistics + + + + + + + + + + */

	var txtStatMonth = $('txtDateStatMonth');
	var txtStatYear = $('txtDateStatYear');
	var statData = $('statData');
	var statDataBody = $('statDataBody');
	var statDataFoot = $('statDataFoot');
	var statChart;

	function statBuildChart(data) {
		trFoot = new Element('tr');
		statDataBody.empty();
		statDataFoot.empty();
		data.each(function(item, day) {
			day++;
			trFoot.adopt(new Element('td', {
				'html' : day
			}));
			var teffort = 0;
			item.tasks.each(function(task) {
				teffort = teffort + parseDec(task.effort);
			});

			tr = new Element('tr');
			tr.adopt(new Element('td', {
				'html' : roundDec(parseDec(item.records.time) / 3600)
			}));
			tr.adopt(new Element('td', {
				'html' : roundDec(teffort / 60)
			}));

			statDataBody.adopt(tr);
		});
		statDataFoot.adopt(trFoot);

		statDrawChart();
	}

	function statDrawChart() {
		var s = tabStatDiv.getSize();

		if (statChart != null && statChart.container != null)
			statChart.container.destroy();

		statChart = new MilkChart.Line(statData, {
			'width' : s.x - 10,
			'height' : s.y - 10,
			'border' : false,
			'showTicks' : true
		});
	}

	txtStatMonth.addEvent('change', function() {
		doClockingStat();
	});
	txtStatYear.addEvent('blur', function() {
		doClockingStat();
	});
	$('btnDateStatBack').addEvent('click', function() {
		var month = parseB10(txtStatMonth.get('value'));
		if (month == 0) {
			txtStatMonth.set('value', 11);
			txtStatYear.set('value', parseB10(txtStatYear.get('value')) - 1);
		} else
			txtStatMonth.set('value', month - 1);

		doClockingStat();
	});
	$('btnDateStatNext').addEvent('click', function() {
		var month = parseB10(txtStatMonth.get('value'));
		if (month == 11) {
			txtStatMonth.set('value', 0);
			txtStatYear.set('value', parseB10(txtStatYear.get('value')) + 1);
		} else
			txtStatMonth.set('value', month + 1);

		doClockingStat();
	});

	var txtStatUsers = new gx.groupion.Select('txtStatUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			doClockingStat();
		},
		'onNoSelect' : function(sel) {
			doClockingStat();
		}
	});
	if (force_user) {
		txtStatUsers.set(getUser(force_user));
		txtStatUsers.disable();
	}
	var btnStatReset = $('btnStatReset');
	if (btnStatReset) {
		btnStatReset.addEvent('click', function() {
			txtStatUsers.set();
		});
	}

	/* + + + + + + + + + + UI: Account + + + + + + + + + + */

	var txtAccountYear = $('txtDateAccountYear');
	if (txtAccountYear) {
		var today = new Date();
		txtAccountYear.set('value', today.getFullYear());
		txtAccountYear.addEvent('change', function() {
			updateAccount();
		});
	}

	var btnDateAccountBack = $('btnDateAccountBack');
	if (btnDateAccountBack) {
		btnDateAccountBack.addEvent('click', function() {
			var year = parseB10(txtAccountYear.get('value'));
			txtAccountYear.set('value', year - 1);
			updateAccount();
		});
	}

	var btnDateAccountNext = $('btnDateAccountNext');
	if (btnDateAccountNext) {
		btnDateAccountNext.addEvent('click', function() {
			var year = parseB10(txtAccountYear.get('value'));
			txtAccountYear.set('value', year + 1);
			updateAccount();
		});
	}

	var selAccountType = $('selAccountType');
	if (selAccountType) {
		selAccountType.addEvent('change', updateAccount);
		selAccountType.addEvent('change', function() {
			var type = selAccountType.getSelected().get('value')[0];
			if (type == '3') {
				edtAccountVacation.show('block');
//				tbxAccountValue.hide();
				tbxAccountValue.disable();
				
			}
			else {
				edtAccountVacation.hide();
				tbxAccountValue.enable();
			}
		});
	}
	var btnAddManualTransaction = $('txtAccountNewSubmit');
	if (btnAddManualTransaction) {
		btnAddManualTransaction.set('disabled', 'disabled');
		btnAddManualTransaction.addEvent('click', function() {
			var user = txtAccountUsers.getSelected();
			var date = accountNewDate.get('seconds');
			var comment = $('txtAccountNewComment').get('value');
			var value = parseDec($('txtAccountNewValue').get('value'));
			sendForm({
				'api' : 'transaction',
				'do' : 'add.manual',
				'date' : date,
				'userid' : user.id,
				'comment' : comment,
				'value' : value,
				'type' : selAccountType.getSelected().get('value')[0]
			}, function(res) {
				updateAccount();
			});
		});
	}

	var SaldoCalculator = new Class({
		initialize : function(type) {
			this.type = type;
			this.credit = 0;
			this.debit = 0;
			this.creditField = $('accountCredit');
			this.debitField = $('accountDebit');
			this.reset();
		},
		add : function(credit, debit) {
			if (credit) {
				this.credit += parseB10(credit);
			}
			if (debit) {
				this.debit += parseB10(debit);
			}
			this.update();
		},
		reset : function() {
			this.credit = 0;
			this.debit = 0;
			this.update();
		},
		update : function() {
			var creditString = null;
			var debitString = null;
			if (this.type == '3') {
				creditString = roundDec(this.credit);
				debitString = roundDec(this.debit);
			} else {
				creditString = formatTime(this.credit);
				debitString = formatTime(this.debit);
			}
			if (this.creditField) {
				this.creditField.set('html', creditString);
			}
			if (this.debitField) {
				this.debitField.set('html', debitString);
			}
		}
	});
	var saldo = null;
	var txtAccountUsers = new gx.groupion.Select('txtAccountUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Benutzer auswählen)'
			},
			'noSelection' : '(Select User)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			btnAddManualTransaction.erase('disabled');
			updateAccount();
		},
		'onNoSelect' : function(sel) {
			btnAddManualTransaction.set('disabled', 'disabled');
		}
	});

	if (force_user) {
		var user = getUser(force_user);
		try {
			txtAccountUsers.set(user);
		} catch (e) {
			alert(e);
		}
		txtAccountUsers.disable();
		$('accountAddBar').hide();
	}

	var tabAccount = new gx.groupion.Table('tabAccount', {
		'cols' : [ {
			'label' : 'Tag',
			'id' : 'accountDate',
			'filter' : 'desc'
		}, {
			'label' : 'Kommentar',
			'id' : 'accountComment'
		}, {
			'label' : 'Zu',
			'id' : 'accountIn'
		}, {
			'label' : 'Ab',
			'id' : 'accountOut'
		} ],
		'onComplete' : function() {
		},
		'structure' : function(row) {
			var value = null;
			var selectedType = selAccountType.getSelected().get('value')[0];
			switch (selectedType) {
			case "0":
				value = row.time;
				break;
			case "1":
				value = row.flexitime;
				break;
			case "2":
				value = row.overtime;
				break;
			case "3":
				value = -row.vacation;
				break;
			default:
				throw "Unknown account type: " + selectedType;
			}
			var debit = null;
			var credit = null;
			if (value < 0) {
				debit = -value;
			} else {
				credit = value;
			}
			saldo.add(debit, credit);
			var date = new Date(row.date * 1000);
			var day = date.getDate();
			var month = date.getMonth() + 1;
			var year = date.getYear() + 1900;
			var dateLabel = day + '.' + month + '.' + year;

			var creditString = null;
			var debitString = null;
			if (selectedType == '3') {
				creditString = roundDec(credit);
				debitString = roundDec(debit);
			} else {
				creditString = formatTime(credit);
				debitString = formatTime(debit);
			}

			return [ dateLabel, row.comment, creditString, debitString ];
		}
	});

	var accountNewDate = new gx.groupion.Datebox('txtAccountNewDate', {
		'format' : [ 'd', '.', 'M', '.', 'y' ]
	});
	accountNewDate.set(new Date().getTime());

	function updateAccount() {
		if (txtAccountUsers == null) {
			return;
		}
		var user = txtAccountUsers.getSelected();
		if (user == null) {
			return;
		}
		accountFilterColumn = selAccountType.getSelected().get('value')[0];
		saldo = new SaldoCalculator(accountFilterColumn);
		var year = txtAccountYear.get('value');
		sendForm({
			'api' : 'transaction',
			'do' : 'list',
			'year' : year,
			'userid' : user.id
		}, function(res) {
			tabAccount.setData(res.result);
		}, null, 'array');
	}

	/* + + + + + + + + + + UI: Tabs + + + + + + + + + + */

	var updateTab = function(name) {
		switch (name) {
		case 'tab0':
			doClockingList();
			break;
		case 'tab1':
			doTransList();
			break;
		case 'tab2':
			doVacationList();
			break;
		case 'tab3':
			doClockingStat();
			break;
		case 'tab4':
			updateAccount();
			break;
		}
	};

	var tabs = new gx.groupion.Tabbox('tabs', {
		'frames' : [ {
			'name' : 'tab0',
			'title' : 'Arbeitszeiten',
			'content' : $('tab0')
		}, {
			'name' : 'tab1',
			'title' : 'Transaktionen',
			'content' : $('tab1')
		}, {
			'name' : 'tab2',
			'title' : 'Abwesenheit',
			'content' : $('tab2')
		}, {
			'name' : 'tab3',
			'title' : 'Statistik',
			'content' : $('tab3')
		}, {
			'name' : 'tab4',
			'title' : 'Konto',
			'content' : $('tab4')
		} ],
		'onChange' : function(name) {
			this.current = name;
			updateTab(name);
		}
	});

	// var tabKonto = $('tab4');
	// if (tabKonto) {
	// tabKonto.hide();
	// }

	tabs.updateCurrent = function() {
		updateTab(tabs.current);
	};

	var tabClockingDiv = $('tabClocking');
	var tabTransDiv = $('tabTrans');
	var tabVacationDiv = $('tabVacation');
	var tabStatDiv = $('tabStat');

	/* Backup */
	var btnClockingBackup = $('btnClockingBackup');
	if (btnClockingBackup) {
		btnClockingBackup.addEvent('click', function() {
			window.location = urlBase + '?_service=' + access._service + '&_accesskey=' + access._accesskey + '&do=backup';
		});
	}

	/* Automatically adapt window height */
	var h = 230;
	var l = 200;
	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if (ht < l)
			ht = l;
		tabClockingDiv.setStyle('height', ht + 'px');
		tabTransDiv.setStyle('height', ht + 'px');
		tabVacationDiv.setStyle('height', ht + 'px');
		tabStatDiv.setStyle('height', ht + 'px');

		if (tabs._active == 'tab3')
			statDrawChart();
	}
	window.addEvent('resize', function() {
		updateHeight();
	});
	updateHeight();

	/* Limit users */
	if (force_user) {
		viewEdit.username.set(getUser(force_user));
		viewEdit.username.disable();
	}
});