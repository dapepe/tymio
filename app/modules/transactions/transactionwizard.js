var TransactionWizard;

(function () {

	var PlaceholderBookingRow = new Class({

		popup      : undefined,

		transaction: undefined,

		typeMenu   : undefined,
		label      : undefined,
		value      : undefined,

		placeholder: undefined,

		initialize: function (transactionWizard, gui, transaction) {
			this.popup       = transactionWizard;
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

	var ClockingList = new Class({

		Implements: [ Events ],

		bound: {},

		element: undefined,
		components: {},

		allClockings: [],
		selectedClockings: {},

		previousUserId: undefined,
		modified: false,

		ready: false,

		initialize: function (gui) {
			this.bound = {
				'update'          : this.update.bind(this),
				'selectAllHandler': this.selectAllHandler.bind(this)
			};

			var selUser       = new Element('div', { 'class': 'f_l' });
			var monthPicker   = new Element('div', { 'class': 'f_l m_l' });
			var clockingTable = new Element('div', { 'class': 'simple_table m_t' });

			this.element      = new Element('div').adopt(
				selUser,
				monthPicker,
				new Element('div', { 'class': 'clear' }),
				clockingTable
			);

			this.components.selUser = new gx.bootstrap.Select(selUser, {
				'icon'          : 'user',
				'label'         : {
					'text'      : _('entity.user.singular'),
					'class'     : 'dialog_label'
				},
				'msg'           : { 'noSelection' : '--- '+_('field.pleaseselect')+' ---' },
				'decodeResponse': gui.initResult,
				'default'       : null,
				'requestData'   : {
					'api'       : 'user',
					'do'        : 'list'
				},
				'requestParam'  : 'search',
				'listFormat'    : getFullName,
				'formatID'      : function (item) {
					return ( item ? item.Id : false );
				},
				'onSelect'      : function (selection) {
					if ( this.previousUserId !== selection.Id ) {
						this.clear();
						this.previousUserId = selection.Id;
					}

					this.update();
					this.fireEvent('userselect', [ this.components.selUser ]);
				}.bind(this)
			})
				.set(AUTHENTICATED_USER);

			var firstDay = new Date().parse('1st');

			this.components.monthPicker = new gx.bootstrap.MonthPicker(monthPicker, {
				'onSelect': this.bound.update
			});

			this.components.selectAllCheckbox = new Element('input', { 'type': 'checkbox' })
				.addEvent('click', this.bound.selectAllHandler);

			this.components.clockingTable = new gx.bootstrap.Table(clockingTable, {
				//'simpleTable'    : true,
				'stopPropagation': true,
				'theme'          : {
					'table_head' : 'fullw fixed table table-bordered',
					'table_body' : 'fixed table table-striped table-bordered'
				},
				'cols'           : [
					{ 'label': this.components.selectAllCheckbox, 'id': 'check', 'width': '20px', 'filterable': false, 'clickable': false },
					{ 'label': 'Arbeitszeit', 'id' : 'Start', 'width': '30%' },
					{ 'label': _('clocking.booked.caption'), 'id' : 'Booked', 'width': '80px' },
					{ 'label': 'Von', 'id' : 'Start', 'width': '10%', 'filter' : 'desc', 'properties': { 'class': 'a_r' } },
					{ 'label': 'Bis', 'id' : 'End', 'width': '10%', 'properties': { 'class': 'a_r' } },
					{ 'label': 'Netto', 'id': 'WorkTime', 'width': '14%', 'properties': { 'class': 'a_r' }, 'filterable': false },
					{ 'label': 'Typ', 'id' : 'Type', 'width': '20%' }
				],
				'onStart'        : function () {
				},
				'onComplete'     : function () {
				},
				'onFilter'       : this.bound.update,
				'onClick'        : function (clocking, event) {
					if ( clocking.checkbox instanceof Element ) {
						clocking.checkbox.checked = !clocking.checkbox.checked;
						clocking.checkbox.fireEvent('click', [ event ]);
					}
				},
				'structure'      : function (clocking) {
					var me = this;

					var isOpen = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );

					if ( isOpen ) {
						clocking.checkbox = '';
					} else {
						clocking.checkbox = new Element('input', { 'type': 'checkbox', 'class': 'transaction_popup_clocking_checkbox' })
							.addEvent('click', function (event) {
								event.stopPropagation();

								var tr = $('transaction_popup_clocking_'+clocking.Id);
								if ( this.checked ) {
									me.selectedClockings[clocking.Id] = clocking;
									tr.addClass('selected');
								} else {
									delete me.selectedClockings[clocking.Id];
									tr.removeClass('selected');
								}

								// Adjust "Select All" checkbox to match the
								// majority of checkboxes.
								me.components.selectAllCheckbox.checked = ( $(me.components.clockingTable).getElements('.transaction_popup_clocking_checkbox:checked').length >= me.allClockings.length / 2 );

								me.modified = true;
							});
						clocking.checkbox.checked = this.selectedClockings[clocking.Id];
					}

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

					var statusIcon;
					var statusText;
					if ( clocking.Booked ) {
						statusIcon = 'ok-sign';
						statusText = _('clocking.booked.closed');
					} else if ( clocking.Frozen ) {
						statusIcon = 'lock';
						statusText = _('clocking.booked.frozen');
					} else {
						statusIcon = 'pencil';
						statusText = _('clocking.booked.open');
					}

					var cssDeleted = ( clocking.Deleted ? 'deleted ' : '' );

					return {
						'row': [
							clocking.checkbox,
							'<div class="'+cssDeleted+'ellipsis">'+(_('date.day_names.'+start.getDay())+' '+start.format(_('date.format.default'))).htmlSpecialChars()+'</div>',
							'<div class="icon-'+statusIcon+' m_l" title="'+statusText+'"></div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+start.format(dateFormat).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+end.format(dateFormat).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis a_r">'+String(duration).htmlSpecialChars()+'</div>',
							'<div class="'+cssDeleted+'ellipsis"><div class="cal_type_badge" style="background:#'+clocking.Type.Color+';"></div>'+( _('clocking.type.'+clocking.Type.Identifier) || clocking.Type.Label )+'</div>'
						],
						'properties': {
							'id'           : 'transaction_popup_clocking_'+clocking.Id,
							'class'        :
								( isOpen ? 'clocking_open' : '' )+
								( clocking.IsHoliday ? ' holiday' : '' )+
								( this.selectedClockings[clocking.Id] ? ' selected' : '' ),
							'data-clocking': clocking.Id
						}
					};
				}.bind(this)
			});

			this.ready = true;
		},

		update: function () {
			if ( !this.ready )
				return this;

			var dateRangeFilter = {
				'start'      : this.components.monthPicker.getStart(),
				'end'        : this.components.monthPicker.getEnd()
			};

			var filter = Object.merge(
				this.components.clockingTable.getFilter(),
				dateRangeFilter,
				{
					'showdeleted': false,
					'showbooked' : ClockingAPI.SHOW_BOOKED_ALL,
					'user'       : this.components.selUser.getId()
				}
			);

			(function (clockings, types, holidaysByDate) {
				this.allClockings = clockings;

				this.components.clockingTable.setData(mergeData(
					clockings,
					{
						'Types': types.reindex('Id')
					},
					function (clocking, related) {
						clocking.Type = ( related.Types[clocking.TypeId] || null );
						clocking.IsHoliday = HolidayManager.isHoliday(clocking, holidaysByDate);
					}.bind(this)
				));

				// Adjust "Select All" checkbox to match the
				// majority of checkboxes.
				this.components.selectAllCheckbox.checked = ( $(this.components.clockingTable).getElements('.transaction_popup_clocking_checkbox:checked').length >= this.allClockings.length / 2 );
			}).bind(this).future()(
				ClockingAPI.list.toApiPromise(ClockingAPI, [ filter ]),
				ClockingAPI.types.toApiPromise(ClockingAPI, [ false ]),
				HolidayManager.get(Object.append({ 'domain': this.components.selUser.getSelected().DomainId }, dateRangeFilter))
			);

			return this;
		},

		clear: function () {
			this.selectedClockings = {};
			this.modified = true;
			return this;
		},

		toElement: function () {
			return this.element;
		},

		selectAllHandler: function (event) {
			event.stopPropagation();

			if ( this.components.selectAllCheckbox.checked ) {
				// Select all
				for (var i = 0; i < this.allClockings.length; i++) {
					var clocking = this.allClockings[i];

					var isOpen = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );
					if ( !isOpen )
						this.selectedClockings[clocking.Id] = clocking;

					if ( clocking.checkbox )
						clocking.checkbox.checked = true;

					var $row = $('transaction_popup_clocking_'+clocking.Id);
					if ( $row )
						$row.addClass('selected');
				}
			} else {
				// Deselect all
				for (var i = 0; i < this.allClockings.length; i++) {
					var clocking = this.allClockings[i];
					delete this.selectedClockings[clocking.Id];

					if ( clocking.checkbox )
						clocking.checkbox.checked = false;

					var $row = $('transaction_popup_clocking_'+clocking.Id);
					if ( $row )
						$row.removeClass('selected');
				}
			}

			this.modified = true;
		}

	});

	var placeholderBooking = null;

	TransactionWizard = new Class({

		Implements      : [ Events ],

		bookingTypesById: undefined,
		transactions    : [],

		popup           : undefined,
		clockingList    : undefined,
		tabbox          : undefined,

		initialize: function (gui, bookingTypesById) {
			this.bookingTypesById = ( bookingTypesById || {} );

			this.transactionTable = new TransactionTable(null, {
				'editable'       : true,
				'showUser'       : false,
				'measuringRow'   : [
					'50px',
					'180px',
					'140px',
					'250px',
					'250px',
					''
				],
				'onSelect'       : this.updateButtons.bind(this)
//				'onFilter'       : ui.list.bind(ui),
//				'onDblclick'     : ui.open.bind(ui),
//				'onUserclick'    : ui.open.bind(ui)
			});

			this.clockingList = new ClockingList(gui);

			this.btnNext = new Element('input', {
				'type'   : 'button',
				'class'  : 'btn btn-success m2_l',
				'value'  : 'Next'

			})
				.addEvent('click', function () {
					this.tabbox.openTab('transaction');
				}.bind(this));

			this.btnBack = new Element('input', {
				'type'   : 'button',
				'class'  : 'btn m2_l',
				'value'  : 'Back'
			})
				.addEvent('click', function () {
					this.tabbox.openTab('clockings');
				}.bind(this));

			this.btnSave = new Element('input', {
				'type'   : 'button',
				'class'  : 'btn btn-primary m2_l',
				'value'  : 'Save'
			})
				.addEvent('click', this.save.bind(this));

			this.tabbox = new gx.bootstrap.Tabbox(new Element('div'), { 'frames': [
				{ 'name': 'clockings', 'title': _('transaction.section.clockings'), 'content': $(this.clockingList) },
				{ 'name': 'transaction', 'title': _('transaction.section.details'), 'content': $(this.transactionTable) }
			]})
				.addEvent('change', function (name) {
					if ( name === 'clockings' ) {
						this.clockingList.components.clockingTable.setHeight(this.calculateHeight(this.clockingList.components.clockingTable));

						this.btnSave.hide();
						this.btnNext.show();
						this.btnBack.hide();

					} else if ( name === 'transaction' ) {
						this.transactionTable.setHeight(this.calculateHeight(this.transactionTable));

						this.btnSave.show();
						this.btnNext.hide();
						this.btnBack.show();

						if ( this.clockingList.modified ) {
							var clockingIds = Object.keys(this.clockingList.selectedClockings);
							this.generateBookings(clockingIds);
						}

					}

					this.popup.setPosition();
				}.bind(this));

			this.popup = new gx.bootstrap.Popup({
				'width'   : window.getSize().x - 100,
				'content' : this.tabbox,
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
					},
					'btnNext'    : this.btnNext,
					'btnBack'    : this.btnBack,
					'btnOk'      : this.btnSave
				}}),
				'closable': true
			});

			window.addEvent('resize', function () {
				this.tabbox.reopen();
			}.bind(this));
		},

		updateButtons: function () {
			var selectedTransactions = Object.values(this.transactionTable.selectedTransactions);
			if ( selectedTransactions.length > 0 )
				this.btnSave.erase('disabled');
			else
				this.btnSave.setProperty('disabled', 'disabled');
		},

		createEmptyTransaction: function (user) {
			var nowTimestamp = new Date().parse('today').format('%s');

			return {
				'Id'       : null,
				'UserId'   : ( user ? user.Id : null ),
				'Start'    : Number(nowTimestamp),
				'End'      : Number(nowTimestamp),
				'Clockings': [],
				'Bookings' : []
			};
		},

		generateBookings: function (clockingIds) {
			this.transactionTable.empty();
			this.updateButtons();

			if ( clockingIds.length === 0 ) {
				this.clockingList.modified = false;

				// Create empty transaction
				this.transactions = [ this.createEmptyTransaction(this.clockingList.components.selUser.getSelected()) ];

				this.transactionTable.setData(this.transactions)
					.then(function () {
						this.transactionTable.select(this.transactions);
					}.bind(this));

				this.updateButtons();

				return;
			}

			TransactionAPI.add(clockingIds, false, function (data) {
				this.transactions = [];

				var dataByUserId = data.result;
				if ( !dataByUserId )
					return;

				var userId       = Object.keys(dataByUserId)[0];
				if ( userId == null )
					return;

				var userData     = dataByUserId[userId];
				if ( !userData )
					return;

				this.clockingList.modified = false;

				var user = this.clockingList.components.selUser.getSelected();
				var transactions = dataByUserId[userId].transactions;

				for (var i = 0; i < transactions.length; i++) {
					transactions[i].User = user;

					for (var j = 0; j < transactions[i].Clockings.length; j++) {
						if ( typeof(transactions[i].Clockings[j]) !== 'object' )
							transactions[i].Clockings[j] = this.clockingList.selectedClockings[transactions[i].Clockings[j]];
						else if ( (typeof(transactions[i].Clockings[j].Id) !== 'undefined') &&
						         this.clockingList.selectedClockings[transactions[i].Clockings[j].Id] )
							transactions[i].Clockings[j] = this.clockingList.selectedClockings[transactions[i].Clockings[j].Id];
					}
				}

				this.transactions = transactions;
				this.transactionTable.setData(this.transactions)
					.then(function () {
						this.transactionTable.select(this.transactions);
					}.bind(this));

				this.updateButtons();
			}.bind(this));
		},

		setBookingTypesById: function (bookingTypesById) {
			this.bookingTypesById = bookingTypesById;
			return this;
		},

		show: function (user, clockings) {
			// This must be set before initializing the clockings because
			// changing the user will deselect all clockings.
			this.clockingList.components.selUser.set(user || AUTHENTICATED_USER);

			var selectedClockings = [];
			for (var i = 0; i < clockings.length; i++) {
				var clocking = clockings[i];
				var isOpen   = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );
				if ( !isOpen )
					selectedClockings.push(clocking);
			}

			this.clockingList.selectedClockings = selectedClockings
				.erase(TransactionWizard.PLACEHOLDER_CLOCKING)
				.reindex('Id');
			this.clockingList.modified = true;

			this.popup.show();

			this.tabbox
				.revealTab('clockings')
				.openTab( clockings.length > 0 ? 'transaction' : 'clockings' );

			this.clockingList.update();

			this.popup.setPosition();

			return this;
		},

		calculateHeight: function (element) {
			return window.getSize().y - 250 - $(element).getPosition(this.tabbox).y;
		},

		save: function () {
			// Add booking before saving if user has entered data
			if ( placeholderBooking && placeholderBooking.hasData() && !placeholderBooking.add() )
				return;

			var selectedTransactions = Object.values(this.transactionTable.selectedTransactions);
			if ( selectedTransactions.length === 0 )
				return;

			var userId = this.clockingList.components.selUser.getId();
			if ( userId == null )
				return;

			for (var i = 0; i < selectedTransactions.length; i++) {
				var bookings = this.toBookingData(selectedTransactions[i].Bookings);
				if ( bookings.length === 0 )
					continue;

				TransactionAPI.create(selectedTransactions[i], bookings, this.toClockingData(selectedTransactions[i].Clockings), function (result) {
					if ( result.result === true )
						this.popup.hide();

					this.fireEvent('save', [ selectedTransactions[i], result ]);
				}.bind(this));
			}
		},


		/**
		 * Transforms the specified bookings to a request-compatible format.
		 * Existing bookings will be reduced to IDs, new bookings with their "Id"
		 * property set to null will be stored as objects.
		 *
		 * @param {Array} bookings
		 * @returns The transformed array.
		 * @type Array
		 */
		toBookingData: function (bookings) {
			var result = [];

			for (var i = 0; i < bookings.length; i++) {
				if ( (bookings[i] === TransactionWizard.PLACEHOLDER_BOOKING) ||
				     bookings[i].Deleted )
					continue;

				result.push({
					'BookingTypeId': bookings[i].BookingTypeId,
					'Label'        : bookings[i].Label,
					'Value'        : bookings[i].Value
				});
			}

			return result;
		},

		toClockingData: function (clockings) {
			var result = [];

			for (var i = 0; i < clockings.length; i++) {
				if ( clockings[i] !== TransactionWizard.PLACEHOLDER_CLOCKING )
					result.push(clockings[i].Id);
			}

			return result;
		}

	});

	TransactionWizard.PLACEHOLDER_BOOKING    = {};
	TransactionWizard.PLACEHOLDER_CLOCKING   = {};
	TransactionWizard.CLOCKING_MEASURING_ROW = {
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
