(function () {

	// "initialDivisor":
	//   The number of seconds of one unit.
	// "groups":
	//   Array with number of ticks per next larger unit and their digit counts,
	//   starting with seconds.
	var DURATION_DIVISORS = {
		'seconds' : { 'initialDivisor':     1, 'groups': [ [60, 2], [60, 2], [24, 2] ] },
		'minutes' : { 'initialDivisor':    60, 'groups': [ [60, 2] ] },
		'hours'   : { 'initialDivisor':  3600, 'groups': [ ] },
		'halfdays': { 'initialDivisor': 43200, 'groups': [ ] },
		'days'    : { 'initialDivisor': 86400, 'groups': [ ] }
	};

	/**
	 * See http://stackoverflow.com/questions/661562/how-to-format-a-float-in-javascript/661757#661757:
	 * In IE (at least up to version 7), the following holds true:
	 *     (0.9).toFixed(0) === '0'
	 */
	function toFixed(value, precision) {
		if ( !precision )
			return Math.round(value);

		var power = Math.pow(10, precision);
		return Number((Math.round(value * power) / power).toFixed(precision));
	}

	Number.implement({

		convertDuration: function (fromUnit, toUnit) {
			if ( isNaN(this) )
				return this;
			else if ( !DURATION_DIVISORS[fromUnit] )
				throw new Error('Invalid source unit "'+fromUnit+'" for value '+this+'.');
			else if ( !DURATION_DIVISORS[toUnit] )
				throw new Error('Invalid target unit "'+toUnit+'" for value '+this+'.');

			return this * DURATION_DIVISORS[fromUnit].initialDivisor / DURATION_DIVISORS[toUnit].initialDivisor;
		},

		formatDuration: function (unit, omitInitialDivisor, precision) {
			var data     = DURATION_DIVISORS[unit];
			if ( !data )
				throw new Error('Invalid unit "'+unit+'".');

			var result   = [];
			var divisors = data.groups;
			var value    = toFixed(( omitInitialDivisor ? this : this / data.initialDivisor ), precision);
			var signed   = ( value < 0 );

			if ( signed )
				value = -value;

			for (var i = 0; i < divisors.length; i++) {
				var divisor   = divisors[i][0];
				var digits    = divisors[i][1];
				var remainder = value % divisor;
				value         = Math.floor(value / divisor);

				result.unshift(String(remainder).pad(digits, '0', 'left'));

				if ( value === 0 )
					break;
			}

			result.unshift(value);

			return ( signed ? '-' : '' )+result.join(':');
		},

		formatDurationFrom: function (fromUnit, toUnit) {
			var precision = (
				( (fromUnit === 'halfdays') && (toUnit === 'days') )
				? 1
				: 0
			);

			return this
				.convertDuration(fromUnit, toUnit)
				.formatDuration(toUnit, true, precision);
		}

	});

	String.implement({

		substitute: function(object, regexp){
			return this.replace(regexp || (/(\\{0,2})\{([^{}]+)\}/g), function(match, escape, name){
				if ( escape.length > 0 ) return match.slice(1);
				return (object[name] != null) ? object[name] : '';
			});
		},

		/**
		 * Converts a formatted duration string to a number where the least-significant digit is in the specified unit.
		 *
		 * For example, calling
		 * <code>
		 *     textToBookingValue('1:23', 'minutes')
		 * </code>
		 * will yield the number 83 [minutes: 1 * 60 + 23] whereas
		 * <code>
		 *     textToBookingValue('1:23', 'hours')
		 * </code>
		 * will yield 47 [hours: 1 * 24 + 23].
		 */
		toBookingValue: function (unit) {
			var indexes = {
				'seconds': 0,
				'minutes': 1,
				'hours'  : 2,
				'days'   : NaN
			};
			var factors = [ 60, 60, 24 ];
			var matches;

			var index = indexes[unit];
			if ( index == null )
				return undefined;

			var text = this;

			var signed = ( text[0] === '-' );
			if ( signed )
				text = text.substring(1, text.length);

			// dd:hh:mm:ss
			parts = text.split(':');
			if ( !parts.length )
				return undefined;

			var result = Number(parts[parts.length - 1]);
			if ( isNaN(result) )
				return undefined;

			var factor = 1;
			for (var i = parts.length - 2; i >= 0; i--) {
				var part   = ( parts[i] == '' ? 0 : Number(parts[i]) );
				if ( isNaN(part) )
					return undefined;

				factor *= factors[index++];
				result += factor * Number(part);
			}

			return ( signed ? -result : result );
		}

	});

	Array.implement({

		/**
		 * Specialized version of {@link map()} to map an object property to the object.
		 *
		 * @param {String} propertyName The name of the property to use as the key.
		 */
		reindex: function (propertyName) {
			var result = {};

			for (var i = 0; i < this.length; i++)
				result[this[i][propertyName]] = this[i];

			return result;
		}

	});

	Function.implement({

		toApiPromise: function (bind, parameters) {
			var promise = new Promise();

			parameters  = ( parameters ? parameters.clone() : [] );

			parameters.push(function (data) {
				promise.deliver(
					( data && data.result )
					? data.result
					: data
				);
			});

			this.apply(bind, parameters);

			return promise;
		}

	});

	Element.implement({

		selectAll: function () {
			var range = document.createRange();
			range.selectNodeContents(this);

			var selection = window.getSelection();
			selection.removeAllRanges();
			selection.addRange(range);
		}

	});

})();

