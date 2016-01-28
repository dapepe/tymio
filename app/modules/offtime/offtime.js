initView(function (gui) {

	var START_OF_WEEK = 1; // 0 = Sunday, 1 = Monday etc.

	var today        = new Date();
	var selectedDate = new Date();
	var calendarData = {};

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		inSave: false,

		list: function () {
			today = new Date();

			calendar.setDate(selectedDate.getMonth() + 1, selectedDate.getFullYear());

			var calStart = calendar.getStart();
			var calEnd   = calendar.getEnd();

			var filter = {
				'showdeleted' : false, //chkShowDel.get();
				'showbooked'  : ClockingAPI.SHOW_BOOKED_ALL, //selFilter.get('value');
				'user'        : selUser.getId(),
				'start'       : calStart,
				'end'         : calEnd,
				'wholedayonly': true
			};

			(function (clockings, users, types) {
				mergeData(
					clockings,
					{
						'Users': users.reindex('Id'),
						'Types': types.reindex('Id')
					},
					function (clocking, related) {
						clocking.User = ( related.Users[clocking.UserId] || null );
						clocking.Type = ( related.Types[clocking.TypeId] || null );
					}
				);
				clockingListHandler(clockings);
			}).future()(
				ClockingAPI.list.toApiPromise(ClockingAPI, [ filter ]),
				UserAPI.list.toApiPromise(UserAPI, [ null ]),
				ClockingAPI.types.toApiPromise(ClockingAPI, [ false ])
			);

			//ClockingAPI.list(filter, clockingListHandler);

			HolidayAPI.list({
				'domain'     : ( selUser.getSelected() || AUTHENTICATED_USER ).DomainId,
				'start'      : calStart,
				'end'        : calEnd
			}, function (data) {
				if ( (typeof(data) !== 'object') || (typeOf(data.result) !== 'array') )
					return;

				var holidays = data.result;

				for (var i = 0; i < holidays.length; i++) {
					var dateText   = new Date().parse(holidays[i].Date * 1000).format('%Y-%m-%d');
					var calCell    = $('cal_cell_'+dateText);
					var calElement = $('cal_item_'+dateText);
					if ( !calCell || !calElement )
						continue;

					calCell.addClass('holiday');
					new Element('div', { 'class': 'cal_item_holiday', 'text': holidays[i].Name })
						.inject(calElement, 'top');
				}
			});

			resizeHandler();
		},
		update: function () {
			viewDetails.popup.show();
		},
		open: function (clocking) {
			viewDetails.selUser.set(clocking.User);
			viewDetails.clockingId.value = clocking.Id;
			viewDetails.clockingType.set(clocking.Type);

			viewDetails.dbxStart.set(clocking.Start * 1000);
			viewDetails.dbxEnd.set(clocking.End * 1000);

			$('offtime_edit_comment').value = clocking.Comment;

			if ( clocking.Booked )
				$('offtime_save').setProperty('disabled', 'disabled');
			else
				$('offtime_save').erase('disabled');

			viewDetails.popup.show();
		},
		add: function (date) {
			today = new Date().parse( date instanceof Date ? date.format('%Y-%m-%d') : 'today' );

			viewDetails.selUser.set( selUser.getSelected() || AUTHENTICATED_USER );
			viewDetails.clockingId.value   = '';
			viewDetails.clockingType.value = '';

			viewDetails.dbxStart.set(today);
			viewDetails.dbxEnd.set(today);

			$('offtime_edit_comment').value = '';

			$('offtime_save').erase('disabled');

			viewDetails.popup.show();
		},
		save: function () {
			if ( this.inSave )
				return;

			this.inSave = true;

			ClockingAPI.update(viewDetails.clockingId.value, {
				'UserId'   : viewDetails.selUser.getId(),
				'TypeId'   : viewDetails.clockingType.getId(),
				'Start'    : viewDetails.dbxStart.get('%s'),
				'End'      : viewDetails.dbxEnd.get('%s'),
				'Breaktime': 0,
				'Comment'  : $('offtime_edit_comment').value
			}, function (data) {
				ui.inSave = false;

				// "data.result" can be true or an ID
				if ( data && (typeof(data) === 'object') && data.result ) {
					viewDetails.popup.hide();
					ui.list();
				}
			}, true);
		},
		remove: function () {

		}
	};

	function getDaysFromInterval(start, end) {
		if ( !(start instanceof Date) )
			start = new Date(start * 1000);

		if ( !(end instanceof Date) )
			end = new Date(end * 1000);

		var day    = new Date().parse(start.format('%Y-%m-%d'));
		var endDay = new Date().parse(end.format('%Y-%m-%d'));

		var days   = [];
		while ( day <= endDay ) {
			days.push(new Date(day));
			day.increment('day');
		}

		return days;
	}

	function createClockingItem(clocking, day) {
		var dateText = day.format('%Y-%m-%d');
		var calItem  = $('cal_item_'+dateText);
		if ( !calItem )
			return;

		var caption      = _('offtime.clocking', {
			'type'         : ( clocking.Type.Label || clocking.Type.Identifier ),
			'user'         : clocking.User.Name,
			'user_fullname': getFullName(clocking.User)
		});

		calItem.adopt(
			new Element('a', {
				'class':
					'cal_clocking'+
					( new Date(clocking.Start * 1000).format('%Y-%m-%d') === dateText ? ' cal_start' : '' )+
					( new Date(clocking.End * 1000).format('%Y-%m-%d') === dateText ? ' cal_end' : '' )+
					( clocking.Booked ? ' cal_booked' : '' )+
					( clocking.ApprovalStatus == ClockingAPI.APPROVAL_STATUS_DENIED ? ' cal_denied' : '' ),
				'title': caption,
				'html' :
					'<div class="cal_type_badge" style="background:#'+clocking.Type.Color+';"></div>'+
					( clocking.Booked ? '<div class="icon-ok-sign" title="'+_('clocking.booked.caption').htmlSpecialChars()+'"></div>' : '' )+
					( clocking.ApprovalStatus == ClockingAPI.APPROVAL_STATUS_DENIED ? '<div class="icon-ban-circle" title="'+_('clocking.status.denied').htmlSpecialChars()+'"></div>' : '' )+
					caption
			}).addEvent('click', function (event) {
				event.stop();
				ui.open(clocking);
			})
		);
	}

	function clockingListHandler(clockings) {
		for (var i = 0; i < clockings.length; i++) {
			var clocking = clockings[i];

			var days = getDaysFromInterval(clocking.Start, clocking.End);
			for (var d = 0; d < days.length; d++)
				createClockingItem(clocking, days[d]);
		}
	}

	/* Details Popup
	----------------------------------------------------------- */

	var viewDetails = {};

	viewDetails.clockingId   = $('offtime_edit_id');
	viewDetails.clockingType = new gx.bootstrap.Select('offtime_edit_type', {
		'label'           : 'Typ',
		'icon'            : 'tag',
		'msg'             : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse'  : gui.initResult,
		'default'         : null,
		'requestData'     : {
			'api'         : 'clocking',
			'do'          : 'types',
			'wholedayonly': 1
		},
		'requestParam'    : 'search',
		'listFormat'      : function (item) {
			return ( _('clocking.type.'+item.Identifier) || item.Label || item.Identifier );
		},
		'formatID'        : function (item) {
			return item ? item.Id : false;
		}
	});

	viewDetails.selUser = new gx.bootstrap.Select('offtime_edit_user', {
		'icon'          : 'user',
		'msg'           : {'noSelection' : '--- '+_('field.pleaseselect')+' ---'},
		'decodeResponse': gui.initResult,
		'default'       : null,
		'requestData'   : {
			'api'       : 'user',
			'do'        : 'list'
		},
		'requestParam'  : 'search',
		'listFormat'    : getFullName,
		'formatID'      : function (item) {
			return item ? item.Id : false;
		},
		'onSelect'      : function () {
		}
	});

	viewDetails.dbxStart = new gx.bootstrap.DatePicker('offtime_edit_start', {
		'label'        : 'Start',
		'icon'         : 'play',
		'format'       : '%a %d.%m.%Y',
		'timePicker'   : false,
		'return_format': null
	});
	viewDetails.dbxEnd = new gx.bootstrap.DatePicker('offtime_edit_end', {
		'label'        : 'Ende',
		'icon'         : 'stop',
		'format'       : '%a %d.%m.%Y',
		'timePicker'   : false,
		'return_format': null
	});

	viewDetails.popup = new gx.bootstrap.Popup({
		'width'   : 500,
		'content' : $('offtime_popup_details'),
		'title'   : 'Details',
		'footer'  : __({'children': {
			'btnClose': {'tag': 'input', 'type': 'button', 'class': 'btn', 'value': 'Close', 'onClick': function () {
				viewDetails.popup.hide();
			}},
			'btnOk': {'tag': 'input', 'type': 'button', 'class': 'btn btn-primary m2_l', 'value': 'Save', 'id': 'offtime_save', 'onClick': function () {
				ui.save();
			}}
		}}),
		'closable': true
	});

	function resizeHandler() {
		var maxHeight  = window.getSize().y - parseInt($(document.body).getStyle('margin-top')) - $calendar.getElement('tbody').getPosition(document.body).y - 30;
		var rowCount   = 6;
		var heightText = String(maxHeight / rowCount)+'px';
		$calendar.getElements('td,.cal_contents').setStyles({ 'height': heightText, 'max-height': heightText });
	}

	function rotateArray(items, offset) {
		offset %= items.length;
		return items.slice(offset, items.length)
			.append(items.slice(0, offset));
	}

	var calendar = new MonthCalendar({
		'startOfWeek': START_OF_WEEK,
		'head'       : rotateArray(Locale.get('Date.days_abbr'), START_OF_WEEK),
		'table'      : {
			'class'  : 'table table-striped table-bordered'
		},
		'format'     : function (date) {
			var dateText = date.format('%Y-%m-%d');
			return {
				'text': new Element('div', { 'class': 'cal_contents' }).adopt(
					new Element('div', { 'class': 'cal_date', 'text': date.getDate() }),
					new Element('div', { 'id': 'cal_item_'+dateText, 'class': 'cal_items' })
				),
				'properties': {
					'id'   : 'cal_cell_'+dateText,
					'class': ( dateText === today.format('%Y-%m-%d') ? 'today' : '' )
				}
			};
		},
		'onClick'    : function (date, event, element) {
			ui.add(date);
		}
	});
	var $calendar = $(calendar);
	var calendarContainer = $('offtime_calendar')
		.adopt($calendar);

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

	// Must be declared after "selUser" because "onSelect" is fired immediately
	// and depends on "selUser".
	var monthPicker = new gx.bootstrap.MonthPicker('mprRange', {
		'onSelect': function (date) {
			selectedDate = date;
			ui.list();
		}
	});

	$('btnNewOfftime').addEvent('click', function (event) {
		event.stop();
		ui.add();
	});

	window.addEvent('resize', resizeHandler);
	resizeHandler();
});