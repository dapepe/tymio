var TransactionPopup;

(function () {

	var PlaceholderBookingRow = new Class({

		popup      : undefined,

		transaction: undefined,

		typeMenu   : undefined,
		label      : undefined,
		value      : undefined,

		placeholder: undefined,

		initialize: function (transactionPopup, gui, transaction) {
			this.popup       = transactionPopup;
			this.transaction = transaction;

			this.typeMenu = new gx.bootstrap.Select(null, {
				'icon'          : 'tag',
				'width'         : '60px',
				'msg'           : { 'noSelection' : '--- '+_('field.pleaseselect')+' ---' },
				'decodeResponse': gui.initResult,
				'default'       : null,
				'requestData'   : {
					'api'       : 'transaction',
					'do'        : 'types'
				},
				'requestParam'  : 'search',
				'listFormat'    : function (bookingType) {
					return ( bookingType.Label || bookingType.Identifier || bookingType.Id );
				},
				'formatID'      : function (bookingType) {
					return ( bookingType ? bookingType.Id : null );
				},
				'onSelect'      : function (bookingType) {
				}
			});

			this.label = new Element('input', { 'type': 'text', 'style': 'width:140px;' });
			this.value = new Element('input', { 'type': 'text', 'style': 'width:50px;' });

			// Yield in-place editable placeholder booking entry
			this.placeholder = {
				'row'       : [
					$(this.typeMenu),
					this.label,
					this.value,
					new Element('a', { 'class': 'btn btn-success', 'html': '<i class="icon-plus"></i>' })
						.addEvent('click', function (event) {
							event.stop();
							this.add();
						}.bind(this))
				],
				'properties': {
					'class' : 'placeholder'
				}
			}
		},

		hasData: function () {
			return (
				(this.typeMenu.getId() != null) ||
				(this.label.value != '') ||
				(this.value.value != '')
			);
		},

		add: function () {
			var typeId = this.typeMenu.getId();

			if ( typeId == null ) {
				this.typeMenu.show();
				return false;
			} else if ( (this.value.value.trim() === '') ||
						isNaN(parseInt(this.value.value)) ) {
				this.value.focus();
				return false;
			}

			this.transaction.Bookings.push({
				'Id'           : null,
				'BookingTypeId': typeId,
				'Label'        : this.label.value,
				'Value'        : this.value.value
			});

			this.typeMenu.set(null);
			this.label.value = '';
			this.value.value = '';

			setTimeout(function () {
				this.popup.listBookings();
			}.bind(this), 0);

			return true;
		},

		getRow: function () {
			return this.placeholder;
		}

	});

	var placeholderBooking = null;

	TransactionPopup = new Class({

		Implements      : [ Events ],

		bookingTypesById: undefined,
		transaction     : undefined,
		start           : undefined,
		end             : undefined,
		bookings        : undefined,
		clockings       : undefined,
		popup           : undefined,
		bound           : {},

		initialize: function (gui, bookingTypesById) {
			this.bookingTypesById = ( bookingTypesById || {} );

			this.start = new gx.bootstrap.DatePicker('transaction_edit_start', {
				'icon'           : 'play',
				'label'          : {
					'text'       : _('field.start'),
					'class'      : 'dialog_label'
				},
				'timePicker'     : false,
				'format'         : _('date.format.default'),
				'return_format'  : null,
				'readOnly'       : true
			});
			this.end = new gx.bootstrap.DatePicker('transaction_edit_end', {
				'icon'           : 'play',
				'label'          : {
					'text'       : _('field.end'),
					'class'      : 'dialog_label'
				},
				'timePicker'     : false,
				'format'         : _('date.format.default'),
				'return_format'  : null,
				'readOnly'       : true
			});

			this.bookings = new gx.bootstrap.Table('transaction_edit_bookings', {
				//'simpleTable'    : true,
				'stopPropagation': true,
				'theme'          : {
					'table_head' : 'fullw fixed table table-bordered',
					'table_body' : 'fixed table table-striped table-bordered'
				},
				'cols'           : [
					{ 'label'    : 'Typ', 'id': 'BookingType', 'filter': 'asc', 'width': '100px' },
					{ 'label'    : 'Name', 'id': 'Label', 'width': '156px' },
					{ 'label'    : 'Dauer', 'id': 'Value', 'width': '66px', 'properties': { 'class': 'a_r' } },
					{ 'label'    : '<i class="icon-pencil"></i>', 'id': 'actions', 'width': '36px', 'filterable': 'false', 'clickable': false }
				],
				'onFilter'       : this.listBookings.bind(this),
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'onClick'        : function (booking, event) {
				},
				'onDblclick'     : function (booking, event) {
					event.stop();
					deselect();
				},
				'structure'      : function (booking) {
					if ( !this.transaction )
						throw new Error('Cannot list bookings without a transaction selected.');

					if ( booking === TransactionPopup.PLACEHOLDER_BOOKING ) {
						placeholderBooking = new PlaceholderBookingRow(this, gui, this.transaction);
						return placeholderBooking.getRow();
					}

					var bookingType = this.bookingTypesById[booking.BookingTypeId];
					if ( !bookingType )
						bookingType = { 'Label': booking.BookingTypeId, 'Unit': '' };

					var actions = '';
					if ( this.transaction.Id == null ) {
						actions = new Element('a', { 'class': 'btn btn-danger', 'html': '<i class="icon-trash"></i>' })
							.addEvent('click', function (event) {
								event.stop();
								this.transaction.Bookings.erase(booking);
								setTimeout(this.listBookings.bind(this), 0);
							}.bind(this));
					}

					return {
						'row': [
							'<div class="ellipsis">'+String(bookingType.Label).htmlSpecialChars()+'</div>',
							'<div class="ellipsis">'+String(booking.Label).htmlSpecialChars()+'</div>',
							'<div class="ellipsis a_r">'+(Number(booking.Value).formatDurationFrom(bookingType.Unit, bookingType.DisplayUnit)+' '+bookingType.DisplayUnit).htmlSpecialChars()+'</div>',
							actions
						],
						'properties': {
						}
					};
				}.bind(this)
			});

			this.clockings = new gx.bootstrap.Table('transaction_edit_clockings', {
				//'simpleTable'    : true,
				'stopPropagation': true,
				'theme'          : {
					'table_head' : 'fullw fixed table table-bordered',
					'table_body' : 'fixed table table-striped table-bordered'
				},
				'cols'           : [
					{ 'label': 'Arbeitszeit', 'id' : 'Start', 'filterable': false },
					{ 'label': 'Von', 'id' : 'Start', 'width': '6ex', 'properties': { 'class': 'a_r' }, 'filterable': false },
					{ 'label': 'Bis', 'id' : 'End', 'width': '6ex', 'properties': { 'class': 'a_r' }, 'filterable': false },
					{ 'label': 'Netto', 'id': 'WorkTime', 'width': '7ex', 'properties': { 'class': 'a_r' }, 'filterable': false },
					{ 'label': 'Typ', 'id' : 'ClockingType', 'filterable': false }
				],
				'onFilter'       : this.listClockings.bind(this),
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'onClick'        : function (clocking, event) {
				},
				'onDblclick'     : function (clocking, event) {
					event.stop();
					deselect();
				},
				'structure'      : function (clocking) {
					if ( clocking === TransactionPopup.CLOCKING_MEASURING_ROW ) {
						return clocking;
					} else if ( clocking === TransactionPopup.PLACEHOLDER_CLOCKING ) {
						return [
							{
								'label':
									'<div id="transaction_edit_clockings_add_notice" class="alert alert-info">'+
										'Es sind keine Arbeitszeiten eingetragen.'+
									'</div>',
								'colspan': 5
							}
						];
					}

					var isOpen = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );

					clocking.Time  = (clocking.End - clocking.End % 60) - (clocking.Start - clocking.Start % 60);

					var start      = new Date(clocking.Start * 1000);
					var end        = new Date(clocking.End * 1000);

					var sameDay = ( start.format('Ymd') === end.format('Ymd') );

					var dateFormat;
					var duration;
					if ( clocking.Type.WholeDay ) {
						duration = (clocking.Time / 86400 + 1).round(); // in days
						dateFormat = _('date.format.default');
					} else {
						duration = Number(clocking.Time - clocking.Breaktime).formatDuration('minutes');
						dateFormat = _('time.format.time');
					}

					var cssDeleted = ( clocking.Deleted ? 'deleted ' : '' );

					return {
						'row': [
							'<div class="'+cssDeleted+'ellipsis">'+(_('date.day_names.'+start.getDay())+' '+start.format(_('date.format.default'))).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+start.format(dateFormat).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+end.format(dateFormat).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+String(duration).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis"><div class="cal_type_badge" style="background:#'+clocking.Type.Color+';"></div>'+( _('clocking.type.'+clocking.Type.Identifier) || clocking.Type.Label )+'</div>'
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

			this.bound = {
				'resize': this.resize.bind(this)
			};

			this.popup = new gx.bootstrap.Popup({
				'width'   : 900,
				'content' : $('tabTransactionDetails'),
				'title'   : _('entity.transaction.singular'),
				'footer'  : __({ 'children': {
					'btnClose'   : {
						'tag'    : 'input',
						'type'   : 'button',
						'class'  : 'btn f_l',
						'value'  : 'Close',
						'onClick': function () {
							this.popup.hide();
						}.bind(this)
					}
				}}),
				'closable': true,
				'onShow'  : this.bound.resize
			});

			window.addEvent('resize', this.bound.resize);
		},

		resize: function () {
			this.bookings.setHeight(this.calculateHeight(this.bookings));
			this.clockings.setHeight(this.calculateHeight(this.clockings));
			this.popup.setPosition();
		},

		setBookingTypesById: function (bookingTypesById) {
			this.bookingTypesById = bookingTypesById;
			return this;
		},

		getTransaction: function () {
			return this.transaction;
		},

		show: function (transaction) {
			if ( transaction == null )
				return this;

			this.transaction = transaction;

			this.popup.show();

			this.start
				.setReadOnly(true)
				.set(new Date(transaction.Start * 1000));

			this.end
				.setReadOnly(true)
				.set(new Date(transaction.End * 1000));

			$('transaction_edit_user').set('text', getFullName(transaction.User));
			$('transaction_edit_comment')
				.setProperty('readonly', 'readonly')
				.value = transaction.Comment;

			this.bookings.setData(transaction.Bookings);
			this.clockings.setData(transaction.Clockings);

			this.popup.setPosition();

			return this;
		},

		calculateHeight: function (element) {
			return window.getSize().y - 250 - $(element).getPosition($('tabTransactionDetails')).y;
		},

		listBookings: function () {
			this.bookings.setData(this.transaction.Bookings);
		},

		listClockings: function () {
			var data = [ TransactionPopup.CLOCKING_MEASURING_ROW ];
			if ( this.transaction.Clockings.length === 0 )
				data.push(TransactionPopup.PLACEHOLDER_CLOCKING);

			this.clockings.setData(data.append(this.transaction.Clockings));
		}

	});

	TransactionPopup.PLACEHOLDER_BOOKING    = {};
	TransactionPopup.PLACEHOLDER_CLOCKING   = {};
	TransactionPopup.CLOCKING_MEASURING_ROW = {
		'row'       : [
			'',
			'',
			'',
			'',
			''
		],
		'properties': {
			'style' : 'display:none; border:none;'
		}
	};

})();
