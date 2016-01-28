if (!Array.isArray)
	Array.isArray = function(pValue) {
		return Object.prototype.toString.call(pValue) == '[object Array]';
	};

if (!Array.prototype.filter)
	Array.prototype.filter = function(pFunction, pThis) {
		var New = [];

		for (var I = 0, L = this.length; I < L; I++)
			if (I in this) {
				var Value = this[I];

				if (pFunction.call(pThis, Value, I, this))
					New.push(Value);
			}

		return New;
	};

if (!Array.prototype.forEach)
	Array.prototype.forEach = function(pFunction, pThis) {
		for (var I = 0, L = this.length; I < L; I++)
			if (I in this)
				pFunction.call(pThis, this[I], I, this);
	};

if (!Array.prototype.map)
	Array.prototype.map = function(pFunction, pThis) {
		var L = this.length;
		var New = new Array(L);

		for (var I = 0; I < L; I++)
			if (I in this)
				New[I] = pFunction.call(pThis, this[I], I, this);

		return New;
	};

if (!Array.prototype.every)
	Array.prototype.every = function(pFunction, pThis) {
		for (var I = 0, L = this.length; I < L; I++)
			if (I in this && !pFunction.call(pThis, this[I], I, this))
				return false;

		return true;
	};

if (!Array.prototype.some)
	Array.prototype.some = function(pFunction, pThis) {
		for (var I = 0, L = this.length; I < L; I++)
			if (I in this && pFunction.call(pThis, this[I], I, this))
				return true;

		return false;
	};

if (!Object.keys)
	Object.keys = function(pObj) {
		var Keys = [];

		for (var Key in pObj)
			if (pObj.hasOwnProperty(Key))
				Keys.push(Key);

		return Keys;
	};

if (!String.prototype.trim)
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};

Date.prototype.getDaysInMonth = function() {
	return new Date(this.getFullYear(), this.getMonth() + 1, 0).getDate();
};

Date.prototype.getDayISO = function() {
	return this.getDay() || 7;
};

Date.prototype.getWeekISO = function() {
	var Week = new Date(this.getFullYear(), this.getMonth(), this.getDate() - this.getDayISO() + 3);
	return 1 + ((Week - new Date(Week.getFullYear(), 0, 4)) / 86400000 / 7).toFixed();
};

function $(pElem) {
	if (pElem) {
		var Type = typeof pElem;

		if (Type != 'object' && Type != 'function' /* FIX Firefox */)
			pElem = document.getElementById(pElem);

		if (pElem && !pElem._) {
			var LocalDOM = DOM;
			pElem._            = LocalDOM.getElem_addBottom;
			pElem.addEvent     = LocalDOM.getElem_addEvent;
			pElem.removeEvent  = LocalDOM.getElem_removeEvent;
			pElem.triggerEvent = LocalDOM.getElem_triggerEvent;
			pElem.setAttr      = LocalDOM.getElem_setAttr;
			pElem.hasClass     = LocalDOM.getElem_hasClass;
			pElem.addClass     = LocalDOM.getElem_addClass;
			pElem.removeClass  = LocalDOM.getElem_removeClass;
			pElem.toggleClass  = LocalDOM.getElem_toggleClass;
			pElem.swapClass    = LocalDOM.getElem_swapClass;
			pElem.display      = LocalDOM.getElem_display;
			pElem.setHtml      = LocalDOM.getElem_setHtml;
			pElem.setText      = LocalDOM.getElem_setText;
			pElem.clear        = LocalDOM.getElem_clear;
			pElem.addTop       = LocalDOM.getElem_addTop;
			pElem.addBottom    = LocalDOM.getElem_addBottom;
			pElem.insertPrev   = LocalDOM.getElem_insertPrev;
			pElem.insertNext   = LocalDOM.getElem_insertNext;
			pElem.removeSelf   = LocalDOM.getElem_removeSelf;
			pElem.replaceSelf  = LocalDOM.getElem_replaceSelf;
			pElem.hasChild     = LocalDOM.getElem_hasChild;
			pElem.getParent    = LocalDOM.getElem_getParent;
			pElem.getChild     = LocalDOM.getElem_getChild;
			pElem.getFirst     = LocalDOM.getElem_getFirst;
			pElem.getLast      = LocalDOM.getElem_getLast;
			pElem.getPrev      = LocalDOM.getElem_getPrev;
			pElem.getNext      = LocalDOM.getElem_getNext;
			pElem.getClone     = LocalDOM.getElem_getClone;

			switch (pElem.nodeName) {
				case 'FORM':
					pElem.addHidden     = LocalDOM.getElem_addHidden;
					pElem.getElem       = LocalDOM.getElem_getElem;
					pElem.getChecked    = LocalDOM.getElem_getChecked;
					pElem.getCheckedAll = LocalDOM.getElem_getCheckedAll;
					pElem.buildAttr     = LocalDOM.getElem_buildAttr;
					break;

				case 'SELECT':
					pElem.addOption       = LocalDOM.getElem_addOption;
					pElem.addOptionList   = LocalDOM.getElem_addOptionList;
					pElem.addOptionValues = LocalDOM.getElem_addOptionValues;
					pElem.addOptionPairs  = LocalDOM.getElem_addOptionPairs;
					pElem.getSelectedAll  = LocalDOM.getElem_getSelectedAll;
					break;

				case 'TEXTAREA':
					pElem.insertText = LocalDOM.getElem_insertText;
					break;
			}
		}
	}

	return pElem;
}

