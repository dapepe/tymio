var ClockingAPI     = null;
var ClockingManager = null;

var ClockingTable = new Class({

	Implements: [ Events, Options ],

	options: {
		'showUser': true
	},

	container: undefined,

	table: undefined,
	selectAllCheckbox: undefined,

	clockings: [],
	selectedClockings: {},

	initialize: function (container, options) {
		this.container = container;
		this.setOptions(options);

		this.selectAllCheckbox = new Element('input', { 'type': 'checkbox' })
			.addEvent('click', this.selectAllHandler.bind(this));

		this.table = new gx.bootstrap.Table(container, Object.append({
			'cols'       : [
				{ 'label': this.selectAllCheckbox, 'id': 'check', 'width': '20px', 'filterable': false, 'clickable': false },
				{ 'label': _('clocking.status.caption'), 'id': 'ApprovalStatus', 'width': '40px', 'filterable': false },
				{ 'label': _('entity.user.singular'), 'id': 'User', 'properties': { 'class': 'clocking_table_user_column' } },
				{ 'label': _('clocking.booked.caption'), 'id': 'Booked', 'width': '60px', 'filterable': false },
				{ 'label': '', 'id': 'Start', 'width': '80px', 'filter' : 'desc' },
				{ 'label': _('field.start'), 'id': 'Start', 'width': '90px', 'properties': { 'class': 'a_r' }, 'filter' : 'desc' },
				{ 'label': _('field.end'), 'id': 'End', 'width': '100px' },
				{ 'label': _('clocking.from_to'), 'id': 'FromTo', 'width': '140px', 'properties': { 'class': 'a_r' }, 'filterable': false },
				{ 'label': _('clocking.time'), 'id': 'Time', 'width': '80px', 'properties': { 'class': 'a_r' }, 'filterable': false },
				{ 'label': _('clocking.type.caption'), 'id': 'Type' },
				{ 'label': _('field.creationdate'), 'id': 'End', 'width': '100px' },
				{ 'label': _('field.last_changed'), 'id': 'End', 'width': '100px' }
			],
			'onFilter'   : this.filterHandler.bind(this),
			'onStart'    : function () { },
			'onComplete' : function () { },
			'onClick'    : this.clickHandler.bind(this),
			'onDblclick' : this.dblClickHandler.bind(this),
			'structure'  : this.structure.bind(this)
		}, this.options));

		$(this.table).addClass('clocking_table');

		this.showUserColumn(this.options.showUser);
	},

	selectAllHandler: function (event) {
		event.stopPropagation();

		// Deselect all
		this.doSelect(null);

		// Select all
		if ( this.selectAllCheckbox.checked )
			this.doSelect(this.clockings);
	},

	/**
	 * Selects or deselects all clockings but does not update the select-all checkbox.
	 *
	 * @param {Array} clockings
	 * @returns Returns this instance for method chaining.
	 * @type ClockingTable
	 * @see select()
	 */
	doSelect: function (clockings) {
		if ( clockings == null ) {
			// Deselect all

			for (var id in this.selectedClockings) {
				if ( !this.selectedClockings.hasOwnProperty(id) )
					continue;

				var clocking = this.selectedClockings[id];

				if ( clocking._checkbox )
					clocking._checkbox.checked = false;

				var $row = clocking.tr;
				if ( $row )
					$row.removeClass('selected');
			}

			this.selectedClockings     = {};
			this.selectedClockingCount = 0;

			this.fireEvent('select', [ this.selectedClockings, null, this ]);

			return this;
		}

		// Select
		for (var i = 0; i < clockings.length; i++) {
			var clocking = clockings[i];
			this.selectedClockings[clocking.Id] = clocking;

			if ( clocking._checkbox )
				clocking._checkbox.checked = true;

			var $row = clocking.tr;
			if ( $row )
				$row.addClass('selected');
		}

		this.selectedClockingCount += clockings.length;

		this.fireEvent('select', [ this.selectedClockings, null, this ]);

		return this;
	},

	/**
	 * Selects or deselects all clockings and updates the select-all checkbox.
	 *
	 * @param {Array} clockings
	 * @returns Returns this instance for method chaining.
	 * @type TransactionTable
	 * @see doSelect()
	 */
	select: function (clockings) {
		this.doSelect(clockings);
		this.selectAllCheckbox.checked = ( this.selectedClockingCount >= this.clockings.length / 2 );
		return this;
	},

	toElement: function () {
		return $(this.table);
	},

	showUserColumn: function (visible) {
		this.options.showUser = visible;
		$(this.table)[ visible ? 'removeClass' : 'addClass' ]('clocking_table_user_hidden');
		return this;
	},

	setHeight: function (height) {
		this.table.setHeight(height);
		return this;
	},

	getFilter: function () {
		return this.table.getFilter();
	},

	setData: function (user, clockingsPromise) {
		var holidaysPromise  = new Promise();
		(function (clockings) {
			holidaysPromise.deliver(HolidayManager.get(Object.append(
				( user === null ? {} : { 'domain': user.DomainId } ),
				ClockingManager.getRange(clockings)
			), true));
		}).future()(clockingsPromise);

		(function (clockings, users, types, holidays) {
			var usersById = users.reindex('Id');
			var typesById = types.reindex('Id');

			for (var i = 0; i < clockings.length; i++) {
				var clocking = clockings[i];

				clocking.User      = ( usersById[clocking.UserId] || null );
				clocking.Type      = ( typesById[clocking.TypeId] || null );
				clocking.IsHoliday = (
					clocking.User
					? HolidayManager.isHoliday(clocking, holidays[clocking.User.DomainId])
					: false
				);
			}

			this.selectAllCheckbox.checked = false;
			this.selectedClockingCount     = 0;
			this.selectedClockings         = {};
			this.clockings                 = clockings;

			this.table.setData(clockings);
		}).future().call(this,
			clockingsPromise,
			UserAPI.list.toApiPromise(UserAPI, [ null ]),
			ClockingAPI.types.toApiPromise(ClockingAPI, [ false ]),
			holidaysPromise
		);

		return this;
	},

	filterHandler: function (column) {
		this.fireEvent('filter', [ column, this ]);
	},

	clickHandler: function (clocking, event) {
		if ( clocking._checkbox instanceof Element ) {
			clocking._checkbox.checked = !clocking._checkbox.checked;
			clocking._checkbox.fireEvent('click', [ event ]);
		}

		this.fireEvent('click', [ clocking, this, event ]);
	},

	dblClickHandler: function (clocking, event) {
		event.stop();
		deselect();
		this.fireEvent('dblclick', [ clocking, this, event ]);
	},

	structure: function (clocking) {
		var isOpen       = ( (clocking.Start === clocking.End) && !clocking.Type.WholeDay && !clocking.Deleted );

		// A clocking is considered "changed" if it specifies a future
		// non-whole-day clocking or when the last change date differs more
		// than a minute to the end date (the latter changed is addressed later).
		var creationDate = Number(clocking.Creationdate);
		var changeDate   = Number(clocking.LastChanged);
		var changed      = ( Math.abs(changeDate - Number(clocking.End)) > 60 );

		var me           = this;

		clocking._checkbox = new Element('input', {
			'id'   : 'clocking_checkbox_'+clocking.Id,
			'type' : 'checkbox',
			'class': 'clocking_checkbox clocking_user_'+clocking.User.Id,
			'value': clocking.Id
		})
			.store('tymio-clocking', clocking)
			.addEvent('click', function (event) {
				var tr = $('clocking_'+clocking.Id);
				if ( tr )
					tr.toggleClass('selected');

				if ( this.checked ) {
					if ( !me.selectedClockings[clocking.Id] )
						me.selectedClockingCount++;
					me.selectedClockings[clocking.Id] = clocking;
				} else {
					if ( me.selectedClockings[clocking.Id] )
						me.selectedClockingCount--;
					delete me.selectedClockings[clocking.Id];
				}

				me.selectAllCheckbox.checked = ( me.selectedClockingCount >= me.clockings.length / 2 );
				me.fireEvent('select', [ me.selectedClockings, event, me ]);
			});

		clocking.Time = clocking.End - clocking.Start;

		var start     = new Date(clocking.Start * 1000);
		var end       = new Date(clocking.End * 1000);

		var dateType;
		var duration;
		if ( clocking.Type.WholeDay ) {
			dateType      = 'date';
			duration      = (clocking.Time / 86400 + 1).round(); // in days
		} else {
			changed      |= (
				(Math.abs(creationDate - Number(clocking.Start)) > 60) ||
				(changeDate < Number(clocking.Start))
			);
			dateType      = 'time';
			var breaktime = Number(clocking.Breaktime);
			duration      = (clocking.Time - breaktime).formatDuration('minutes');
			if ( breaktime != 0 )
				duration += ' + '+breaktime.formatDuration('minutes');
		}

		var sameDay = ( start.format('%Y-%m-%d') === end.format('%Y-%m-%d') );

		var endFormat;
		if ( sameDay )
			endFormat = '.format.'+( clocking.Type.WholeDay ? 'default' : 'time' );
		else
			endFormat = '.format.reverse';

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

		var cssDeleted = ( clocking.Deleted ? 'deleted' : '' );

		var APPROVAL_STATUS_ICON_MAP = {};
		APPROVAL_STATUS_ICON_MAP[ClockingAPI.APPROVAL_STATUS_PRELIMINARY] = 'asterisk';
		APPROVAL_STATUS_ICON_MAP[ClockingAPI.APPROVAL_STATUS_REQUIRED]    = 'exclamation-sign';
		APPROVAL_STATUS_ICON_MAP[ClockingAPI.APPROVAL_STATUS_DENIED]      = 'ban-circle';
		APPROVAL_STATUS_ICON_MAP[ClockingAPI.APPROVAL_STATUS_CONFIRMED]   = 'ok';
		APPROVAL_STATUS_ICON_MAP[ClockingAPI.APPROVAL_STATUS_AS_IS]       = 'ok-sign';
		var approvalStatusIcon = APPROVAL_STATUS_ICON_MAP[clocking.ApprovalStatus];
		if ( typeof(approvalStatusIcon) === 'undefined' )
			approvalStatusIcon = 'question-sign';

		var caption = new Element('a', {
			'text' : getFullName(clocking.User).htmlSpecialChars(),
			'class': cssDeleted
		})
			.addEvent('click', function (event) {
				event.stop();
				this.fireEvent('open', [ clocking, this, event ]);
			}.bind(this));
		if ( clocking.Deleted )
			caption = new Element('div').adopt(Factory.DeletedBadge(), caption);

		return {
			'row': [
				clocking._checkbox,
				'<i class="clocking_table_status icon-'+approvalStatusIcon+'" title="'+ClockingManager.getApprovalStatusCaption(clocking.ApprovalStatus)+'"></i>',
				{ 'label': caption, 'class': 'clocking_table_user_column' },
				'<div class="icon-'+statusIcon+' m_l" title="'+statusText+'"></div>',
				'<div class="'+cssDeleted+' a_r">'+_('date.day_names.'+start.getDay())+'</div>',
				'<div class="'+cssDeleted+' a_r">'+start.format(_('date.format.default'))+'</div>',
				'<span class="'+cssDeleted+'">'+( sameDay ? '' : end.format(_('date.format.default')) )+'</span>',
				'<div class="'+cssDeleted+' a_r">'+( clocking.Type.WholeDay ? '' : start.format(_('time.format.time'))+' &ndash; '+end.format(_('time.format.time')) )+'</div>',
				'<div class="'+cssDeleted+' a_r">'+duration+'</div>',
				'<div class="'+cssDeleted+'"><div class="cal_type_badge" style="background:#'+clocking.Type.Color+';"></div>'+( _('clocking.type.'+clocking.Type.Identifier) || clocking.Type.Label )+'</div>',
				'<span class="'+cssDeleted+'">'+(new Date(1000 * clocking.Creationdate).format('%y-%m-%d %H:%M'))+'</span>',
				'<span class="'+cssDeleted+'">'+(new Date(1000 * clocking.LastChanged).format('%y-%m-%d %H:%M'))+'</span>',
			],
			'properties': {
				'id'   : 'clocking_'+clocking.Id,
				'class':
					'clocking'+
					( isOpen ? ' clocking_open' : '' )+
					( clocking.Booked ? ' clocking_booked' : '' )+
					( clocking.IsHoliday ? ' holiday' : '' )+
					( changed ? '' : ' changed' )
			}
		};
	}

});