function mergeData(items, related, callback) {
	for (var i = 0; i < items.length; i++)
		callback(items[i], related);

	return items;
}

/**
 * Rounds a decimal value
 * @param {Number} num
 * @returns {Number}
 */
function roundDec(num) {
	return Math.round(num * 100) / 100;
}

/**
 * Parses a decimal number string with base 10
 *
 * @param {String} num
 * @returns {Number}
 */
function parseDec(num) {
	return parseFloat(num, 10);
}

/**
 * Parses an integer string with base 10
 *
 * @param {String} num
 * @returns {Int}
 */
function parseB10(num) {
	return parseInt(num, 10);
}

/**
 * Add a zero "0" at the beginning of a number, in case it's below 10
 *
 * @param {Number} num
 * @returns {String}
 */
function addZero(num) {
	return num < 10 ? ('0' + num) : num;
}

/**
 * Returns a formatted time string to express a number of minutes in hours
 *
 * See also {@link Number.formatDuration()}.
 *
 * @param {Number} mins Number of minutes
 * @returns {String} Formatted string HH:MM
 */
function formatTime(mins) {
	var prefix = '';
	if ( mins == null )
		return '0:00';
	if ( mins < 0 ) {
		mins = -mins;
		prefix = '-';
	}
	var timeInMinutes = Math.round(mins);
	var minutes = timeInMinutes % 60;
	var hours = Math.floor(timeInMinutes / 60);
	return prefix + hours + ':' + addZero(minutes) + 'h';
}

/**
 * Returns the number of weeks in a given year
 *
 * @param {Int} ts Timestamp in seconds
 * @returns {Number} Number of weeks
 */
function getWeek(ts) {
	var a = new Date(ts * 1000);
	var b = new Date(a.getYear(), 1, 1);
	return Math.floor((a.UTC() - b.UTC()) / 604800000);
}

/**
 * Converts a JavaScript object to a format that is suitable for AJAX requests.
 *
 * For example, booleans will be transformed to 1 / 0 values, and dates are
 * converted to UNIX timestamp strings. Objects will be traversed recursively.
 *
 * @param {Object} value
 * @returns The cloned and transformed object
 * @type Object
 */
function toRequestValue(value) {
	if ( value == null )
		return '';

	switch ( typeof(value) ) {
		case 'boolean':
			return ( value ? '1' : '0' );

		case 'object':
			if ( value instanceof Date ) {
				return value.format('%s');
			} else if ( typeOf(value) === 'array' ) {
				value = Array.clone(value);
				for (var i = 0; i < value.length; i++)
					value[i] = toRequestValue(value[i]);
				return value;
			} else {
				value = Object.clone(value);
				for (var i in value) {
					if ( value.hasOwnProperty(i) )
						value[i] = toRequestValue(value[i]);
				}
				return value;
			}

		default:
			return String(value);
	}
}

/**
 * Initializes a conditional parameter. Especially handy when working with requests
 *
 * @param {Object} target The target object (usually the request data object)
 * @param {String} key The key, in case the value could be initalized
 * @param {mixed} value The value
 */
function initParam(target, key, value) {
	if ( value != null )
		target[key] = toRequestValue(value);
}

function getColorLabel(value1, value2) {
	if ( value1 == null || value2 == null || value2 == 0 )
		return '';

	var color = 'grey';
	if ( value1 > value2 )
		color = 'red';
	else if ( value1 < value2 )
		color = 'green';

	if ( Math.abs(value2) < 1 ) {
		var sign = '';
		if ( value2 < 0 ) {
			value2 = -value2;
			sign = '-';
		}
		seconds = Math.round(value2 * 60);
		return '<span class="bold ' + color + '">' + '[' + sign + seconds + 's]</span>'
	} else {
		return '<span class="bold ' + color + '">[' + formatTime(value2) + ']</span>';
	}
}

function getFullName(user) {
	if ( user == null )
		return null;

	var firstName = user.Firstname;
	if ( firstName == null )
		firstName = '';
	var lastName  = user.Lastname;
	if ( lastName == null )
		lastName = '';

	var fullName = (firstName+' '+lastName).trim();
	return ( fullName == '' ? user.Name : fullName );
}

var deselect;
if ( (typeof(window.getSelection) === 'function') &&
     (typeof(window.getSelection().removeAllRanges) === 'function') ) {
	deselect = function () {
		window.getSelection().removeAllRanges();
	};
} else {
	deselect = function () {
		document.selection.empty();
	};
}