function $$(pSelector, pRoot /* default: document */) {
	if (!pRoot)
		pRoot = document;

	return Array.isArray(pSelector) ? pRoot.querySelectorAll(pSelector[0]) : $(pRoot.querySelector(pSelector));
}

function __(pTag /* format: tag.class#id */, pAttrs /* optional */) {
	var Class = '',
			Id    = '';

	var Index = pTag.indexOf('#');

	if (Index != -1) {
		Id = pTag.substr(Index + 1);
		pTag = pTag.substr(0, Index);
	}

	Index = pTag.indexOf('.');

	if (Index != -1) {
		Class = pTag.substr(Index + 1);
		pTag = pTag.substr(0, Index);
	}

	var Elem = $(document.createElement(pTag));

	if (Id != '')
		Elem.id = Id;

	if (Class != '')
		Elem.className = Class;

	if (pAttrs) {
		Elem.setAttr(pAttrs);

		if ('defaultChecked' in Elem && pAttrs.checked)
			Elem.defaultChecked = true;

		if ('defaultValue' in Elem) {
			var Value = pAttrs.value;

			if (UTL.isSet(Value))
				Elem.defaultValue = Value;
		}
	}

	return Elem;
}

var DOM = {
	getElem: function(pElem) {
		return $(pElem);
	},

	getElem_addEvent: function(pType, pHandler) {
		DOM.addEvent(this, pType, pHandler);
		return this;
	},

	getElem_removeEvent: function(pType, pHandler) {
		DOM.removeEvent(this, pType, pHandler);
		return this;
	},

	getElem_triggerEvent: function(pType) {
		return DOM.triggerEvent(this, pType);
	},

	getElem_setAttr: function(pName, pValue) {
								// function(pAttrs)

		if (typeof pName == 'object') {
			for (var Name in pName)
				this.setAttr(Name, pName[Name]);
		} else if (UTL.isSet(pValue))
			if (pName[0] == '_')
				this.style[pName.substr(1)] = pValue;
			else if (pName.indexOf('on') == 0)
				this.addEvent(pName.substr(2), pValue);
			else if (typeof pValue == 'boolean' || '|dir|href|id|lang|name|src|target|title|value|'.indexOf('|' + pName + '|') != -1)
				this[pName] = pValue;
			else
				this.setAttribute(pName, pValue);

		return this;
	},

	getElem_hasClass: function(pClass) {
		if (pClass == '')
			return false;

		var List = pClass.split(' ');

		for (var I = List.length; I--;) {
			var Token     = List[I],
					ClassList = this.classList;

			if (ClassList) {
				if (!ClassList.contains(Token))
					return false;
			} else if (!new RegExp('(^| )' + Token + '( |$)').test(this.className))
				return false;
		}

		 return true;
	},

	getElem_addClass: function(pClass) {
		if (pClass != '')
			this.className += ' ' + pClass;

		return this;
	},

	getElem_removeClass: function(pClass) {
		if (pClass != '') {
			var List = pClass.split(' ');

			for (var I = List.length; I--;) {
				var Token     = List[I],
						ClassList = this.classList;

				if (ClassList)
					ClassList.remove(Token);
				else
					this.className = this.className.replace(new RegExp('(?:^| )' + Token + '(?= |$)', 'g'), '');
			};
		}

		return this;
	},

	getElem_toggleClass: function(pClass) {
		if (pClass != '') {
			var List = pClass.split(' ');

			for (var I = List.length; I--;) {
				var Token     = List[I],
						ClassList = this.classList;

				if (ClassList)
					ClassList.toggle(Token);
				else {
					var Class = this.className;

					if (new RegExp('(^| )' + Token + '( |$)').test(Class))
						this.className = Class.replace(new RegExp('(?:^| )' + Token + '(?= |$)', 'g'), '')
					else
						this.className += ' ' + Token;
				}
			}
		}

		return this;
	},

	getElem_swapClass: function(pClassOld, pClassNew) {
		return this.removeClass(pClassOld).addClass(pClassNew);
	},

	getElem_display: function(pDisplay /* default: toggle */) {
		if (!UTL.isSet(pDisplay))
			pDisplay = this.style.display != '';

		this.style.display = pDisplay ? '' : 'none';
		return this;
	},

	getElem_setHtml: function(pHtml) {
		this.innerHTML = pHtml;
		return this;
	},

	getElem_setText: function(pText, pUrlToLink /* default: false */) {
		this.innerHTML = UTL.htmlcode(pText, pUrlToLink);
		return this;
	},

	getElem_clear: function() {
		this.innerHTML = '';
		return this;
	},

	getElem_addTop: function(pChild) {
		if (UTL.isSet(pChild)) {
			pChild = DOM.getElemOrNewText(pChild);

			if (this.hasChildNodes())
				this.insertBefore(pChild, this.firstChild);
			else
				this.appendChild(pChild);
		}

		return this;
	},

	getElem_addBottom: function(pChild) {
		if (UTL.isSet(pChild))
			this.appendChild(DOM.getElemOrNewText(pChild));

		return this;
	},

	getElem_insertPrev: function(pPrev) {
		if (UTL.isSet(pPrev))
			this.parentNode.insertBefore(DOM.getElemOrNewText(pPrev), this);

		return this;
	},

	getElem_insertNext: function(pNext) {
		if (UTL.isSet(pNext)) {
			var Parent = this.parentNode,
					Next   = this.nextSibling;

			pNext = DOM.getElemOrNewText(pNext);

			if (Next)
				Parent.insertBefore(pNext, Next);
			else
				Parent.appendChild(pNext);
		}

		return this;
	},

	getElem_removeSelf: function() {
		this.parentNode.removeChild(this);
	},

	getElem_replaceSelf: function(pElem) {
		pElem = DOM.getElemOrNewText(pElem);
		this.parentNode.replaceChild(pElem, this);
		return pElem;
	},

	getElem_hasChild: function(pChild) {
		return this.contains ? this.contains(pChild) : !!(this.compareDocumentPosition(pChild) & 16);
	},

	getElem_getParent: function(pOffset /* default: false */) {
		return $(pOffset ? this.offsetParent : this.parentNode);
	},

	getElem_getChild: function(pIndex) {
		return $(this.childNodes[pIndex]);
	},

	getElem_getFirst: function() {
		return $(this.firstChild);
	},

	getElem_getLast: function() {
		return $(this.lastChild);
	},

	getElem_getPrev: function() {
		return $(this.previousSibling);
	},

	getElem_getNext: function() {
		return $(this.nextSibling);
	},

	getElem_getClone: function() {
		var Clone = $(this.cloneNode(false));

		var EventList = this.EventList;

		if (EventList)
			for (var Type in EventList) {
				var Events = EventList[Type];

				for (var I = 0, L = Events.length; I < L; I++)
					Clone.addEvent(Type, Events[I]);
			}

		for (var Child = this.firstChild; Child; Child = Child.nextSibling)
			Clone.appendChild(Child.nodeType == 1 ? $(Child).getClone() : document.createTextNode(Child.nodeValue));

		return Clone;
	},

	getElem_addHidden: function(pName, pValue /* optional */) {
											// function(pAssoc)

		if (typeof pName == 'object') {
			for (var Name in pName)
				this.addHidden(Name, pName[Name]);

			return this;
		}

		if (Array.isArray(pValue)) {
			for (var I = 0, L = pValue.length; I < L; I++)
				this.addHidden(pName, pValue[I]);

			return this;
		}

		return this._(__('input', {name: pName, type: 'hidden', value: pValue}));
	},

	getElem_getElem: function(pName) {
		return $(this.elements[pName]);
	},

	getElem_getChecked: function(pName) {
		var Input = this.elements[pName];

		if (Input) {
			var L = Input.length;

			if (typeof L == 'number')
				for (var I = 0; I < L; I++) {
					var Elem = Input[I];

					if (Elem.checked)
						return Elem.value;
				}
			else if (Input.checked)
				return Input.value;
		}

		return '';
	},

	getElem_getCheckedAll: function(pName) {
		var Input = this.elements[pName];

		if (Input) {
			var L = Input.length;

			if (typeof L == 'number') {
				var Values = [];

				for (var I = 0; I < L; I++) {
					var Elem = Input[I];

					if (Elem.checked)
						Values.push(Elem.value);
				}

				return Values;
			}

			if (Input.checked)
				return [Input.value];
		}

		return [];
	},

	getElem_buildAttr: function(pAttr /* optional */) {
		var Attr = '';

		for (var I = 0, Elems = this.elements, L = this.length; I < L; I++) {
			var Elem = Elems[I];

			if ('name' in Elem) {
				var Name = Elem.name;

				if (Name != '')
					switch (Elem.type) {
						case 'file':
							break;

						case 'select-multiple':
							Name = encodeURIComponent(Name + '[]');

							var Values = $(Elem).getSelectedAll();

							for (var J = 0, K = Values.length; J < K; J++)
								Attr += '&' + Name + '=' + encodeURIComponent(Values[J]);

							break;

						case 'checkbox':
						case 'radio':
							if (!Elem.checked)
								break;

						default:
							Attr += '&' + encodeURIComponent(Name) + '=' + encodeURIComponent(Elem.value);
					}
			}
		}

		if (UTL.isSet(pAttr))
			Attr += UTL.buildAttr(pAttr);

		return Attr;
	},

	getElem_addOption: function(pText, pValue /* optional */, pSelected /* default: false */) {
		return this._(__('option', {selected: pSelected, value: UTL.isSet(pValue) ? pValue : ''})._(pText));
	},

	getElem_addOptionList: function(pList, pSelValue) {
		for (var I = 0, L = pList.length; I < L; I++)
			this.addOption(pList[I], I, I == pSelValue);

		return this;
	},

	getElem_addOptionValues: function(pValues, pSelValue) {
		if (Array.isArray(pValues))
			for (var I = 0, L = pValues.length; I < L; I++) {
				var Value = pValues[I];
				this.addOption(Value, Value, Value == pSelValue);
			}
		else
			for (var Value in pValues)
				this.addOption(pValues[Value], Value, Value == pSelValue);

		return this;
	},

	getElem_addOptionPairs: function(pPairs, pSelValue) {
		for (var I = 0, L = pPairs.length; I < L; I++) {
			var Item = pPairs[I];
			this.addOption(Item[1], Item[0], Item[0] == pSelValue);
		}

		return this;
	},

	getElem_getSelectedAll: function() {
		var Values = [];

		for (var I = 0, Options = this.options, L = this.length; I < L; I++) {
			var Elem = Options[I];

			if (Elem.selected)
				Values.push(Elem.value);
		}

		return Values;
	},

	getElem_insertText: function(pText) {
		this.focus();

		if (typeof this.selectionStart == 'number') {
			var Start = this.selectionStart,
					End   = this.selectionEnd;

			this.value = this.value.substr(0, Start) + pText + this.value.substr(End);

			this.selectionStart = Start;
			this.selectionEnd   = Start + pText.length;
		} else if (document.selection)
			document.selection.createRange().text = pText;
		else
			this.value += pText;
	},

	getElemOrNewText: function(pElem) {
		var Type = typeof pElem;
		return Type == 'object' || Type == 'function' ? pElem : document.createTextNode(pElem);
	},

	selElem: function(pSelector, pRoot /* default: document */) {
		return $$(pSelector, pRoot);
	},

	newElem: function(pTag /* format: tag.class#id */, pAttrs /* optional */) {
		return __(pTag, pAttrs);
	},

	newText: function(pText) {
		return document.createTextNode(pText);
	},

	newFragment: function() {
		var Fragment = document.createDocumentFragment();

		var LocalDOM = DOM;
		Fragment._         = LocalDOM.getElem_addBottom;
		Fragment.addTop    = LocalDOM.getElem_addTop;
		Fragment.addBottom = LocalDOM.getElem_addBottom;
		Fragment.getChild  = LocalDOM.getElem_getChild;
		Fragment.getFirst  = LocalDOM.getElem_getFirst;
		Fragment.getLast   = LocalDOM.getElem_getLast;

		return Fragment;
	},

	addScript: function(pSrc, pHandler /* optional */) {
		var Script = __('script', {async: true, charset: 'UTF-8', src: pSrc});

		if (pHandler)
			if (Script.readyState && Script.onload !== null) {
				Script.onreadystatechange = function() {
					var State = this.readyState;

					if (State == 'loaded' || State == 'complete') {
						this.onreadystatechange = null;
						pHandler();
					}
				};
			} else
				Script.onload = function() {
					this.onload = null;
					pHandler();
				};

		DOM.getHead()._(Script);
	},

	addEvent: function(pTarget, pType, pHandler) {
		var EventList = pTarget.EventList;

		if (!EventList) {
			EventList = {};
			pTarget.EventList = EventList;
		}

		var Events = EventList[pType];

		if (Events)
			Events.push(pHandler);
		else
			EventList[pType] = [pHandler];

		pTarget['on' + pType] = DOM.handleEvent;
	},

	handleEvent: function(pEvent) {
		if (!pEvent)
			pEvent = event;

		var Type = pEvent.type;

		if (Type.indexOf('key') == 0) {
			var Target = pEvent.which;

			if (typeof Target != 'number') // FIX IE
				Target = pEvent.keyCode;
		} else {
			var Target = $(pEvent.target);

			if (!Target) // FIX IE
				Target = $(pEvent.srcElement);
		}

		var Events = this.EventList[Type];

		for (var I = Events.length; I--;) {
			var Handler = Events[I];

			if (Handler == false || Handler.call(this, pEvent, Target) == false) {
				if (pEvent.stopPropagation)
					pEvent.stopPropagation();
				else
					pEvent.cancelBubble = true;

				return false;
			}
		}
	},

	removeEvent: function(pTarget, pType, pHandler)  {
		var Events = pTarget.EventList[pType];

		for (var I = Events.length; I--;)
			if (Events[I] == pHandler) {
				Events.splice(I, 1);
				break;
			}

		if (Events.length == 0)
			pTarget['on' + pType] = null;
	},

	triggerEvent: function(pTarget, pType) {
		var EventList = pTarget.EventList;

		if (EventList) {
			var Events = EventList[pType];

			if (Events) {
				var Target = pType.indexOf('key') == 0 ? -1 : pTarget;

				for (var I = Events.length; I--;) {
					var Handler = Events[I];

					if (Handler == false || Handler.call(pTarget, null, Target) == false)
						return false;
				}
			}
		}

		return true;
	},

	getBody: function() {
		return $(document.body);
	},

	getHead: function() {
		return $(document.getElementsByTagName('head')[0]);
	},

	getPageHeight: function() {
		return document.documentElement.scrollHeight;
	},

	getPageWidth: function() {
		return document.documentElement.scrollWidth;
	},

	getWinHeight: function() {
		return typeof innerHeight == 'number' ? innerHeight : document.documentElement.clientHeight;
	},

	getWinWidth: function() {
		return typeof innerWidth == 'number' ? innerWidth : document.documentElement.clientWidth;
	},

	getScrollLeft: function() {
		return typeof pageXOffset == 'number' ? pageXOffset : document.documentElement.scrollLeft;
	},

	getScrollTop: function() {
		return typeof pageYOffset == 'number' ? pageYOffset : document.documentElement.scrollTop;
	}
};

