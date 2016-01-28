window.addEvent('domready', function() {
	/* gx.bootstrap.Tabbox
	============================================================== */
	var myTabbox = new gx.bootstrap.Tabbox('tabs', {
		// 'height': 500,
		'frames': [
			{'name': 'tab0', 'title': 'Time components', 'content': $('tab0')},
			{'name': 'tab1', 'title': 'Table', 'content': $('tab1')},
			{'name': 'tab2', 'title': 'Message box', 'content': $('tab2')},
			{'name': 'tab3', 'title': 'Dynamic selection', 'content': $('tab3')},
			{'name': 'tab4', 'title': 'Fieldset', 'content': $('tab4')},
			{'name': 'tab5', 'title': 'Custom content', 'content': 'Hello world'}
		]
	});
	myTabbox.openTab('tab4');
	
	/* gx.bootstrap.Form and gx.bootstrap.Fieldset
	============================================================== */
	var btnFieldsetGetValues = new Element('input', {'type': 'button', 'class': 'btn btn-primary', 'value': 'Get values'});
	var btnFieldsetSetValues = new Element('input', {'type': 'button', 'class': 'btn btn-info m_l', 'value': 'Set values'});
	var btnFieldsetHighlight = new Element('input', {'type': 'button', 'class': 'btn btn-warning m_l', 'value': 'Add highlights'});
	var btnFieldsetReset = new Element('input', {'type': 'button', 'class': 'btn m_l', 'value': 'Reset'});
	var form = new gx.bootstrap.Form(null, {
		'title': 'Benutzer',
		'fields': {
			'name': {'type': 'text', 'label': 'Name', 'default': 'mmustermann'},
			'firstname': {'type': 'text', 'label': 'Vorname', 'default': 'Max'},
			'lastname': {'type': 'text', 'label': 'Nachname', 'default': 'Mustermann'},
			'email': {'type': 'text', 'label': 'E-Mail'}
		},
		'actions': [ btnFieldsetGetValues, btnFieldsetSetValues, btnFieldsetHighlight, btnFieldsetReset ]
	});
	form.addFieldset({
		'title': 'Passwort ändern',
		'fields': {
			'pwd1': {'type': 'password', 'label': 'Passwort'},
			'pwd2': {'type': 'password', 'label': 'Passwort bestätigen', 'help': 'Passwörter müssen übereinstimmen!'}
		}
	});
	$('fieldset').adopt(form.display());
	btnFieldsetGetValues.addEvent('click', function() {
		alert(JSON.encode(form.getValues()));
	});
	btnFieldsetSetValues.addEvent('click', function() {
		form.setValues({
			'name': 'p.haider',
			'firstname': 'Peter',
			'lastname': 'Haider',
			'email': 'peter.haider@groupion.com'
		});
	});
	btnFieldsetHighlight.addEvent('click', function() {
		form.setHighlights({
			'name': 'Invalid length',
			'firstname': {'label': 'blah', 'type': 'warning'},
			'pwd1': 'Password does not match',
			'pwd2': true
		});
	});
	btnFieldsetReset.addEvent('click', function() {
		form.reset();
	});
	
	/* gx.bootstrap.DatePicker
	============================================================== */
	var pickDate = new gx.bootstrap.DatePicker('datepicker');
	$('btnDatePickerSet').addEvent('click', function() {
		var ts = parseInt($('txtTimestamp').get('value'), 10);
		pickDate.set(Date.parse(ts));
	});
	
	/* gx.bootstrap.MonthPicker
	============================================================== */
	var pickMonth = new gx.bootstrap.MonthPicker('monthpicker');
	
	/* gx.bootstrap.Timebox
	============================================================== */
	var myTimebox = new gx.bootstrap.Timebox('timebox', {
		'prefix': true,
		'seconds': true,
		'icon': 'time',
		'label': 'Break'
	});
	$('btnTimeboxSet').addEvent('click', function() {
		myTimebox.set($('txtTimeboxTime').get('value'), $('txtTimeboxUnit').get('value'));
	});
	$('btnTimeboxGet').addEvent('click', function() {
		alert(myTimebox.get($('txtTimeboxUnit').get('value'), $('txtTimeboxPrecision').get('value')));
	});
	
	/* gx.bootstrap.CheckButton
	============================================================== */
	var checkbtn = new gx.bootstrap.CheckButton('checkbtn', {
		'label': 'Check me!',
		'size': 'mini'
	});
	
	/* gx.bootstrap.MenuButton
	============================================================== */
	var menubutton = new gx.bootstrap.MenuButton('menubutton', {
		'label': 'My Menu',
		'style': 'inverse',
		'orientation': 'right',
		'entries': [
			'Test1',
			'Test2',
			'Test3'
		]
	});
	
	/* gx.bootstrap.Select
	============================================================== */
	var mySelect = new gx.bootstrap.Select('divSelect', {
		'url': window.location.href.substring(0, window.location.href.lastIndexOf("/")+1) + 'assets/lib/gx/src/js/groupion/Select/sample/data.json'
	});
	$('btnSelectValue').addEvent('click', function() {
		alert(JSON.encode(mySelect.getSelected()));
	});
	$('btnSelectReset').addEvent('click', function() {
		mySelect.set();
	});
	
	/* gx.bootstrap.Checklist
	============================================================== */
	var myChecklist = new gx.bootstrap.Checklist('divChecklist', {
		'data': [
			{'value': 1, 'label': 'Test1'},
			{'value': 2, 'label': 'Hallo Welt'},
			{'value': 3, 'label': 'CMS'},
			{'value': 4, 'label': 'Kallahalla'},
			{'value': 5, 'label': 'Tampere'},
			{'value': 6, 'label': 'Reykjavik'}
		]
	});
	$('btnChecklistSetValue').addEvent('click', function() {
		myChecklist.addItem({'label': $('txtChecklistLabel').get('value'), 'value': $('txtChecklistValue').get('value')})
	});
	$('btnChecklistGetValue').addEvent('click', function() {
		alert(JSON.encode(myChecklist.getValues()));
	});
	
	/* gx.bootstrap.Popup
	============================================================== */
	var btnClosePopup = new Element('input', {'type': 'button', 'class': 'btn', 'value': 'close'});
	btnClosePopup.addEvent('click', function() {
		myPopup.hide();
	});
	
	var myPopup = new gx.bootstrap.Popup({
		'width': 300,
		'content': $('popop'),
		'title': 'Test',
		'footer': btnClosePopup,
		'closable': true
	});
	$('btnShowPopup').addEvent('click', function() {
		myPopup.show();
	});
	
	/* gx.bootstrap.Message
	============================================================== */
	var myMessage = new gx.bootstrap.Message($(document.body), {
		'messageWidth': 400,
		'duration': 0
	});
	myMessage.addEvent('click', function() {
		myMessage.clear();
	});
	$('btnAddMessage').addEvent('click', function(e) {
		myMessage.addMessage($('txtMessageText').get('value'), $('txtMessageType').get('value'), true);
	});
	$('btnAddMessageBlend').addEvent('click', function(e) {
		myMessage.addMessage($('txtMessageText').get('value'), $('txtMessageType').get('value'), true, true);
	});
	$('btnClear').addEvent('click', function(e) {
		myMessage.clear();
	});
	$('btnStatusBar').addEvent('click', function(e) {
		myMessage.showStatus(0.7, 'Loading', 1);
		myMessage.hideStatus.delay(2000, myMessage);
	});
	$('btnHideStatus').addEvent('click', function(e) {
		myMessage.hideStatus();
	});
	
	/* gx.bootstrap.Table
	============================================================== */
	var myTableData = [
		{'customername': 'HyperFlyer', 'customernum': '1003', 'lastmodified': '1220454105'},
		{'customername': 'InScreen Design', 'customernum': '1004', 'lastmodified': '1220454283'},
		{'customername': 'GlobalSpin Travel Agency', 'customernum': '1005', 'lastmodified': '1220454466'},
		{'customername': 'nTronic AG', 'customernum': '1001', 'lastmodified': '1220453517'},
		{'customername': 'CleanTexx', 'customernum': '1002', 'lastmodified': '1220454105'},
	];
	var myTable = new gx.bootstrap.Table('myTable', {
		'cols': [
			{'label': 'Name', 'id': 'customername'},
			{'label': 'Number', 'id': 'customernum'},
			{'label': 'Last change', 'id': 'lastmodified'}
		],
		'structure': function(row) {
			return [
				row.customername,
				row.customernum,
				new Date(row.lastmodified * 1000).format('%d.%m.%Y %H:%M')
			];
		},
		'data': myTableData,
		'height': '400px',
		'onClick': function(row) {
			alert(JSON.encode(row));
		},
		'onFilter': function(col) {
			alert(JSON.encode(col));
		}
	});
	myTable._display.tableDiv.setStyle('max-height', '300px');

	$('btnTableEmpty').addEvent('click', function() {
		myTable.empty();
	});
	$('btnTableSet').addEvent('click', function() {
		myTable.setData(myTableData);
	});
	$('btnAddData').addEvent('click', function() {
		var temp = myTableData.append(myTableData);
		temp.push({'customername': 'Another One', 'customernum': '1003943295792836012345719837632809467', 'lastmodified': '1220454105'});
		myTable.setData(myTableData);
	});
});