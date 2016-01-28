var Factory = {
	/**
	 * Creates a button
	 *
	 * @param {object} options
	 * @option {string} style Button style (primary, success, etc.)
	 * @option {string} size Button size (large, mini)
	 * @option {string} icon The icon class
	 * @option {string} label The button label
	 * @returns {Element}
	 */
	Button: function (options) {
		return new Element('a', {
			'class': 'btn' + (options.style == null ? '' : ' btn-'+options.style) + (options.size == null ? '' : ' btn-'+options.size),
			'html': (options.icon == null ? '' : '<i class="icon-'+options.icon+'"></i> ') + options.label
		})
	},

	DeletedBadge: function () {
		return new Element('span', { 'class': 'm2_r label label-important', 'html': _('field.deleted') });
	},

	/**
	 * Creates a prepend frame for input elements
	 *
	 * @param {object} options
	 * @option {string} class Additional classes
	 * @option {string} icon Icon class
	 * @option {Element|object} content The content
	 * @returns {Element}
	 */
	InputPrepend: function (options) {
		return __({
			'class': 'input-prepend' + (options['class'] != null ? ' ' + options['class'] : ''),
			'children': {
				'icon': {
					'tag': 'span',
					'class': 'add-on '+( options.labelClasses ? options.labelClasses.join(' ') : '' ),
					'children': {
						'img': {'tag': 'i', 'class': 'icon-'+(options.icon != null ? options.icon : 'th')},
						'label': options.label == null ? '' : ' '+options.label
					}
				},
				'content': options.content
			}
		});
	},

	/**
	 * Creates a form collection object
	 *
	 * @returns {object}
	 */
	FormCollection: function () {
		return {
			'_exclude': ['_exclude', '_tabbox', 'reset', 'setValue', 'setValues', 'getValues', 'setHighlights', 'setTabbox'],
			'_tabbox': false,
			'reset': function () {
				for (i in this)
					if (!this._exclude.contains(i))
						this[i].reset();
			},
			'setValue': function (fieldid, value) {
				for (i in this)
					if (!this._exclude.contains(i))
						this[i].setValue(fieldid, value);
			},
			'setValues': function (values) {
				for (i in this)
					if (!this._exclude.contains(i))
						this[i].setValues(values);
			},
			'getValues': function () {
				var res = {};
				for (i in this)
					if (!this._exclude.contains(i))
						Object.append(res, this[i].getValues());

				return res;
			},
			'setHighlights': function (values, type) {
				var count;
				for (i in this) {
					if (!this._exclude.contains(i)) {
						count = this[i].setHighlights(values, type);

						if (this._tabbox && this._tabbox._tabs[i] != null) {
							if (count > 0)
								this._tabbox.setHighlight(i, count);
							else
								this._tabbox.setHighlight(i);
						}
					}
				}
			},
			'setTabbox': function (tabbox) {
				this._tabbox = tabbox;
			}
		};
	},

	FieldCollection: function () {
		return {
			'_exclude': ['_exclude', 'reset', 'setValues', 'getValues', 'setHighlights'],
			'reset': function () {
				for (i in this) {
					if (!this._exclude.contains(i)) {
						switch (typeOf(this[i])) {
							case 'element':
								switch (this[i].get('tag')) {
									case 'input':
									case 'select':
									case 'textarea':
										this[i].erase('value')
										break;
								}
								break;
							case 'object':
								if (typeOf(this[i].reset) == 'function')
									this[i].reset();
								if (typeOf(this[i].set) == 'function')
									this[i].set();
								break;
						}
					}
				}
			},
			'setValues': function (values) {
				for (i in values) {
					switch (typeOf(this[i])) {
						case 'element':
							switch (this[i].get('tag')) {
								case 'input':
								case 'select':
								case 'textarea':
									values[i] = this[i].set('value', values)
									break;
							}
							break;
						case 'object':
							if (typeOf(this[i].setValue) == 'function')
								values[i] = this[i].setValue(values);
							if (typeOf(this[i].setValues) == 'function')
								values[i] = this[i].setValues(values);
							if (typeOf(this[i].set) == 'function')
								values[i] = this[i].set(values);
							break;
					}
				}
			},
			'getValues': function () {
				var values = {};
				for (i in this) {
					if (!this._exclude.contains(i))  {
						switch (typeOf(this[i])) {
							case 'element':
								switch (i.get('tag')) {
									case 'input':
									case 'select':
									case 'textarea':
										values[i] = this[i].get('value')
										break;
								}
								break;
							case 'object':
								if (typeOf(this[i].getValue) == 'function')
									values[i] = this[i].getValue();
								if (typeOf(this[i].getValues) == 'function')
									values[i] = this[i].getValues();
								if (typeOf(this[i].get) == 'function')
									values[i] = this[i].get();
								break;
						}
					}
				}

				return values;
			},
			'setHighlights': function (fields) {

			}
		};
	}
};
