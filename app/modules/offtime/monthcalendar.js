var MonthCalendar = new Class({

	Implements: [ Options, Events ],

	options: {
		'month'      : undefined,
		'year'       : undefined,
		'startOfWeek': 1,
		'weeks'      : 6,
		'head'       : undefined,
		'table'      : undefined,
		'format'     : function (date) {
			return date.getDate();
		},
		'onClick'    : undefined
	},

	element: undefined,

	rendered: false,

	initialize: function (options) {
		this.setOptions(options);

		if ( !this.options.month || !this.options.year ) {
			var now = new Date();
			if ( this.options.month == null )
				this.options.month = now.getMonth() + 1;
			if ( this.options.year == null )
				this.options.year = now.getFullYear();
		}

		this.element = new Element('table');
		if ( this.options.table )
			this.element.set(this.options.table);
	},

	getStartDate: function (month, year, startOfWeek) {
		var startDate = new Date(year, month - 1, 1);
		var dow       = startDate.getDay();
		var delta     = dow - startOfWeek;

		if ( delta !== 0 ) {
			// Month does not start on startOfWeek.
			// Determine which day startOfWeek refers to.
			// startOfWeek = 1 Monday; dow = 3 Wednesday
			if ( delta < 0 )
				delta += 7;

			startDate.decrement('day', delta);
		}

		return startDate;
	},

	getStart: function () {
		return this.getStartDate(this.options.month, this.options.year, this.options.startOfWeek);
	},

	getEnd: function () {
		return this.getStart().increment('week', this.options.weeks);
	},

	getData: function (startDate, weeks) {
		var currentDay = new Date(startDate);

		var result = [];
		var days;

		for (var week = 0; week < weeks; week++) {
			days = [];
			for (var day = 0; day < 7; day++) {
				days.push(new Date(currentDay));
				currentDay = currentDay.increment('day', 1);
			}

			result.push(days);
		}

		return result;
	},

	renderHead: function (columns) {
		var headColumns = [];

		for (var i = 0; i < this.options.head.length; i++) {
			var headColumn = this.options.head[i];
			if ( !(headColumn instanceof Element) )
				headColumn = new Element('th', { 'html': headColumn });

			headColumns.push(headColumn);
		}

		return new Element('thead')
			.adopt(new Element('tr').adopt(headColumns));
	},

	renderCell: function (date) {
		var cell = this.options.format(date);
		if ( (cell instanceof Element) && (cell.tag === 'td') )
			return cell;

		var text;
		var cellProperties;
		if ( cell && (typeof(cell) === 'object') ) {
			text = cell.text;
			cellProperties = cell.properties;
		} else {
			text = cell;
			properties = null;
		}

		cell = new Element('td');
		if ( text && (typeof(text) === 'object') )
			cell.adopt(text);
		else
			cell.set('text', text);

		if ( cellProperties )
			cell.set(cellProperties);

		var me = this;
		return cell
			.addClass('day '+( date.getMonth() + 1 === this.options.month ? 'this' : 'other' ))
			.addEvent('click', function (event) {
				me.clickHandler(date, event, this);
			});
	},

	clickHandler: function (date, event, element) {
		event.stop();
		this.fireEvent('click', [ date, event, element, this ]);
	},

	renderBody: function (data) {
		var rows = [];

		for (var week = 0; week < data.length; week++) {
			var cells = [];
			var weekData = data[week];
			for (var day = 0; day < weekData.length; day++)
				cells.push(this.renderCell(weekData[day]));

			rows.push(new Element('tr').adopt(cells));
		}

		return new Element('tbody').adopt(rows);
	},

	render: function (month, year) {
		var tableParts = [];

		if ( this.options.head && this.options.head.length )
			tableParts.push(this.renderHead(this.options.head));

		if ( this.options.weeks ) {
			tableParts.push(this.renderBody(this.getData(
				this.getStart(),
				this.options.weeks
			)));
		}

		this.rendered = true;

		return this.element
			.empty()
			.adopt(tableParts);
	},

	destroy: function () {
		if ( this.element ) {
			this.element.destroy();
			this.element = undefined;
		}
	},

	setMonth: function (month) {
		this.options.month = month;
		this.render(this.options.month, this.options.year);
		return this;
	},

	setYear: function (year) {
		this.options.year = year;
		this.render(this.options.month, this.options.year);
		return this;
	},

	setDate: function (month, year) {
		this.options.month = month;
		this.options.year  = year;
		this.render(this.options.month, this.options.year);
		return this;
	},

	toElement: function () {
		if ( this.element && !this.rendered )
			this.render(this.options.month, this.options.year);

		return this.element;
	}

});
