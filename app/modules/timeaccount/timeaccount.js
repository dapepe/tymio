initView(function (gui) {
	selFilter = $('selFilter');

	var balanceContainer = $('time_account_balance')
		.addEvent('click:relay(.time_account_balance)', function (event) {
			event.stop();

			var id = this.getProperty('data-type-id');

			if ( this.hasClass('selected') ) {
				this.removeClass('selected');
				delete ui.selectedTypes[id];
			} else {
				this.addClass('selected');
				ui.selectedTypes[id] = true;
			}
			ui.list();
		});

	/* GUI Bindings
	----------------------------------------------------------- */
	var ui = {
		_current: null,
		_ready  : false,

		selectedTypes: {},

		showBalances: function (types, typesYear, typesCumulated, typesSelection, typesTotal) {
			balanceContainer.empty();

			typesCumulated = typesCumulated.reindex('Id'); // Start date's start of year to end date
			typesYear      = typesYear.reindex('Id');      // Start date's start of year to end date's end of year
			typesSelection = typesSelection.reindex('Id'); // From very first record to end date
			typesTotal     = typesTotal.reindex('Id');     // From very first record to very last one

			for (var i = 0; i < types.length; i++) {
				var id        = types[i].Id;
				var cumulated = typesCumulated[id];
				var yearTotal = typesYear[id];
				var selected  = typesSelection[id];
				var total     = typesTotal[id];

				new Element('div', {
					'class'       : 'time_account_balance'+( this.selectedTypes[id] ? ' selected' : '' ),
					'data-type-id': id
				}).adopt(
					new Element('label', {
						'html':
							'<div class="cal_type_badge" style="background:#'+types[i].Color+';"></div>'+
							String( types[i].Label || types[i].Identifier ).htmlSpecialChars()
						}),
					new Element('div', { 'class': 'a_r', 'text': Number(types[i].Balance).formatDurationFrom(types[i].Unit, types[i].DisplayUnit)+' '+( _('datetime.unit.format.'+types[i].DisplayUnit) || types[i].DisplayUnit ) }),
					new Element('div', { 'class': 'bold a_r', 'text': (
						cumulated
						? Number(cumulated.Balance).formatDurationFrom(cumulated.Unit, cumulated.DisplayUnit)+' '+( _('datetime.unit.format.'+cumulated.DisplayUnit) || cumulated.DisplayUnit )
						: '-'
					) }),
					new Element('div', { 'class': 'bold a_r', 'text': (
						yearTotal
						? Number(yearTotal.Balance).formatDurationFrom(yearTotal.Unit, yearTotal.DisplayUnit)+' '+( _('datetime.unit.format.'+yearTotal.DisplayUnit) || yearTotal.DisplayUnit )
						: '-'
					) }),
					new Element('div', { 'class': 'bold a_r', 'text': (
						selected
						? Number(selected.Balance).formatDurationFrom(selected.Unit, selected.DisplayUnit)+' '+( _('datetime.unit.format.'+selected.DisplayUnit) || selected.DisplayUnit )
						: '-'
					) }),
					new Element('div', { 'class': 'bold a_r', 'text': (
						total
						? Number(total.Balance).formatDurationFrom(total.Unit, total.DisplayUnit)+' '+( _('datetime.unit.format.'+total.DisplayUnit) || total.DisplayUnit )
						: '-'
					) })
				)
					.inject(balanceContainer);
			}
		},

		getYearStart: function (timestamp) {
			var date = new Date(timestamp * 1000);
			date.setMonth(0);
			date.setDate(1);
			return Math.floor(date.getTime() / 1000);
		},

		getYearEnd: function (timestamp) {
			var date = new Date(timestamp * 1000);
			date.setMonth(11);
			date.setDate(31);
			return Math.floor(date.getTime() / 1000);
		},

		list: function () {
			if ( !this._ready )
				return;

			var dateRangeFilter = {
				'start': monthPicker.getStart(),
				'end'  : monthPicker.getEnd()
			};

			var showDeleted = chkShowDel.get();

			var filter = Object.merge({
				'showdeleted': showDeleted,
				'user'       : selUser.getId()
			}, tabTimeAccount.getFilter(), dateRangeFilter);

			var user = selUser.getSelected();

			(function (bookings, transactions, types, holidays) {
				var transactionsById = transactions.reindex('Id');
				var typesById        = types.reindex('Id');

				tabTimeAccount.setData(bookings.each(function (booking, index) {
					booking.Transaction = ( transactionsById[booking.TransactionId] || null );
					booking.Type        = ( typesById[booking.BookingTypeId] || null );
					booking.IsHoliday   = (
						user && booking.Transaction
						? HolidayManager.isHoliday(booking.Transaction, holidays[user.DomainId])
						: false
					);
				}));

				// Show balances
				var yearStart = this.getYearStart(dateRangeFilter.start);
				this.showBalances.future().bind(this)(
					types,
					TransactionAPI.types.toApiPromise(TransactionAPI, [ user.Id, yearStart, this.getYearEnd(dateRangeFilter.end), showDeleted ]),
					TransactionAPI.types.toApiPromise(TransactionAPI, [ user.Id, yearStart, dateRangeFilter.end, showDeleted ]),
					TransactionAPI.types.toApiPromise(TransactionAPI, [ user.Id, null, dateRangeFilter.end, showDeleted ]),
					TransactionAPI.types.toApiPromise(TransactionAPI, [ user.Id, null, null, showDeleted ])
				);
			}).bind(this).future()(
				TransactionAPI.listBookings.toApiPromise(TransactionAPI, [ Object.append({ 'types': Object.keys(this.selectedTypes) }, filter) ]),
				TransactionAPI.list.toApiPromise(TransactionAPI, [ filter ]),
				TransactionAPI.types.toApiPromise(TransactionAPI, [ user.Id, dateRangeFilter.start, dateRangeFilter.end, showDeleted ]),
				HolidayManager.get(Object.append(
					( user === null ? {} : { 'domain': user.DomainId } ),
					dateRangeFilter
				), true)
			);
		},

		open: function (booking) {
			ui._current = booking;
		}
	};

	/* Details Popup
	----------------------------------------------------------- */
/*
	var transactionDetails = new TransactionWizard(gui)
		.addEvent('save', ui.list.bind(ui));
*/
	/* Time account table
	----------------------------------------------------------- */

	var tabTimeAccount = new gx.bootstrap.Table('tab_time_account', {
		'cols'       : [
			{ 'label': 'Von', 'id' : 'Start', 'width': '200px', 'properties': { 'class': 'a_r' }, 'filter' : 'desc' },
			{ 'label': 'Bis', 'id' : 'End', 'width': '200px' },
			{ 'label': 'Buchung', 'id': 'Label' },
			{ 'label': 'Zeit', 'id' : 'Time', 'width': '80px', 'properties': { 'class': 'a_r' }, 'filterable': false },
			{ 'label': 'Typ', 'id' : 'Type' }
		],
		'onFilter'   : function (col) {
			ui.list();
		},
		'onStart'    : function () {
		},
		'onComplete' : function () {
		},
		'onClick'    : function (booking, event) {
		},
		'onDblclick' : function (booking, event) {
			event.stop();
			deselect();
			ui.open(booking);
		},
		'structure'  : function (booking) {
			var start   = new Date(booking.Transaction.Start * 1000);
			var end     = new Date(booking.Transaction.End * 1000);

			var sameDay = ( start.format('%Y-%m-%d') === end.format('%Y-%m-%d') );

			var endFormat;
			if ( sameDay )
				endDateText = '';
			else
				endDateText = end.format(_('date.format.default'));

			var caption = new Element('span', { 'text': booking.Label });

			var cssDeleted;
			if ( booking.Transaction.Deleted ) {
				cssDeleted = 'deleted';
				caption = new Element('div').adopt(Factory.DeletedBadge(), caption);
			} else {
				cssDeleted = '';
			}

			return {
				'row': [
					'<div class="'+cssDeleted+' a_r">'+_('date.day_names.'+start.getDay())+' '+start.format(_('date.format.default'))+'</div>',
					'<span class="'+cssDeleted+'">'+endDateText+'</span>',
					caption,
					'<div class="'+cssDeleted+' a_r">'+Number(booking.Value).formatDurationFrom(booking.Type.Unit, booking.Type.DisplayUnit)+'</div>',
					'<span class="'+cssDeleted+'"><div class="cal_type_badge" style="background:#'+booking.Type.Color+';"></div>'+( _('booking.type.'+booking.Type.Identifier) || booking.Type.Label )+'</span>'
				],
				'properties': {
					'id'   : 'booking_'+booking.Id,
					'class':
						( booking.IsHoliday ? ' holiday' : '' )
				}
			};
		}
	});

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
			ui.list();
		}
	})
		.set(AUTHENTICATED_USER);

	/* Options menu
	----------------------------------------------------------- */

	var btnOptions = new gx.bootstrap.MenuButton('btnOptions', {
		'label'      : _('field.options'),
		'style'      : 'primary',
		'orientation': 'right'
	});
	var optRefresh = btnOptions.add('Aktualisieren', 'refresh').addEvent('click', ui.list.bind(ui));

	/* Automatically adapt window height */
	var h = 300;
	var l = 200;

	function updateHeight() {
		var s = window.getSize();
		var ht = s.y - h;
		if ( ht < l )
			ht = l;
		tabTimeAccount.setHeight(ht+'px');
	}

	window.addEvent('resize', function () {
		updateHeight();
	});
	updateHeight();

	ui._ready = true;
	ui.list();
});