initView(function (gui) {

	/* API Calls for "Clocking"
	----------------------------------------------------------- */
	ClockingAPI = {

		/**
		 * Not approved; considered valid but pending validation.
		 */
		APPROVAL_STATUS_PRELIMINARY: 0,

		/**
		 * Needs approval.
		 */
		APPROVAL_STATUS_REQUIRED   : 1,

		/**
		 * Disapproved.
		 */
		APPROVAL_STATUS_DENIED     : 2,

		/**
		 * Explicitly approved. iXML-based rules may be applied.
		 */
		APPROVAL_STATUS_CONFIRMED  : 3,

		/**
		 * Approved; use as is; do not recalculate.
		 */
		APPROVAL_STATUS_AS_IS      : 4,

		SHOW_BOOKED_ALL            : 0,
		SHOW_BOOKED_ONLY           : 1,
		SHOW_BOOKED_HIDE           : 2,

		types: function (wholeDayOnly, callback) {
			var params = {
				'api'  : 'clocking',
				'do'   : 'types'
			};

			initParam(params, 'wholedayonly', ( wholeDayOnly ? 1 : 0 ));

			gui.request(params, callback, 'array');
		},

		list: function (filter, callback) {
			var params = {
				'api'   : 'clocking',
				'do'    : 'list'
			};

			// TODO Add additional filter settings
			initParam(params, 'start', filter.start);
			initParam(params, 'end', filter.end);

			if ( filter.user )
				initParam(params, 'user', filter.user);

			initParam(params, 'domain', filter.domain);
			initParam(params, 'showdeleted', ( filter.showdeleted ? 1 : 0 ));
			initParam(params, 'showbooked', filter.showbooked);
			initParam(params, 'wholedayonly', ( filter.wholedayonly ? 1 : 0 ));
			initParam(params, 'orderby', filter.id);
			initParam(params, 'ordermode', filter.mode);

			gui.request(params, function (res) {
				callback(res);
			}, 'array');
		},

		current: function (userId, callback) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'current',
				'user'  : userId
			}, function (res) {
				callback(res);
			}, 'object');
		},

		details: function (id, callback) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'details',
				'id'    : id
			}, function (res) {
				callback(res);
			}, 'object');
		},

		update: function (id, data, callback, callOnFailure) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'update',
				'id'    : id,
				'data'  : data
			}, function (res) {
				callback(res);
			}, null, callOnFailure);
		},

		approve: function (id, status, callback, callOnFailure) {
			this.update(id, { 'ApprovalStatus': status }, callback, callOnFailure);
		},

		add: function (data, callback, callOnFailure) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'add',
				'data'  : data
			}, function (res) {
				callback(res);
			}, 'int', callOnFailure);
		},

		remove: function (id, callback, callOnFailure) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'remove',
				'id'    : id
			}, function (res) {
				callback(res);
			}, 'boolean', callOnFailure);
		},

		restore: function (id, callback) {
			gui.request({
				'api'   : 'clocking',
				'do'    : 'restore',
				'id'    : id
			}, function (res) {
				callback(res);
			}, 'boolean');
		}

	};

	ClockingManager = {

		APPROVAL_STATUS_MAP: {},

		getApprovalStatusCaption: function (status) {
			return ClockingManager.APPROVAL_STATUS_MAP[status];
		},

		approvalStatusToItem: function (status) {
			return (
				ClockingManager.APPROVAL_STATUS_MAP.hasOwnProperty(status)
				? { 'value': status, 'label': this.getApprovalStatusCaption(status) }
				: null
			);
		},

		getRange: function (clockings) {
			var min = +Infinity;
			var max = -Infinity;

			for (var i = 0; i < clockings.length; i++) {
				if ( clockings[i].Start < min )
					min = Number(clockings[i].Start);
				if ( clockings[i].End > max )
					max = Number(clockings[i].End);
			}

			var result = {};

			if ( min < +Infinity )
				result.start = min;
			if ( max > -Infinity )
				result.end = max;

			return result;
		}

	};

	ClockingManager.APPROVAL_STATUS_MAP[ClockingAPI.APPROVAL_STATUS_PRELIMINARY] = _('clocking.status.preliminary');
	ClockingManager.APPROVAL_STATUS_MAP[ClockingAPI.APPROVAL_STATUS_REQUIRED   ] = _('clocking.status.required');
	ClockingManager.APPROVAL_STATUS_MAP[ClockingAPI.APPROVAL_STATUS_DENIED     ] = _('clocking.status.denied');
	ClockingManager.APPROVAL_STATUS_MAP[ClockingAPI.APPROVAL_STATUS_CONFIRMED  ] = _('clocking.status.confirmed');
	ClockingManager.APPROVAL_STATUS_MAP[ClockingAPI.APPROVAL_STATUS_AS_IS      ] = _('clocking.status.as_is');

});
