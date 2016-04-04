/**
 * @class gx.zfx.Slider
 * @description Creates a slider
 * @extends gx.zfx.DefaultField
 * @implements gx.ui.HGroup
 *
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @version 1.00
 * @package com
 * @copyright Copyright (c) 2011-2013, Zeyon (www.zeyon.net)
 *
 * @option {string} unit
 * @option {object} content
 * @option {int} min The minimum value
 * @option {int} max The maximum value
 * @option {int} stepsize The size of a single step
 * @option {int} factor
 * @option {int} precision The precision of measurement
 * @option {int} value
 *
 * @event resize
 * @event blur
 * @event keypress
 *
 * @sample Slider
 */
gx.com.Slider = new Class({
	Extends: gx.ui.Container,

	options: {
		'input'       : true,
		'inputWidth'  : 50,
		'width'       : 300,
		'unit'        : '',
		'content'     : false,
		'min'         : 0,
		'max'         : 200,
		'stepsize'    : 1,
		'factor'      : 1,
		'precision'   : 2,
		'value'       : 0,

		'compare'     : null,

		'onChange'    : null
	},

	initialize: function(display, options) {
		var root = this;

		this.parent(display, options);

		this._ui.text = new Element('input', {
			'type'      : 'text',
			'styles'    : {
				'width' : this.options.inputWidth,
				'margin': '1px 0px 0px 15px'
			},
			'value'     : this.options.value
		});

		if ( !this.options.compare )
			this.options.compare = this.compare.bind(this);

		this._ui.frame = new Element('div', { 'class': 'slider' });
		this._ui.bar   = new Element('div', { 'class': 'bar' });
		this._ui.knob  = new Element('div', { 'class': 'knob' });
		this._ui.frame.adopt(this._ui.bar, this._ui.knob);

		this._ui.frame.setStyle('width', this.options.width);

		this._ui.root.adopt(
			this._ui.frame
		);

		if ( this.options.input )
			this._ui.root.adopt(
				this._ui.text
			);

		this._ui.text.addEvent('keypress', function(event) {
			if (event.key == 'enter')
				root.setValue(parseFloat(root._ui.text.get('value'), root.options.precision));
			if (event.key == ',')
				return '.';
			if (event.key.match(/[0-9\.]/))
				return event.key;

			return undefined;
		});
		this._ui.text.addEvent('blur', function() {
			root.setValue(parseFloat(root._ui.text.get('value'), root.options.precision));
		});
	},

	/**
	 * Compares two values and checks if they can be considered equivalent.
	 *
	 * Values are assumed to be equal to the default setting if it differs by
	 * less than a quarter of the step size to account for rounding errors.
	 *
	 * @param {Number} a
	 * @param {Number} b
	 * @returns Returns true if the two values are similar enough to be
	 *     considered equivalent, otherwise false.
	 * @type Boolean
	 */
	compare: function (a, b) {
		return ( Math.abs(a - b) < 0.25 * this.options.stepsize );
	},

	/**
	 * @method initSlider
	 * @description Initializes the slider to the values in this.options
	 */
	initSlider: function() {
		if ( this._slider != null )
			return this;

		var root = this;

		this._slider = new Slider(this._ui.frame, this._ui.knob, {
			'range'      : [ this.options.min * this.options.factor, this.options.max * this.options.factor ],
			'steps'      : (this.options.max - this.options.min) / this.options.stepsize,
			'initialStep': this.options.value * this.options.factor,
			'wheel'      : true,
			'snap'       : true,
			'mode'       : 'horizontal'
		});

		this._slider.addEvent('change', function(step) {
			var value = (step / root.options.factor);

			root._ui.bar.setStyle('width', ((value * 100)/root.options.max)+'%' );
			root._ui.text.set('value', value);
			root.fireEvent('change', [ root ]);
		});

		window.addEvent('resize', function() {
			root._slider.autosize();
		});

		return this;
	},

	/**
	 * @method setValue
	 * @description Sets the value of the slider
	 * @param {int} value The value to set
	 */
	setValue: function(value) {
		if (value < this.options.min)
			value = this.options.min;
		if (value > this.options.max)
			value = this.options.max;

		this._slider.set(value * this.options.factor);
		this._ui.text.set('value', value);
	},

	/**
	 * @method getValue
	 * @description Returns the value
	 */
	getValue: function() {
		return this._ui.text.get('value');
	}

});
