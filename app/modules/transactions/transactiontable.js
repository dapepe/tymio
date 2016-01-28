var TransactionTable = null;

initView(function (gui) {

	/**
	 * Maps clocking type IDs to their objects.
	 */
	var clockingTypesById = {};

	/**
	 * Maps booking type IDs to their objects.
	 */
	var bookingTypesById = {};

	TransactionTable = new Class({

		Implements: [ Events, Options ],

		options: {
			'editable'    : false,
			'showUser'    : true,

			// This dummy row is used to make "gx.com.Table.adoptSizeToHead()" work
			'measuringRow': [
				'50px',
				'180px',
				'120px',
				'250px',
				'200px',
				''
			]
		},

		expandedTransactionCount: 0,
		transactions            : [],
		selectedTransactions    : [],
		selectedTransactionCount: 0,

		table                   : undefined,

		expandAllControl        : undefined,
		selectAllCheckbox       : undefined,
		inPlaceEditor           : undefined,

		initialize: function (element, options) {
			this.setOptions(options);
			this.options = Object.append({
				'onFilter'       : this.filter.bind(this),
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'onClick'        : this.click.bind(this),
				'onDblclick'     : this.dblClick.bind(this),
				'structure'      : this.structure.bind(this)
			}, this.options);

			this.expandAllControl = new Element('div', { 'class': 'expand expanded m_r' })
				.addEvent('click', function (event) {
					event.stop();
					if ( this.expandedTransactionCount > this.transactions.length / 2 )
						this.collapseAll();
					else
						this.expandAll();
				}.bind(this));

			this.selectAllCheckbox = new Element('input', { 'type': 'checkbox' })
				.addEvent('click', this.selectAllHandler.bind(this));

			var widgets = new Element('div').adopt(
				this.expandAllControl,
				this.selectAllCheckbox
			);

			this.options.cols = [
				{ 'label': widgets, 'id': 'check', 'width': this.options.measuringRow[0], 'filterable': false, 'clickable': false },
				{ 'label': 'Von', 'id': 'Start', 'filter': 'desc', 'width': this.options.measuringRow[1], 'properties': { 'class': 'a_r' } },
				{ 'label': 'Bis', 'id': 'End', 'width': this.options.measuringRow[2], 'properties': { 'class': 'a_l' } },
				{ 'label': 'Benutzer', 'id': 'User', 'width': this.options.measuringRow[3], 'properties': { 'class': 'transaction_table_user_column' } },
				{ 'label': 'Kommentar', 'id': 'Comment', 'width': this.options.measuringRow[4], 'filterable': false },
				{ 'label': 'Buchungen', 'id': 'BookingTypes', 'width': this.options.measuringRow[5], 'filterable': false }
			];

			this.table = new gx.bootstrap.Table(element, this.options);
			this.showUserColumn(this.options.showUser);

			if ( this.options.editable ) {
				var inPlaceHandler = this.inPlaceChangeHandler.bind(this);
				this.inPlaceEditor = new gx.com.InPlaceEditor()
					.attach('.inplace', $(this.table))
					.addEvent('change', inPlaceHandler);
			}
		},

		showUserColumn: function (visible) {
			this.options.showUser = visible;
			$(this.table)[ visible ? 'removeClass' : 'addClass' ]('transaction_table_user_hidden');
			return this;
		},

		/**
		 * Selects or deselects all transactions but does not update the select-all checkbox.
		 *
		 * @param {Array} transactions
		 * @returns Returns this instance for method chaining.
		 * @type TransactionTable
		 * @see select()
		 */
		doSelect: function (transactions) {
			if ( transactions == null ) {
				// Deselect all

				for (var id in this.selectedTransactions) {
					if ( !this.selectedTransactions.hasOwnProperty(id) )
						continue;

					var transaction = this.selectedTransactions[id];

					if ( transaction._checkbox )
						transaction._checkbox.checked = false;

					var $row = transaction.tr;
					if ( $row )
						$row.removeClass('selected');
				}

				this.selectedTransactions      = {};
				this.selectedTransactionCount  = 0;

				this.fireEvent('select', [ this.selectedTransactions, null, this ]);

				return this;
			}

			// Select
			for (var i = 0; i < transactions.length; i++) {
				var transaction = transactions[i];
				this.selectedTransactions[transaction.Id] = transaction;

				if ( transaction._checkbox )
					transaction._checkbox.checked = true;

				var $row = transaction.tr;
				if ( $row )
					$row.addClass('selected');
			}

			this.selectedTransactionCount += transactions.length;

			this.fireEvent('select', [ this.selectedTransactions, null, this ]);

			return this;
		},

		/**
		 * Selects or deselects all transactions and updates the select-all checkbox.
		 *
		 * @param {Array} transactions
		 * @returns Returns this instance for method chaining.
		 * @type TransactionTable
		 * @see doSelect()
		 */
		select: function (transactions) {
			this.doSelect(transactions);
			this.selectAllCheckbox.checked = ( this.selectedTransactionCount >= this.transactions.length / 2 );
			return this;
		},

		selectAllHandler: function (event) {
			event.stopPropagation();

			// Deselect all
			this.doSelect(null);

			// Select all
			if ( this.selectAllCheckbox.checked )
				this.doSelect(this.transactions);
		},

		inPlaceChangeHandler: function (text, element, inPlaceEditor, event) {
			var data = element.retrieve('tymio-inplace');
			if ( !data || !data.type )
				return;

			switch ( data.type ) {
				case 'booking':
					if ( data.key === 'Value' ) {
						var value = String(text).toBookingValue(data.displayUnit);
						if ( typeof(value) === 'undefined' ) {
							element.set('text', Number(data.item[data.key]).formatDurationFrom(data.unit, data.displayUnit));
							gui.msg.addMessage('Invalid duration "'+text+'".', 'error', true, false, false);
							//inPlaceEditor.focus(element, event);
							//element.selectAll();
							return;
						} else {
							var duration = Math.round(value.convertDuration(data.displayUnit, data.unit));
							text = duration;
							element.set('text', duration.formatDurationFrom(data.unit, data.displayUnit));
						}
					}

					data.item[data.key] = String(text).trim(); // Do not allow leading / trailing whitespace
					break;
			}
		},

		toElement: function () {
			return $(this.table);
		},

		filter: function (column) {
			this.fireEvent('filter', [ column, this ]);
		},

		click: function (transaction, event) {
			if ( event.target.hasClass('inplace') ||
			     event.target.getParent('.stop-propagation') )
				return;

			if ( (transaction !== TransactionTable.TAB_TRANSACTION_MEASURING_ROW) &&
				 (transaction._checkbox instanceof Element) ) {
				transaction._checkbox.checked = !transaction._checkbox.checked;
				transaction._checkbox.fireEvent('click', [ event ]);
			}

			this.fireEvent('click', [ transaction, event, this ]);
		},

		dblClick: function (transaction, event) {
			event.stop();
			deselect();
			if ( transaction !== TransactionTable.TAB_TRANSACTION_MEASURING_ROW )
				this.fireEvent('dblclick', [ transaction, event, this ]);
		},

		stopPropagation: function (event) {
			event.stopPropagation();
		},

		structure: function (transaction) {
			if ( transaction === TransactionTable.TAB_TRANSACTION_MEASURING_ROW ) {
				return {
					'row'        : [
						{ 'style': 'width:'+this.options.measuringRow[0]+';' },
						{ 'style': 'width:'+this.options.measuringRow[1]+';' },
						{ 'style': 'width:'+this.options.measuringRow[2]+';' },
						{ 'style': 'width:'+this.options.measuringRow[3]+';', 'class': 'transaction_table_user_column' },
						{ 'style': 'width:'+this.options.measuringRow[4]+';' },
						{ 'style': 'width:'+this.options.measuringRow[5]+';' }
					],
					'properties' : { 'style': 'display:none;' }
				};
			}

			var transactionDetailsRow = new Element('tr', { 'class': 'transaction_details' }).adopt(new Element('td', { 'colspan': 6 }).adopt(
				new Element('table', { 'class': 'fullw' }).adopt(new Element('tbody').adopt(new Element('tr').adopt(
					new Element('td', { 'width': '55%', 'style': 'border:0; padding:0;' }).adopt(this.createClockingTable(transaction)),
					new Element('td', { 'style': 'width:15px; border:0; vertical-align:middle;' }).adopt(new Element('div', { 'style': 'border: 30px solid transparent; border-left: 15px solid #80a0ff; border-right: 0;' })),
					new Element('td', { 'style': 'border:0; padding:0;' }).adopt(this.createBookingTable(transaction))
				)
			))));

			var me = this;

			var expandControl = new Element('div', { 'class': 'transaction_expand expand expanded m_r' })
				.addEvents({
					'click'   : function (event) {
						event.stop();
						this.toggleClass('expanded');
						transactionDetailsRow.toggle();
						if ( this.hasClass('expanded') ) {
							me.expandedTransactionCount++;
							me.fireEvent('expand', [ transaction, me ]);
						} else {
							me.expandedTransactionCount--;
							me.fireEvent('collapse', [ transaction, me ]);
						}
						me.updateExpansionControl.apply(me);
					},
					'dblclick': function (event) {
						event.stop();
					}
				});

			transaction._checkbox = new Element('input', { 'type': 'checkbox', 'value': transaction.Id, 'class': 'transaction_checkbox' })
				.addEvents({
					'click'   : function (event) {
						event.stopPropagation();
						var tr = $('transaction_'+transaction.Id);
						if ( tr )
							tr.toggleClass('selected');

						if ( this.checked ) {
							if ( !me.selectedTransactions[transaction.Id] )
								me.selectedTransactionCount++;
							me.selectedTransactions[transaction.Id] = transaction;
						} else {
							if ( me.selectedTransactions[transaction.Id] )
								me.selectedTransactionCount--;
							delete me.selectedTransactions[transaction.Id];
						}

						me.selectAllCheckbox.checked = ( me.selectedTransactionCount >= me.transactions.length / 2 );
						me.fireEvent('select', [ me.selectedTransactions, event, me ]);
					},
					'dblclick': function (event) {
						event.stopPropagation();
					}
				});

			var cssDeleted = ( transaction.Deleted ? 'deleted' : '' );

			var caption = new Element('a', { 'text': getFullName(transaction.User), 'class': cssDeleted })
				.addEvent('click', function (event) {
					event.stop();
					this.fireEvent('userclick', [ transaction ]);
				}.bind(this));
			if ( transaction.Deleted )
				caption = new Element('div').adopt(Factory.DeletedBadge(), caption);

			var bookingTypeBadges = this.getBookingTypeBadges(transaction);

			var now = new Date();

			var startDateElement;
			var start = ( transaction.Start == null ? null : new Date(transaction.Start * 1000) );
			if ( !start || !start.isValid() ) {
				start = now;
				transaction.Start = Number(start.format('%s'));
			}

			var endDateElement;
			var end = ( transaction.End == null ? null : new Date(transaction.End * 1000) );
			if ( !end || !end.isValid() ) {
				end = now;
				transaction.End = Number(end.format('%s'));
			}

			if ( (transaction.Id == null) && this.options.editable ) {
				startDateElement = new Element('div')
					.addEvent('click', this.stopPropagation);
				new gx.bootstrap.DatePicker(startDateElement, {
					'date'       : start,
					'timePicker' : false,
					'format'     : '%Y-%m-%d',
					'width'      : '100px'
				})
					.addEvent('select', function (date) {
						transaction.Start = date.format('%s');
					});

				endDateElement = new Element('div')
					.addEvent('click', this.stopPropagation);
				new gx.bootstrap.DatePicker(endDateElement, {
					'date'       : end,
					'timePicker' : false,
					'format'     : '%Y-%m-%d',
					'width'      : '100px'
				})
					.addEvent('select', function (date) {
						transaction.End = date.format('%s');
					});
			} else {
				startDateElement = document.createTextNode( start.isValid() ? _('date.day_names.'+start.getDay())+' '+start.format(_('date.format.default')) : '' );
				endDateElement   = document.createTextNode( end.isValid() ? end.format(_('date.format.default')) : '' );
			}

			return {
				'row': [ {
					'label'  : new Element('table', { 'class': 'transaction_table' }).adopt(new Element('tbody').adopt(
						new Element('tr', { 'class': 'transaction_header' }).adopt(
							new Element('td', { 'style': 'width:'+this.options.measuringRow[0]+';' }).adopt(
								expandControl,
								transaction._checkbox
							),
							new Element('td', { 'class': 'transaction_date '+cssDeleted, 'style': 'width:'+this.options.measuringRow[1]+';' })
								.adopt(startDateElement),
							new Element('td', { 'class': 'transaction_date a_l '+cssDeleted, 'style': 'width:'+this.options.measuringRow[2]+';' })
								.adopt(endDateElement),
							new Element('td', { 'class': 'transaction_employee transaction_table_user_column', 'style': 'width:'+this.options.measuringRow[3]+';' }).adopt(caption),
							new Element('td', { 'style': 'width:'+this.options.measuringRow[4]+';', 'text': transaction.Comment }),
							new Element('td', { 'class': 'transaction_booking_types', 'style': 'width:'+this.options.measuringRow[5]+';' }).adopt(bookingTypeBadges)
						),
						transactionDetailsRow
					)),
					'styles' : {
						'width'     : '100%',
						'padding'   : '4px 0',
						'border-top': 0
					},
					'colspan': 4
				} ],
				'properties' : {
					'id'     : 'transaction_'+transaction.Id
				}
			};
		},

		getFilter: function () {
			return this.table.getFilter();
		},

		expandAll: function () {
			this.expandedTransactionCount = this.transactions.length;
			this.updateExpansionControl();

			var controls = $$('.transaction_expand');
			if ( controls.length > 0 )
				controls.addClass('expanded');

			var details  = $$('.transaction_details');
			if ( details.length > 0 )
				details.show();
		},

		collapseAll: function () {
			this.expandedTransactionCount = 0;
			this.updateExpansionControl();

			var controls = $$('.transaction_expand');
			if ( controls.length > 0 )
				controls.removeClass('expanded');

			var details  = $$('.transaction_details');
			if ( details.length > 0 )
				details.hide();
		},

		updateExpansionControl: function () {
			if ( this.expandedTransactionCount > this.transactions.length / 2 )
				this.expandAllControl.addClass('expanded');
			else
				this.expandAllControl.removeClass('expanded');
		},

		setHeight: function (height) {
			this.table.setHeight(height);
			return this;
		},

		empty: function () {
			this.table.empty();

			this.transactions = [];
			this.select(null);

			return this;
		},

		setData: function (transactions) {
			return (function (bookingTypes, clockingTypes) {
				if ( (typeOf(bookingTypes) !== 'array') ||
				     (typeOf(clockingTypes) !== 'array') )
					return this;

				bookingTypesById = bookingTypes.reindex('Id');
				clockingTypesById = clockingTypes.reindex('Id');

				this.expandedTransactionCount = transactions.length;

				this.transactions             = transactions;

				this.select(null);
				this.table.setData([ TransactionTable.TAB_TRANSACTION_MEASURING_ROW ].append(transactions));

				this.updateExpansionControl();

				return this;
			}).bind(this).future()(
				TransactionAPI.types.toApiPromise(TransactionAPI, [ null, null, null, false ]),
				ClockingAPI.types.toApiPromise(ClockingAPI, [ false ])
			);
		},

		createClockingTable: function (transaction) {
			var element = new Element('div', { 'class': 'p_l' });
			if ( transaction.Clockings.length === 0 ) {
				return element
					.addClass('m_l alert alert-info')
					.set('text', _('transaction.clockings.empty'));
			}

			var table = new gx.bootstrap.Table(element, {
				'simpleTable'    : true,
				'stopPropagation': false,
				'theme'          : {
					'table_head' : 'fullw',
					'table_body' : 'fixed table table-striped table-bordered m_b stop-propagation'
				},
				'cols'           : [
					{ 'label': 'Arbeitszeit', 'id' : 'Date', 'width': '30%' },
					{ 'label': 'Von', 'id' : 'StartTime', 'width': '13%', 'filter' : 'desc', 'properties': { 'class': 'a_r' } },
					{ 'label': 'Bis', 'id' : 'EndTime', 'width': '13%', 'properties': { 'class': 'a_r' } },
					{ 'label': 'Dauer', 'id': 'WorkTime', 'width': '14%', 'properties': { 'class': 'a_r' }, 'filterable': false },
					{ 'label': 'Typ', 'id' : 'Type', 'width': '30%' }
				],
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'structure'      : function (clocking) {
					var isOpen = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );

					clocking.Time  = (clocking.End - clocking.End % 60) - (clocking.Start - clocking.Start % 60);

					var start      = new Date(clocking.Start * 1000);
					var end        = new Date(clocking.End * 1000);

					var sameDay = ( start.format('Ymd') === end.format('Ymd') );

					var dateFormat;
					var duration;
					var durationTooltip;
					if ( clocking.Type.WholeDay ) {
						duration        = (clocking.Time / 86400 + 1).round(); // in days
						dateFormat      = _('date.format.default');
						durationTooltip = '';
					} else {
						duration        = Number(clocking.Time - clocking.Breaktime).formatDuration('minutes');
						breaktimeText   = Number(clocking.Breaktime).formatDuration('minutes');
						dateFormat      = _('time.format.time');
						durationTooltip = ' title="Pause '+breaktimeText.htmlSpecialChars()+'"';
					}

					var clockingType = clockingTypesById[clocking.TypeId];

					var cssDeleted = ( clocking.Deleted ? 'deleted' : '' );

					return {
						'row': [
							'<div class="'+cssDeleted+'">'+_('date.day_names.'+start.getDay())+' '+start.format(_('date.format.default'))+'</div>',
							'<div class="'+cssDeleted+' a_r">'+start.format(dateFormat)+'</div>',
							'<div class="'+cssDeleted+' a_r">'+end.format(dateFormat)+'</div>',
							'<div class="'+cssDeleted+' a_r"'+durationTooltip+'>'+duration+( clocking.Type.WholeDay || (Number(clocking.Breaktime) === 0) ? '' : ' + '+breaktimeText )+'</div>',
							'<div class="'+cssDeleted+'"><div class="cal_type_badge" style="background:#'+clockingType.Color+';"></div>'+( _('clocking.type.'+clocking.Type.Identifier) || clocking.Type.Label )+'</div>'
						],
						'properties': {
							'class'        :
								( isOpen ? 'clocking_open' : '' )+
								( clocking.IsHoliday ? ' holiday' : '' ),
							'data-clocking': clocking.Id
						}
					};
				}
			});

			(function (holidaysByDate) {
				for (var i = 0; i < transaction.Clockings.length; i++)
					transaction.Clockings[i].IsHoliday = HolidayManager.isHoliday(transaction.Clockings[i], holidaysByDate);
				table.setData(transaction.Clockings);
			}).future()(
				HolidayManager.get(Object.append({
					'domain': transaction.User.DomainId
				}, ClockingManager.getRange(transaction.Clockings)))
			);

			return $(table);
		},

		createSelectMenu: function (items, selectedValue) {
			var options = [];
			for (var i = 0; i < items.length; i++) {
				var option = new Element('option', items[i]);
				if ( selectedValue === items[i].value )
					option.setProperty('selected', 'selected');

				options.push(option);
			}
			return new Element('select').adopt(options);
		},

		createBookingRemoveButton: function (table, transaction, booking) {
			return new Element('div', {
				'class': 'booking_toggle btn '+( booking.Deleted ? 'btn-success' : 'btn-danger' ),
				'html' : '<i class="icon '+( booking.Deleted ? 'icon-shopping-cart' : 'icon-trash' )+'"></i>'
			})
				.addEvent('click', function (event) {
					event.stopPropagation();
					booking.Deleted = !booking.Deleted;
					setTimeout(function () {
						table.setData(transaction.Bookings);
					}, 0);
				});
		},

		getBookingTypeCaption: function (bookingType) {
			return ( _('booking.type.'+bookingType.Identifier) || bookingType.Label || bookingType.Identifier );
		},

		createBookingTable: function (transaction) {
			var element = new Element('div');

			var typeHeader;
			if ( this.options.editable ) {
				typeHeader = new Element('div', {
					'class': 'booking_add btn f_r',
					'style': 'margin-right:-4px;',
					'html' : '<i class="icon icon-plus" title="'+_('action.add')+'"></i>'
				});
			} else {
				typeHeader = '';

				if ( transaction.Bookings.length === 0 ) {
					return element
						.addClass('m_l alert alert-info')
						.set('text', _('transaction.bookings.empty'));
				}
			}

			var table = new gx.bootstrap.Table(element, {
				'simpleTable'    : true,
				'stopPropagation': false,
				'theme'          : {
					'table_head' : 'fullw',
					'table_body' : 'fixed table table-striped table-bordered m_b stop-propagation'
				},
				'cols'           : [
					{ 'label': 'Buchung', 'id': 'Identifier', 'filter' : 'desc' },
					{ 'label': 'Dauer', 'id': 'Duration', 'width': '20%', 'properties': { 'class': 'a_r' } },
					{ 'label': 'Typ', 'id': 'Type', 'width': '30%' },
					{ 'label': typeHeader, 'id': 'Action', 'width': '0', 'properties': { 'style': 'border-left:none;' } }
				],
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'structure'      : function (booking, index, table) {
					var cssDeleted  = ( booking.Deleted ? 'deleted' : '' );
					var bookingType = bookingTypesById[booking.BookingTypeId];
					if ( !bookingType ) {
						return {
							'row': [
								_('common.loadingerror.row')
							],
							'properties': {
								'class'   : '',
								'colspan' : '4'
							}
						};
					}

					var typeBadge   = new Element('div', {
						'class': 'cal_type_badge',
						'style': 'background:#'+bookingType.Color+';'
					});

					var cssInPlace;
					var typeContainer;
					var removeButtonColumn = {
						'style': 'position:relative; border-left:none; width:0; padding-left:0; padding-right:0;'
					};

					if ( this.options.editable ) {
						cssInPlace = ' inplace';

						var types  = Object.values(bookingTypesById);

						// Restrict to booking types with the same unit
						// if booking was auto-generated.
						if ( !booking.Manual )
							types = types.filter(function (item) { return ( item.Unit === bookingType.Unit ); });

						var typeMenu = this.createSelectMenu(types.map(function (type) {
							return {
								'value': type.Id,
								'text' : this.getBookingTypeCaption(type)
							};
						}.bind(this)), bookingType.Id)
							.addClass('booking_type_select')
							.addEvents({
								'change': function (event) {
									var type = bookingTypesById[typeMenu.value];
									if ( !type ) {
										event.stop();
										return;
									}

									var oldType = bookingTypesById[booking.BookingTypeId];

									booking.BookingTypeId = typeMenu.value;
									booking.Type          = type;

									if ( !oldType || (oldType.Unit !== type.Unit) ) {
										booking.Value = 0;
										setTimeout(function () {
											table.setData(transaction.Bookings);
										}, 0);
									} else {
										typeBadge.setStyle('background', '#'+type.Color);
									}
								}.bind(this)
							});

						typeContainer = new Element('div', { 'class': 'booking_type_inplace' }).adopt(
							typeBadge,
							typeMenu
						);

						removeButtonColumn.label = this.createBookingRemoveButton(table, transaction, booking);

					} else {
						cssInPlace    = '';
						typeContainer = new Element('div').adopt(
							typeBadge,
							new Element('span', {
								'class': 'booking_type_caption',
								'text' : this.getBookingTypeCaption(bookingType)
							})
						);

					}

					var unit        = bookingType.Unit;
					var displayUnit = bookingType.DisplayUnit;
					var unitText    = ' '+( _('datetime.unit.format.'+displayUnit) || displayUnit );
					var valueText   = Number(booking.Value).formatDurationFrom(unit, displayUnit);

					return {
						'row': [
							// The "&nbsp;" is used to make the "<div />"
							// non-empty in case the label is empty or consists
							// of whitespace only. The non-breakable space will
							// be trimmed later by the in-place editor handler.
							new Element('div', { 'class': cssInPlace, 'html': '&nbsp;'+String(booking.Label).htmlSpecialChars() })
								.store('tymio-inplace', { 'type': 'booking', 'item': booking, 'key': 'Label' }),
							new Element('div', { 'class': 'a_r' })
								.adopt(new Element('span', { 'class': 'p_l '+cssInPlace, 'text': valueText })
									.store('tymio-inplace', {
										'type'       : 'booking',
										'item'       : booking,
										'key'        : 'Value',
										'unit'       : unit,
										'displayUnit': displayUnit
									}))
								.appendText(unitText),
							typeContainer,
							removeButtonColumn
						],
						'properties': {
							'class' : cssDeleted
						}
					};
				}.bind(this)
			})
				.setData(transaction.Bookings);

			return $(table)
				.addEvent('click:relay(.booking_add)', function (event) {
					event.stopPropagation();

					var bookingType = Object.values(bookingTypesById)[0];
					if ( !bookingType )
						return;

					transaction.Bookings.push({
						'Manual'       : true, // Manually added booking, not auto-generated => allow all booking types
						'BookingTypeId': bookingType.Id,
						'Label'        : '',
						'Value'        : 0
					});

					setTimeout(function () {
						table.setData(transaction.Bookings);
					}, 0);
				});
		},

		getBookingTypeBadges: function (transaction) {
			// Aggregate bookings by their types
			var itemsObj = {};
			for (var i = 0; i < transaction.Bookings.length; i++) {
				var booking = transaction.Bookings[i];
				var typeId  = booking.BookingTypeId;
				var type    = bookingTypesById[typeId];
				if ( !type ) {
				} else if ( itemsObj[typeId] ) {
					itemsObj[typeId].value += Number(booking.Value);
				} else {
					itemsObj[typeId] = {
						'caption'    : ( type.Label || type.Identifier ),
						'unit'       : type.Unit,
						'displayUnit': type.DisplayUnit,
						'value'      : Number(booking.Value),
						'color'      : type.Color
					};
				}
			}

			// Bring types into alphabetical order. Note that properties in objects
			// might not be in the order they were added (e.g. in Google Chrome).
			var items = [];
			for (var id in itemsObj) {
				if ( itemsObj.hasOwnProperty(id) )
					items.push(itemsObj[id]);
			}
			items.sort(function (a, b) {
				return String(a.caption).localeCompare(b.caption);
			});

			// Create DOM elements and return them as an array

			var badges = [];

			for (var i = 0; i < items.length; i++) {
				badges.push(new Element('span', {
					'class': 'label',
					'style': 'background:#'+items[i].color+';',
					'text' : _('transaction.bookings.summary', {
						'caption': items[i].caption,
						'value'  : Number(items[i].value).formatDurationFrom(items[i].unit, items[i].displayUnit),
						'unit'   : ( _('datetime.unit.format.'+items[i].displayUnit) || items[i].displayUnit )
					})
				}));
			}

			return badges;
		}

	});

	TransactionTable.TAB_TRANSACTION_MEASURING_ROW = {};

});