var UTL = {
	isSet: function(pValue) {
		return typeof pValue != 'undefined' && pValue != null;
	},

	isNum: function(pValue) {
		return typeof pValue == 'number' || /^[+-]?\d+(\.\d+)?$/.test(pValue);
	},

	arrayToObject: function(pValue, pNullOnEmpty /* default: false */) {
		if (Array.isArray(pValue)) {
			var L = pValue.length;

			if (L == 0)
				return pNullOnEmpty ? null : {};

			var Obj = {};

			for (var I = 0; I < L; I++)
				Obj[I] = I;

			return Obj;
		}

		if (pNullOnEmpty) {
			for (var Key in pValue)
				if (pValue.hasOwnProperty(Key))
					return pValue;

			return null;
		}

		return pValue;
	},

	flipArray: function(pArray) {
		var Flip = {};

		for (var I = 0, L = pArray.length; I < L; I++)
			Flip[pArray[I]] = I;

		return Flip;
	},

	xmlcode: function(pValue) {
		return ('' + pValue).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	},

	htmlcode: function(pValue, pUrlToLink /* default: false */) {
		pValue = UTL.xmlcode(pValue);

		if (pUrlToLink)
			pValue = pValue
				.replace(/(^|\s)(www\.\S)/gi, '$1http://$2')
				.replace(/((?:\.\.?|[a-z\d.]+:\/)\/(?:[a-z\d_\-=$%:,;~@#?.+\/]|&amp;)+)/gi, '<a href="$1" target="_blank">$1</a>')
			;

		return pValue
			.replace(/\r\n|\r|\n/g, '<br>')
			.replace(/\t/g, ' ').replace(/  /g, '&nbsp; ').replace(/  /g, ' &nbsp;')
		;
	},

	htmldecode: function(pValue, pBreak /* default: true */) {
		pValue += '';

		var Match = /<body\b[\s\S]*<\/body>/i.exec(pValue);

		if (Match)
			pValue = Match[0];

		pValue = pValue.replace(/&nbsp;|\s+/g, ' ');

		if (!UTL.isSet(pBreak) || pBreak)
			pValue = pValue.replace(/<(?:br\s*\/?|\/p)>\s*/gi, '\n');

		return pValue
			.replace(/<!--[\s\S]*?-->/gi, '')
			.replace(/<\/?([a-z][a-z\d]*)\b[^>]*>/gi, '')
			.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ').replace(/&quot;/g, '"').replace(/&#0*39;/g, "'").replace(/&amp;/g, '&')
		;
	},

	trimText: function(pText, pMaxLength /* default: 255 */) {
		if (!UTL.isSet(pMaxLength))
			pMaxLength = 255;

		pText = ('' + pText).trim();

		if (pText.length > pMaxLength)
			pText = pText.substr(0, pMaxLength);

		return pText.trim();
	},

	buildAttr: function(pParams) {
		if (typeof pParams != 'object')
			return pParams;

		var Attr = '';

		for (var Name in pParams) {
			var Value = pParams[Name];

			if (UTL.isSet(Value))
				if (Array.isArray(Value)) {
					var L = Value.length;

					if (L == 0)
						Attr += '&' + encodeURIComponent(Name) + '=';
					else {
						Name = encodeURIComponent(Name + '[]');

						for (var I = 0; I < L; I++)
							Attr += '&' + Name + '=' + encodeURIComponent(Value[I]);
					}
				} else if (typeof Value == 'object')
					for (var Key in Value)
						Attr += '&' + encodeURIComponent(Name + '[' + Key + ']') + '=' + encodeURIComponent(Value[Key]);
				else
					Attr += '&' + encodeURIComponent(Name) + '=' + encodeURIComponent(Value);
		}

		return Attr;
	},

	go: function(pUrl) {
		location.href = pUrl;
	},

	goBlank: function(pUrl) {
		return open(pUrl, '_blank');
	},

	json: function(pValue) {
		return window.JSON ? JSON.parse(pValue) : eval('(' + pValue + ')');
	},

	request: function(pUrl, pAttr, pHandler /* optional */) {
		var HttpReq = new XMLHttpRequest();

		if (pHandler)
			HttpReq.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200)
					pHandler(this.responseText);
			};

		/*if (pAttr.length < 1000) {
			HttpReq.open('GET', pUrl + '?' + pAttr, true);
			HttpReq.send(null);
		} else {
			HttpReq.open('POST', pUrl, true);
			HttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			HttpReq.send(pAttr);
		}*/
	HttpReq.open('POST', pUrl, true);
	HttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	HttpReq.send(pAttr);

		return HttpReq;
	}
};

var FMT = {
	num: function(pNumber, pCountDec /* default: 0 */) {
		if (!UTL.isSet(pCountDec))
			pCountDec = 0;

		if (pCountDec != -1 && UTL.isNum(pNumber))
			pNumber = (+pNumber).toFixed(pCountDec);

		var List = ('' + pNumber).split('.');

		if (List[0].length > 3)
			List[0] = List[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, C.fmt[1]);

		return List.join(C.fmt[0]);
	},

	duration: function(pDuration) {
		var Fraction = Math.round(pDuration % 60);

		if (Fraction < 10)
			Fraction = '0' + Fraction;

		return Math.floor(pDuration / 60) + ':' + Fraction;
	},

	size: function(pSize, pCountDec /* default: 1 */) {
		if (!UTL.isSet(pCountDec))
			pCountDec = 1;

		var Unit = 'Byte';

		if (Math.abs(pSize) < 1000)
			pCountDec = 0;
		else {
			pSize /= 1024;

			if (Math.abs(pSize) < 1000)
				Unit = 'KB';
			else {
				pSize /= 1024;

				if (Math.abs(pSize) < 1000)
					Unit = 'MB';
				else {
					pSize /= 1024;

					if (Math.abs(pSize) < 1000)
						Unit = 'GB';
					else {
						pSize /= 1024;
						Unit = 'TB';
					}
				}
			}
		}

		return FMT.num(pSize, pCountDec) + ' ' + Unit;
	},

	version: function(pVersion) {
		return (pVersion / 1000).toFixed(3);
	},

	licenseKey: function(pLicenseKey) {
		return pLicenseKey.toUpperCase().match(/.{0,8}/g).join(' ').trim();
	},

	name: function(pLastName, pFirstName, pLinear /* default: false */) {
		if (pLastName == null)
			pLastName = '';
		else
			pLastName += '';

		if (pFirstName == null)
			pFirstName = '';
		else
			pFirstName += '';

		if (pFirstName == '')
			return pLastName;

		if (pLastName == '')
			return pFirstName;

		return pLinear ? pFirstName + ' ' + pLastName : pLastName + ', ' + pFirstName;
	},

	salutation: function(pLastName, pFirstName, pTitle /* optional */) {
		var List = [];

		if (UTL.isSet(pTitle) && pTitle != '')
			List.push(pTitle);

		if (pFirstName != null && pFirstName != '')
			List.push(pFirstName);

		if (pLastName != null && pLastName != '')
			List.push(pLastName);

		return List.join(' ');
	},

	url: function(pUrl, pMaxLength /* default: 60 */) {
		if (!UTL.isSet(pMaxLength))
			pMaxLength = 60;

		pUrl += '';

		if (pUrl.length <= pMaxLength)
			return pUrl;

		if (pMaxLength > 4) {
			var Offset = Math.floor(pMaxLength / 4);
			return pUrl.substr(0, pMaxLength - Offset) + '…' + pUrl.substr(-Offset)
		}

		return pUrl.substr(0, pMaxLength) + '…';
	},

	email: function(pEmail, pPersonal) {
		if (pEmail == null || pEmail == '')
			return '';

		if (pPersonal == null || pPersonal == '')
			return pEmail;

		pPersonal += '';

		if (/["<>]/.test(pPersonal))
			pPersonal = '"' + pPersonal.replace(/"/g, '\\"') + '"';

		return pPersonal + ' <' + pEmail + '>';
	},

	dateTimeFull: function(pDate) {
		return DT.format(pDate, C.dt.fmt);
	},

	dateTimeDisp: function(pDate) {
		return DT.format(pDate, C.dt.fmtdisp);
	},

	dateTimeShort: function(pDate) {
		pDate = DT.getDate(pDate);
		return pDate >= DT.mkDate() && pDate < DT.mkDate(null, 1) ? FMT.time(pDate) : FMT.date(pDate);
	},

	date: function(pDate) {
		return DT.format(pDate, C.dt.fmtdate);
	},

	time: function(pDate) {
		return DT.format(pDate, C.dt.fmttime);
	},

	month: function(pDate) {
		return DT.format(pDate, C.dt.fmtmonth);
	},

	createdModified: function(pCreationDate, pLastModified) {
		return FMT.dateTimeFull(pCreationDate) + ' / ' + FMT.dateTimeFull(pLastModified);
	}
};

var DT = {
	TzOffset: 0,

	parse: function(pValue) {
		var date = String(pValue);

		var Num;
		var matches = date.match(/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?/i);
		if ( matches ) {
			if ( !date.match(/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[Z+-]/i) ) {
				var tzOffset = new Date().getTimezoneOffset();
				var tzHours  = Math.abs(tzOffset) / 60;
				if ( tzHours < 10 )
					tzHours = '0'+tzHours;
				var tzMinutes = Math.abs(tzOffset) % 60;
				if ( tzMinutes < 10 )
					tzMinutes = '0'+tzMinutes;
				date += ( matches[1] ? '' : ':00' )+( tzOffset <= 0 ? '+' : '-' )+tzHours+':'+tzMinutes;
			}

			Num = Date.parse(date);

		} else {
			Num = Date.parse(date
			.replace(/(\d{2,4})-(\d{1,2})-(\d{1,2})/, '$2/$3/$1')
			.replace(/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{2,4})/, '$2/$1/$3')
			.replace(/([ap]m)/i, ' $1')
			.replace(/(\d{1,2})\s*([ap]m)/i, '$1:00 $2')
			);

		}

			return isNaN(Num) ? null : new Date(Num);
	},

	getDate: function(pDate) {
		return pDate instanceof Date ? pDate : new Date(pDate * 1000);
	},

	getStamp: function(pDate) {
		return pDate.getTime() / 1000;
	},

	mkDate: function(pDate /* default: now */, pOffsetDay /* default: 0 */) {
		pDate = UTL.isSet(pDate) ? DT.getDate(pDate) : new Date();

		if (!UTL.isSet(pOffsetDay))
			pOffsetDay = 0;

		return new Date(pDate.getFullYear(), pDate.getMonth(), pDate.getDate() + pOffsetDay);
	},

	format: function(pDate, pFormat) {
		pDate = DT.getDate(pDate);

		return ('' + pFormat).replace(/\\?([a-z])/gi, function(pMatch, pFirst) {
			switch (pMatch) {
				case 'd': // Day of month w/leading 0; 01..31
					var Day = pDate.getDate();

					if (Day < 10)
						Day = '0' + Day;

					return Day;

				case 'j': // Day of month; 1..31
					return pDate.getDate();

				case 'N': // ISO-8601 day of week; 1[Mon]..7[Sun]
					return pDate.getDayISO();

				case 'w': // Day of week; 0[Sun]..6[Sat]
					return pDate.getDay();

				case 'W': // ISO-8601 week number
					return pDate.getWeekISO();

				case 'm': // Month w/leading 0; 01...12
					var Month = pDate.getMonth() + 1;

					if (Month < 10)
						Month = '0' + Month;

					return Month;

				case 'n': // Month; 1...12
					return pDate.getMonth() + 1;

				case 't': // Days in month; 28...31
					return pDate.getDaysInMonth();

				case 'y': // Last two digits of year; 00...99
					return ('' + pDate.getFullYear()).slice(-2);

				case 'Y': // Full year; e.g. 2010
					return pDate.getFullYear();

				case 'a': // am or pm
					return pDate.getHours() > 11 ? 'pm' : 'am';

				case 'A': // AM or PM
					return pDate.getHours() > 11 ? 'PM' : 'AM';

				case 'g': // 12-Hours; 1..12
					return pDate.getHours() % 12 || 12;

				case 'G': // 24-Hours; 0..23
					return pDate.getHours();

				case 'h': // 12-Hours w/leading 0; 01..12
					var Hours = pDate.getHours() % 12 || 12;

					if (Hours < 10)
						Hours = '0' + Hours;

					return Hours;

				case 'H': // 24-Hours w/leading 0; 00..23
					var Hours = pDate.getHours();

					if (Hours < 10)
						Hours = '0' + Hours;

					return Hours;

				case 'i': // Minutes w/leading 0; 00..59
					var Minutes = pDate.getMinutes();

					if (Minutes < 10)
						Minutes = '0' + Minutes;

					return Minutes;

				case 's': // Seconds w/leading 0; 00..59
					var Seconds = pDate.getSeconds();

					if (Seconds < 10)
						Seconds = '0' + Seconds;

					return Seconds;

				case 'Z': // Seconds w/leading 0; 00..59
					var minutes = pDate.getTimezoneOffset(),
			sign, hours;
			if ( minutes < 0 ) {
			minutes *= -1;
			sign = '+';
			} else
			sign = '-';

			hours = parseInt(minutes / 60);
			if ( hours < 10)
			hours = '0' + hours;

			minutes = parseInt(minutes % 60);

					return sign + hours + ':' + ( minutes < 10 ? '0' : '' ) + minutes;
			}

			return pFirst;
		});
	}
};