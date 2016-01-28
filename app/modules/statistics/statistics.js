window.addEvent('domready', function() {

	function doClockingStat() {
		var user = txtStatUsers.getSelected();
		var year = txtStatYear.get('value');
		var month = parseB10(txtStatMonth.get('value'));

		sendForm({
			'api' : 'clocking',
			'do' : 'stat',
			'month' : month + 1,
			'year' : year,
			'userid' : user == null ? null : user.id,
			'theuser' : user == null ? null : user.id
		}, function(res) {
			statBuildChart(res.result);
		}, null, 'array');
	}

	/* + + + + + + + + + + UI: Statistics + + + + + + + + + + */

	var txtStatMonth = $('txtDateStatMonth');
	var txtStatYear = $('txtDateStatYear');
	var statData = $('statData');
	var statDataBody = $('statDataBody');
	var statDataFoot = $('statDataFoot');
	var statChart;

	function statBuildChart(data) {
		trFoot = new Element('tr');
		statDataBody.empty();
		statDataFoot.empty();
		data.each(function(item, day) {
			day++;
			trFoot.adopt(new Element('td', {
				'html' : day
			}));
			var teffort = 0;
			item.tasks.each(function(task) {
				teffort = teffort + parseDec(task.effort);
			});

			tr = new Element('tr');
			tr.adopt(new Element('td', {
				'html' : roundDec(parseDec(item.records.time) / 3600)
			}));
			tr.adopt(new Element('td', {
				'html' : roundDec(teffort / 60)
			}));

			statDataBody.adopt(tr);
		});
		statDataFoot.adopt(trFoot);

		statDrawChart();
	}

	function statDrawChart() {
		var s = tabStatDiv.getSize();

		if (statChart != null && statChart.container != null)
			statChart.container.destroy();

		statChart = new MilkChart.Line(statData, {
			'width' : s.x - 10,
			'height' : s.y - 10,
			'border' : false,
			'showTicks' : true
		});
	}

	txtStatMonth.addEvent('change', function() {
		doClockingStat();
	});
	txtStatYear.addEvent('blur', function() {
		doClockingStat();
	});
	$('btnDateStatBack').addEvent('click', function() {
		var month = parseB10(txtStatMonth.get('value'));
		if (month == 0) {
			txtStatMonth.set('value', 11);
			txtStatYear.set('value', parseB10(txtStatYear.get('value')) - 1);
		} else
			txtStatMonth.set('value', month - 1);

		doClockingStat();
	});
	$('btnDateStatNext').addEvent('click', function() {
		var month = parseB10(txtStatMonth.get('value'));
		if (month == 11) {
			txtStatMonth.set('value', 0);
			txtStatYear.set('value', parseB10(txtStatYear.get('value')) + 1);
		} else
			txtStatMonth.set('value', month + 1);

		doClockingStat();
	});

	var txtStatUsers = new gx.bootstrap.Select('txtStatUsers', {
		'language' : 'de',
		'msg' : {
			'de' : {
				'noSelection' : '(Alle Nutzer)'
			},
			'noSelection' : '(All Users)'
		},
		'decodeResponse' : function(json) {
			var res = JSON.decode(json);
			return res.result;
		},
		'default' : null,
		'url' : urlBase,
		'requestData' : {
			'api' : 'user',
			'do' : 'list'
		},
		'requestParam' : 'search',
		'listFormat' : function(elem) {
			if (elem.realname)
				return elem.realname + ' (' + elem.username + ')';
			else
				return elem.username;
		},
		'onSelect' : function(sel) {
			doClockingStat();
		},
		'onNoSelect' : function(sel) {
			doClockingStat();
		}
	});
	if (force_user) {
		txtStatUsers.set(getUser(force_user));
		txtStatUsers.disable();
	}
	var btnStatReset = $('btnStatReset');
	if (btnStatReset) {
		btnStatReset.addEvent('click', function() {
			txtStatUsers.set();
		});
	}
});