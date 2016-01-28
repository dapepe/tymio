window.localStorage; // FIX WebKit

function getMonthStartStamp(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1).getTime();
}
function getMonthEndStamp(date) {
  // Create date with first day of next month and sub 1 to get stamp of the day before
  return new Date(
	new Date(date.getFullYear(), date.getMonth() + 1, 1).getTime() - 1
  ).getTime();
}

var C = {
  dt: {
  	fmt: 'Y-m-d\TH:i:s', // used for input type="datetime-local" fitting for ios systems.
	fmtdisp: 'Y-m-d H:i',
    fmtdate: 'd.m.Y',
    fmttime: 'H:i',
    fmtmonth: 'm/Y',
    startsunday: false
  },
  fmt: ['.', ','],
  url: '../index.php',
  accountName: 'cms/'
};

var APIparams = {
  user: {
	login: function(username, password) {
	  return {
		'api'     : 'user',
		'do'      : 'details',
		'username': username,
		'password': password
	  }
	}
  },
  clocking: {
	list: function(start, end, userId) {
	  return {
		'api'   : 'clocking',
		'do'    : 'list',
		'start' : start,
		'end'   : end,
		'user'  : userId
	  }
	},

	current: function(userId) {
	  return {
		'api' : 'clocking',
		'do'  : 'current',
		'user': userId
	  }
	},

	signin: function(date, comment, userId) {
	  return {
		'api' : 'clocking',
		'do'  : 'add',
		'data': {
		  'Start'   : date,
		  'End'     : date,
		  'UserId'  : userId,
		  'TypeId'  : 1,
		  'Comment' : comment
		}
	  }
	},

	signout: function(id, start, end, comment, pause) {
	  return {
		'api' : 'clocking',
		'do'  : 'update',
		'id'  : id,
		'data': {
		  'Start'    : start,
		  'End'      : end,
		  'Comment'  : comment,
		  'Breaktime': pause
		}
	  }
	},

	remove: function(id) {
	  return {
		'api' : 'clocking',
		'do'  : 'remove',
		'id'  : id
	  }
	},

	add: function(data) {
	  return {
		'api' : 'clocking',
		'do'  : 'add',
		'data': data
	  }

	},

	update: function(id, data) {
	  return {
		'api' : 'clocking',
		'do'  : 'update',
		'id'  : id,
		'data': data
	  }

	}
  },

  transactions: {
	list_bookings: function(userid, start, end, types) {
	  return {
		'api'  : 'transactions',
		'do'   : 'list_bookings',
		'user' : id,
		'start': start,
		'end'  : end,
		'types': types
	  }
	}
  }
};

var PG = {
  StoragePrefix: 'groupion_',
  BaseUrl: C.url,

  CurrentDialog: '',
  CurrentPage: '',

  ClockingDate: null,
  ClockingDetails: null,

  Settings: {},

  CaptionsMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

	init: function() {
  	if (window.localStorage)
  	  for (var I = localStorage.length; I--;) {
  	    var Key = localStorage.key(I);

  	    if (Key.indexOf(PG.StoragePrefix) == 0)
  	      PG.Settings[Key.substr(9)] = localStorage.getItem(Key);
  	  }
	else
	  alert('No local storage support');

		PG.initCheck();

		PG.showPageIndex(0);
	},

	initCheck: function() {
  	if ('onorientationchange' in window)
  	  DOM.addEvent(window, 'orientationchange', function() {
  	  	var Page = $(PG.CurrentPage);

	  	  if (Page) {
	  	    var Min = (innerHeight + (navigator.platform == 'iPhone' && !navigator.standalone ? 16 : -44)) + 'px';

	  	    if (Min != Page.style.minHeight)
	  	      Page.style.minHeight = Min;
	  	  }
	  	});
	},

  load: function(pAttr, pHandler) {
  	PG.displayIndicator(true);

  	var User = PG.getSetting('user', '');

  	UTL.request(PG.BaseUrl, 'username=' + encodeURIComponent(User) + '&password=' + encodeURIComponent(PG.getSetting('pwd', '')) + UTL.buildAttr(pAttr), function(pResponse) {
  		if (pResponse != '') {
  			var Data = UTL.json(pResponse);

  			if (Data.error) {
  				if (Data.error == 'Authentication required.' || Data.error == 'Username not found') {
  				  if (User != '')
  				    alert('Incorrect username or password!');

				  PG.showPageLogin(2);
  				} else if (Data.errorname == 'overlap' ) {
				  var ns = FMT.dateTimeDisp(pAttr.data['Start']);
				  var ne = '';
				  if ( pAttr.data['End'] != null )
					ne = FMT.dateTimeDisp(pAttr.data['End']);

				  var es = FMT.dateTimeDisp(Data.errordata.Start);
				  var ee = FMT.dateTimeDisp(Data.errordata.End);
				  alert(ns + (ne != '' ? ' - ' : ' ') + ne + '\nis overlapping with\n' + es + ' - ' + ee );

				} else
  				  alert(Data.error == '' ? 'Unknown error!' : Data.error);
  			} else {
			//DT.TzOffset = Math.round((new Date() - Date.parse(Data.time)) / 1000);
  		    //PG.setSetting('user', Data.username);

  			  pHandler(Data);
  			}
  		}

  		PG.displayIndicator(false);
  	});
  },

  setSetting: function(pName, pValue) {
  	PG.Settings[pName] = pValue;

  	if (window.localStorage)
  	  localStorage.setItem(PG.StoragePrefix + pName, pValue);
  },

  getSetting: function(pName, pDefault) {
  	return pName in PG.Settings ? PG.Settings[pName] : pDefault;
  },

  add: function(pId, pToolBar, pPage) {
  	if (PG.CurrentDialog != pId && PG.CurrentPage != pId) {
  	  pPage.display(false);
  	  pToolBar.display(false);
  	}

  	var Page = $(pId);

  	pPage.id = pId;

    if (Page) {
    	Page.getNext().replaceSelf(pToolBar);
    	Page.replaceSelf(pPage);
    } else
    	DOM.getBody()
    	  ._(pPage)
    	  ._(pToolBar)
    	;
  },

  remove: function(pId) {
  	var Page = $(pId);

  	if (Page) {
  	  if (PG.CurrentDialog == pId)
  	    PG.CurrentDialog = '';

  	  if (PG.CurrentPage == pId)
  	    PG.CurrentPage = '';

  	  Page.getNext().removeSelf();
    	Page.removeSelf();
  	}
  },

  markOld: function(pId) {
  	var Page = $(pId);

  	if (Page) {
  		Page.id = 'old_' + pId;

  		if (PG.CurrentPage == pId)
  		  PG.CurrentPage = 'old_' + pId;

  		return 'old_' + pId;
  	}

  	return '';
  },

  show: function(pId, pType, pHandler /* optional */) {
  	scrollTo(0, 0);

  	PG.hideDialog();

  	if (PG.CurrentPage == pId)
  	  return;

  	var Old     = $(PG.CurrentPage),
  	    New     = $(pId),
  	    ToolBar = New.getNext().display(true);

  	if (Old) {
  		Old.getNext().display(false);

  	  if (!window.WebKitCSSMatrix)
  	    pType = 0;
  	  else if (pType != 0) {
  	  	var Title = ToolBar.getFirst();

  	  	PG.resetTransition(Title, (pType > 2 ? 'rotateY' : 'rotateX') + '(180deg)');

  	    setTimeout(function() {
  	    	Title.style.webkitTransform = '';
  	    }, 0);
      }
  	} else if (pType != 1)
  	  pType = 0;

    switch (pType) {
      case 1:
  	    PG.resetTransition(New, 'translateY(-100%)');

  	    if (Old)
  	      New.style.zIndex = 2;

  	    New.display(true);

  	    New.addEventListener('webkitTransitionEnd', function() {
  	    	New.removeEventListener('webkitTransitionEnd', arguments.callee, false);

      	  if (Old) {
      	  	Old.display(false);
  	        New.style.zIndex = '';
      	  }

  	      if (pHandler != null)
  	  	    pHandler();
  	    }, false);

  	    setTimeout(function() {
  	    	New.style.webkitTransform = '';
  	    }, 0);

      	break;

      case 2:
  	    Old.style.zIndex = '2';
  	    New.display(true);

  	    Old.addEventListener('webkitTransitionEnd', function() {
  	    	Old.removeEventListener('webkitTransitionEnd', arguments.callee, false);

          Old.display(false);
  	      Old.style.zIndex = '';

  	      PG.resetTransition(Old, '');

  	      if (pHandler != null)
  	  	    pHandler();
  	    }, false);

  	    setTimeout(function() {
  	    	Old.style.webkitTransform = 'translateY(-100%)';
  	    }, 0);

      	break;

      case 3:
      case 4:
        PG.resetTransition(New, 'translateX(' + (pType == 4 ? '-' : '') + '100%)');

        New.display(true);

  	    Old.addEventListener('webkitTransitionEnd', function() {
  	    	Old.removeEventListener('webkitTransitionEnd', arguments.callee, false);

          Old.display(false);

          PG.resetTransition(Old, '');

  	      if (pHandler != null)
  	  	    pHandler();
  	    }, false);

  	    setTimeout(function() {
  	      Old.style.webkitTransform = 'translateX(' + (pType == 4 ? '' : '-') + '100%)';
  	      New.style.webkitTransform = '';
  	    }, 0);

  	    break;

  	  case 5:
  	  case 6:
        var Body = DOM.getBody();
        Body.style.webkitPerspective = '600';

        PG.resetTransition(New, 'rotateY(' + (pType == 6 ? '-' : '') + '90deg)');

        New.display(true);

  	    New.addEventListener('webkitTransitionEnd', function() {
  	    	New.removeEventListener('webkitTransitionEnd', arguments.callee, false);

          Body.style.webkitPerspective = '';
          New.style.webkitTransitionDuration = '';

  	      if (pHandler != null)
  	  	    pHandler();
  	    }, false);

  	    Old.addEventListener('webkitTransitionEnd', function() {
  	    	Old.removeEventListener('webkitTransitionEnd', arguments.callee, false);

  	    	New.setAttr({_webkitTransitionDuration: '300ms', _webkitTransform: ''});

          Old.display(false);

          PG.resetTransition(Old, '');
  	    }, false);

        Old.style.webkitTransitionDuration = '300ms';

  	    setTimeout(function() {
  	    	Old.style.webkitTransform = 'rotateY(' + (pType == 6 ? '' : '-') + '90deg)';
  	    }, 0);

  	    break;

      default:
        New.display(true);

        if (Old)
      	  Old.display(false);

  	    if (pHandler != null)
  	  	  pHandler();
    }

  	PG.CurrentPage = pId;

  	DOM.triggerEvent(window, 'orientationchange');
  },

  resetTransition: function(pElem, pTransform) {
  	pElem
  	  .setAttr('_webkitTransitionDuration', 0)
  	  .setAttr('_webkitTransform', pTransform)
  	  .setAttr('_webkitTransitionDuration', '')
  	;
  },

  showDialog: function(pId) {
  	scrollTo(0, 0);

  	if (PG.CurrentDialog != pId) {
  		var Page   = $(PG.CurrentPage),
  		    Dialog = $(pId);

  	  Dialog.style.height = Page.offsetHeight + 'px';

  	  Dialog
  	    .display(true)
  	    .getNext().display(true)
  	  ;

  	  Page.getNext().display(false);

  	  PG.CurrentDialog = pId;
  	}
  },

  hideDialog: function() {
  	var Dialog = $(PG.CurrentDialog);

  	if (Dialog) {
  		var Page = $(PG.CurrentPage);

  		if (Page)
  		  Page.getNext().display(true);

  		Dialog
  		  .display(false)
  		  .getNext().display(false)
  		;
  	}

  	PG.CurrentDialog = '';
  },

  displayIndicator: function(pDisplay) {
  	var Indicator = $('indicator');

  	if (Indicator)
  	  Indicator.display(pDisplay);
  	else if (pDisplay) {
  		Indicator = __('div#indicator')
  	    ._(__('div'))
  	  ;

  	  DOM.getBody()._(Indicator);
  	}

  	if (pDisplay)
  	  Indicator.style.top = (DOM.getScrollTop() + 120) + 'px';
  },

  fmtDurationShort: function(pDuration) {
  	return FMT.duration(pDuration) + ' h';
  },

  fmtDurationLong: function(pDuration) {
  	var Hours   = Math.floor(pDuration / 60),
  	    Minutes = Math.round(pDuration % 60);

  	if (Hours == 0)
  	  return Minutes + ' Minutes';

  	if (Minutes == 0)
  	  return Hours + ' Hours';

  	return Hours + ' Hours, ' + Minutes + ' Minutes';
  },

  showPageLogin: function(pType) {
  	if (!$('login'))
  	  PG.add('login',
  	    __('div.toolbar')
  	      ._(__('div')._('Groupion'))
  	      /*._(__('a.left', {onclick: function() {
		  	    location.href = PG.BaseUrl + 'index.php';
	  	    }})._('Regular')) */
  	      ._(__('a.sel', {onclick: PG.showPageLoginHandler})._('Login'))
  	    ,
  	    __('form.panel page', {onsubmit: function () {
		  PG.showPageLoginHandler();
		  if ( document.activeElement )
			document.activeElement.blur();
		  return false;
		} })
  	      ._(__('img', {src: './logo.png'}))
  	      ._(__('h2')._('Groupion Mobile for ' + navigator.platform))
  	      ._(__('p')
  	        ._(__('h3.input')
  	          ._(__('h1')._('User'))
  	          ._(__('input#login_user', {maxlength: 60}))
  	        )
  	        ._(__('h3.input')
  	          ._(__('h1')._('Password'))
  	          ._(__('input#login_pwd', {type: 'password'}))
  	        )
  	      )
  	      ._(__('input', {type: 'submit', style:'position:absolute;visibility:hidden;'}))
  	      ._(__('div.btnWhite', {onclick: PG.showPageLoginHandler})._('Login'))
  	      ._(__('h3')._('© 2011 Groupion Software Inc.'))
  	  );

  	$('login_user').value = PG.getSetting('user', '');
  	$('login_pwd').value = PG.getSetting('pwd', '');

  	PG.show('login', pType, function() {
  		var Page    = $('login'),
  		    ToolBar = Page.getNext();

  		DOM.getBody()
  		  .clear()
  		  ._(Page)
  		  ._(ToolBar)
  		;
  	});
  },

  showPageLoginHandler: function() {
  	PG.displayIndicator(true);

	var user = $('login_user').value;
	if ( !user.match(/\//) )
	  user = C.accountName+user;

	var pwd = $('login_pwd').value;

  	UTL.request(PG.BaseUrl, UTL.buildAttr(APIparams.user.login(user, pwd)), function(pResponse) {
	  try {
		if (pResponse != '') {
			var Data = UTL.json(pResponse);

			if (Data.error) {
			  if (Data.error == 'Authentication required.')
				alert('Wrong username or password.');
			  else
				alert(Data.error);

			} else if ( Data.result) {
			  //DT.TzOffset = Math.round((new Date() - Date.parse(Data.time)) / 1000);

			  if ( Data.result.Id == undefined || Data.result.DomainId == undefined ) {
				alert('Unexpected result. Please contact developer.');
				return;
			  }
			  PG.setSetting('user', user);
			  PG.setSetting('pwd', pwd);
			  PG.setSetting('userid', Data.result.Id);
			  PG.showPageIndex(2);
			}
		} else {
		  alert('Unexpected empty response');

		}
		PG.displayIndicator(false);
	  } catch ( e ) {
		alert( 'Login: ' + e.message);
	  }
  	});
  },

  showPageIndex: function(pType, pHandler /* optional */) {
  	if ($('index'))
  	  PG.show('index', pType, pHandler);
  	else {
	  var userId = PG.getSetting('userid');

  	  PG.load(APIparams.clocking.current(userId), function(pData) {
	  	  if (pData && pData.result && !Array.isArray(pData.result)) {

	  	  	var Attention = __('p')
	  	  	  ._(__('h4')
	  	  	    ._(__('h1')._('User'))
	  	  	    ._(PG.getSetting('user'))
	  	  	  )
	  	  	  ._(__('h4')
	  	  	    ._(__('h1')._('Signed In'))
	  	  	    ._(FMT.dateTimeDisp(pData.result.Start))
	  	  	  )
	  	  	  ._(__('h4')
	  	  	    ._(__('h1')._('Duration'))
	  	  	    ._(PG.fmtDurationLong((DT.getStamp(new Date()) - pData.result.Start) / 60))
	  	  	  )
	  	  	;

	  	    var Button = __('div.btnRed', {onclick: function() {
	  	    	PG.showDialogSignOut(pData.result);
	  	    }})._('Sign Out');
	  	  } else {
	  	  	var Attention = __('p')
	  	  	  ._(__('h1.bold center')._('You are currently not signed in!'))
	  	  	;

	  	    var Button = __('div.btnGreen', {onclick: PG.showDialogSignIn})._('Sign In');
	  	  }

  	  	PG.add('index',
	  	    __('div.toolbar')
	  	      ._(__('div')._('Groupion'))
	  	      ._(__('a.back')
		  	      ._(__('div.logout', {onclick: function() {
					if ( !confirm('Logout ?') )
					  return;

					PG.setSetting('pwd', '');
					PG.setSetting('user', '');
		  	      	PG.showPageLogin(1);
		  	      }}))
		  	    )
	  	      ._(__('a', {onclick: function() {
	  	      	var Id = PG.markOld('index');

		  	      PG.showPageIndex(0, function() {
		  	      	PG.remove(Id);
		  	      });
	  	      }})._('Refresh'))
	  	    ,
	  	    __('div.panel page')
	  	      ._(PG.createTabsIndex('index'))
	  	      ._(__('h2')
		  	      ._(__('img', {src: './logo_cms.png'}))
		  	    )
		  	    ._(Attention)
		  	    ._(Button)
		  	    ._(__('h3')._('Sign in/out from a work session'))
	  	  );

	  	  PG.show('index', pType, pHandler);
  	  });
  	}
  },

  createTabsIndex: function(pPage) {
  	return __('table')
	    ._(__('tbody')
	  	  ._(__('tr')
	  	    ._(__('td', {onclick: function() {
	          PG.showPageIndex(5);
	  	    }})
	  	      .addClass(pPage == 'index' ? 'sel' : '')
	  	      ._('Session')
	  	    )
	  	    ._(__('td', {onclick: function() {
	          PG.showPageClocking(5);
	  	    }})
	  	      .addClass(pPage == 'clocking' ? 'sel' : '')
	  	      ._('Clockings')
	  	    )
	  	    /*
	  	    ._(__('td', {onclick: function() {
	          PG.showPageCases(5);
	  	    }})
	  	      .addClass(pPage == 'cases' ? 'sel' : '')
	  	      ._('Tasks')
	  	    )
	  	    */
	  	  )
	  	)
	 ;
  },

  showDialogSignIn: function() {
  	if (!$('signin'))
  	  PG.add('signin',
  	    __('div.toolbar')
  	      ._(__('div')._('Start Session'))
  	      ._(__('a.left', {onclick: PG.hideDialog})._('Close'))
  	      ._(__('a.sel', {onclick: PG.showDialogSignInHandler})._('Sign In'))
  	    ,
  	    __('form.dialog', { onsubmit: function () {
		  PG.showDialogSignInHandler();
		  if ( document.activeElement )
			document.activeElement.blur();
		  return false;
		} })
  	      ._(__('h1')
  	        ._(__('h1')._('Start'))
  	        ._(__('div.DTF').
			   _(__('input#signin_start', {'type':'datetime-local'}))
			  )
  	      )
  	      ._(__('h1')
  	        ._(__('textarea#signin_comment', {placeholder: 'Enter Comment'}))
  	      )
  	      ._(__('input', {type: 'submit', style: 'position:absolute;visibility:hidden;'}))
  	      ._(__('div.btnGray', {onclick: PG.showDialogSignInHandler})._('Sign In'))
  	      ._(__('div.btnBlack', {onclick: PG.hideDialog})._('Abort'))
  	  );

  	$('signin_start').value = FMT.dateTimeFull(new Date());
  	$('signin_comment').value = '';

  	PG.showDialog('signin');
  },

  showDialogSignInHandler: function() {
  	var Start = DT.parse($('signin_start').value);

  	if (Start == null)
  	  alert('Incorrect date and time!');
  	else
  		PG.load(APIparams.clocking.signin(DT.getStamp(Start), $('signin_comment').value, PG.getSetting('userid')), function() {
	  	  var Id = PG.markOld('index');

		  	PG.showPageIndex(0, function() {
		  	  PG.remove(Id);
		  	  PG.remove('clocking_' + DT.getStamp(DT.mkDate(Start)));
		  	});
  		});
  },

  showDialogSignOut: function(pLast) {
  	if (!$('signout')) {
		// Handler must be updated after each successfull sign out
		// therefore recreate this dialog after successfull sign out
		// @see showDialogSignOutHandler for more information
  		var Handler = function() {
  			PG.showDialogSignOutHandler(pLast);
			if ( document.activeElement )
			  document.activeElement.blur();
			return false;
  		};

  	  PG.add('signout',
  	    __('div.toolbar')
  	      ._(__('div')._('End Session'))
  	      ._(__('a.left', {onclick: PG.hideDialog})._('Close'))
  	      ._(__('a.sel', {onclick: Handler})._('Sign Out'))
  	    ,
  	    __('form.dialog', { onsubmit: Handler })
  	      ._(__('h1')
  	        ._(__('h1')._('Start'))
  	        ._(__('div.DTF').
			   _(__('input#signout_start', {'type':'datetime-local'}))
			  )
  	      )
  	      ._(__('h1')
  	        ._(__('h1')._('End'))
  	        ._(__('div.DTF').
			   _(__('input#signout_end', {'type':'datetime-local'}))
			  )
  	      )
  	      ._(__('h1')
  	        ._(__('h1')._('Break'))
  	        ._(__('input#signout_break', {placeholder: 'In Minutes'}))
  	      )
  	      ._(__('h1')
  	        ._(__('textarea#signout_comment', {placeholder: 'Enter Comment'}))
  	      )
  	      ._(__('input', {type: 'submit', style: 'position:absolute;visibility:hidden;'}))
  	      ._(__('div.btnGray', {onclick: Handler})._('Sign Out'))
  	      ._(__('div.btnBlack', {onclick: PG.hideDialog})._('Abort'))
  	  );
  	}

  	var Now      = new Date(),
  		  Duration = (DT.getStamp(Now) - pLast.Start) / 3600;

  	$('signout_start').value   = FMT.dateTimeFull(pLast.Start);
  	$('signout_end').value     = FMT.dateTimeFull(Now);
  	$('signout_break').value   = Duration > 9.5 ? 45 : (Duration > 6.5 ? 30 : '');
  	$('signout_comment').value = '';

  	PG.showDialog('signout');
  },

  showDialogSignOutHandler: function(pLast) {
  	var End = DT.parse($('signout_end').value);
  	var Start = DT.parse($('signout_start').value);

  	if (End == null || Start == null )
  	  alert('Incorrect date and time!');
  	else {
  		var Break = $('signout_break').value.trim();

  		if (Break == '')
  		  Break = 0;

  		if (!UTL.isNum(Break))
  		  alert('Break must be a number of minutes!');
  		else
	  		PG.load(APIparams.clocking.signout(pLast.Id, DT.getStamp(Start), DT.getStamp(End), $('signout_comment').value.trim(), Break * 60), function() {
	  	    var Id = PG.markOld('index');
	  	    var IdSignOut = PG.markOld('signout');

			  	PG.showPageIndex(0, function() {
			  	  PG.remove(Id);
			  	  PG.remove(IdSignOut); // force recreating this dialog:
				  /* sign in -> sing out -> sign in -> sign out (without refreshing)
				   * create bug that second sign out changes the clocking of the first sign in cause the update id
				   * is still the old one. This happens cause the update Handler on the sign out button still
				   * remains from the first sign out dialog. Therefore remove and recreate sign out dialog!
				   */
			  	  PG.remove('clocking_' + DT.getStamp(DT.mkDate(End)));
			  	});
	  		});
  	}
  },

  showPageClocking: function(pType, pHandler /* optional */) {
  	if (PG.ClockingDate == null)
  	  PG.ClockingDate = DT.mkDate();

  	var Id = 'clocking_' + DT.getStamp(PG.ClockingDate);

  	if ($(Id))
  	  PG.show(Id, pType, pHandler);
  	else {
  		var Month = PG.ClockingDate.getMonth(),
  		    Year  = PG.ClockingDate.getFullYear(),
			username = PG.getSetting('user');

  	  PG.load(APIparams.clocking.list(getMonthStartStamp(PG.ClockingDate) / 1000, getMonthEndStamp(PG.ClockingDate) / 1000, PG.getSetting('userid')), function(pData) {
  	  	var Result = pData.result;

	  		var List = __('div.list page')
		  	  ._(__('h4')
		  	    ._(PG.createTabsIndex('clocking'))
		  	  )
		  	  ._(__('h2.link', {onclick: function() {
		  	  	PG.ClockingDetails = null;
		  	  	PG.showPageClockingEdit(1);
		  	  }})._('Add new entry …'))
		  	;

		  	if (Result.length == 0)
		  	  List._(__('h4')._('No Clocking Entries'));
		  	else {
			  	var Grouping;

			  	Result.forEach(function(pItem) {
			  		var DayStart = DT.getStamp(DT.mkDate(pItem.Start));

			  		if (Grouping != DayStart) {
			  			Grouping = DayStart;
			  		  List._(__('h1')._(DT.getDate(DayStart).toLocaleDateString()));
			  		}

			  		var Row = __('h3', {onclick: function() {
			  			PG.ClockingDetails = pItem;
			  			PG.showPageClockingDetails(3);
			  		}})._(pItem.User == null ? username : pItem.User.Name);

			  		if (pItem.End == null)
			  		  Row._(__('h2')._(FMT.dateTimeDisp(pItem.Start)));
			  		else
			  		  Row
			  		    .addTop(__('h3')._(PG.fmtDurationShort((pItem.End - pItem.Start) / 60)))
			  		    ._(__('h2')._(FMT.dateTimeDisp(pItem.Start) + ' ― ' + (DayStart == DT.getStamp(DT.mkDate(pItem.End)) ? FMT.time(pItem.End) : FMT.dateTimeDisp(pItem.End))))
			  		  ;

			  		List._(Row);
			  	});
		  	}

		  	List._(__('h2.more', {onclick: function() {
	  	    var IdOld = PG.markOld(Id);

		  		PG.showPageClocking(0, function() {
		  	  	PG.remove(IdOld);
		  		});
		  	}})._('Refresh …'));

		  	var Prev = new Date(Year, Month - 1, 1),
  		      Next = new Date(Year, Month + 1, 1);

  	  	PG.add(Id,
	  	    __('div.toolbar')
	  	      ._(__('div')._(PG.CaptionsMonths[Month] + ' ' + Year))
	  	      ._(__('a.left', {onclick: function() {
	  	      	PG.ClockingDate = Prev;
	  	      	PG.showPageClocking(4);
	  	      }})._(PG.CaptionsMonths[Prev.getMonth()]))
	  	      ._(__('a', {onclick: function() {
	  	      	PG.ClockingDate = Next;
	  	      	PG.showPageClocking(3);
	  	      }})._(PG.CaptionsMonths[Next.getMonth()]))
	  	    ,
	  	    List
	  	  );

  	    PG.show(Id, pType, pHandler);
  	  });
  	}
  },

  showPageClockingDetails: function(pType, pHandler /* optional */) {
  	if (PG.ClockingDate == null)
  	  PG.ClockingDate = DT.mkDate();

  	var Item     = PG.ClockingDetails,
  	    Start    = Item.Start,
  	    End      = Item.End,
		username = PG.getSetting('user');

	  var Section = __('p')
  	  ._(__('h4')
  	    ._(__('h1')._('User'))
	  	  ._(username)
  	  )
  	  ._(__('h4')
	  	  ._(__('h1')._('Start'))
	  	  ._(FMT.dateTimeDisp(Start))
  	  )
  	;

   if (End != null) {
  	 Section._(__('h4')
	  	 ._(__('h1')._('End'))
	  	 ._(FMT.dateTimeDisp(End))
  	 );

	   var Break = Item.Breaktime;

  	 if (Break != 0)
  	   Section._(__('h4')
	  	   ._(__('h1')._('Break'))
	  	   ._((Break / 60) + ' Minutes')
  	   );

  	 Section._(__('h4')
	  	 ._(__('h1')._('Duration'))
	  	 ._(PG.fmtDurationLong((End - Start - Break) / 60))
	   );
   }

    var Page = __('div.panel page')
  	  ._(Section)
  	;

  	if (Item.Comment != null && Item.Comment != '')
  	  Page
  	    ._(__('h1')._('Comment'))
  	    ._(__('p')
  	      ._(__('h2').setText(Item.Comment))
  	    )
  	  ;
/*
  	Page
  	  ._(__('h1')._('Break Down'))
  	  ._(__('p')
  	    ._(__('h5')
	  	    ._(__('h1')._('Flexitime'))
	  	    ._(PG.fmtDurationShort(Item.flexitime))
  	    )
  	    ._(__('h5')
	  	    ._(__('h1')._('Overtime'))
	  	    ._(PG.fmtDurationShort(Item.overtime))
  	    )
  	    ._(__('h5')
	  	    ._(__('h1')._('Denied'))
	  	    ._(__('span', {_color: Item.denied == 0 ? null : 'red'})._(PG.fmtDurationShort(Item.denied)))
  	    )
  	    ._(__('h5')
	  	    ._(__('h1')._('Tasks'))
	  	    ._(PG.fmtDurationShort(Item.task_sum) + ' (' + FMT.num(Item.task_count) + ')')
  	    )
  	  )
  	;
*/
  	var Handler = function() {
  		PG.showPageClockingEdit(1);
  	};

  	Page
  	  ._(__('h1')._('Info'))
  	  ._(__('p')
  	    ._(__('h6')
	  	    ._(__('h1')._('Creator'))
	  	    ._(Item.Cerator == null ? '-' : Item.Cerator.Name)
  	    )
  	    ._(__('h6')
	  	    ._(__('h1')._('Created'))
	  	    ._(FMT.dateTimeDisp(Item.Creationdate))
  	    )
  	  )
  	  ._(__('div.btnWhite', {onclick: Handler})._('Edit'))
  	  ._(__('div.btnRed', {onclick: function() {
  	    if (confirm('Do you really want to remove this clocking entry?'))
  			  PG.load(APIparams.clocking.remove(Item.Id), function() {
  			  	PG.remove('clocking_' + DT.getStamp(DT.mkDate(Start)));
            PG.remove('clocking_' + DT.getStamp(DT.mkDate(End)));
  			  	alert('Clocking entry successfully removed!');
  			  	PG.showPageClocking(4);
  			  });
  	  }})._('Remove'))
  	;

  	PG.add('clocking_details',
  	  __('div.toolbar')
  	    ._(__('div')._('Details'))
  	    ._(__('a.back', {onclick: function() {
		  	  PG.showPageClocking(4);
	  	  }})._(FMT.month(PG.ClockingDate)))
  	    ._(__('a')
  	      ._(__('div.options', {onclick: Handler}))
  	    )
  	  ,
  	  Page
  	);

  	PG.show('clocking_details', pType, pHandler);
  },

  showPageClockingEdit: function(pType, pHandler /* optional */) {
  	var Item   = PG.ClockingDetails,
  	    NoItem = Item == null;

  	PG.add('clocking_edit',
  	  __('div.toolbar')
  	    ._(__('div')._(NoItem ? 'New' : 'Edit'))
  	    ._(__('a.left', {onclick: function() {
		  	  if (NoItem)
		  	    PG.showPageClocking(2);
		  	  else
		  	    PG.showPageClockingDetails(2);
	  	  }})._('Close'))
  	    ._(__('a.sel', {onclick: PG.showPageClockingEditHandler})._('Save'))
  	  ,
  	  __('div.panel page')
  	    ._(__('p')
  	      ._(__('h3.input')
  	        ._(__('h1')._('User'))
	  	      ._(__('input#clocking_edit_user', {value: PG.getSetting('user')}))
  	      )
  	      ._(__('h3')
  	        ._(__('h1')._('Start'))
  	        ._(__('div.DTF').
			   _(__('input#clocking_edit_start', {type: 'datetime-local', value: FMT.dateTimeFull(NoItem ? new Date() : Item.Start)}))
			  )
  	      )
  	      ._(__('h3')
  	        ._(__('h1')._('End'))
  	        ._(__('div.DTF').
			   _(__('input#clocking_edit_end', {type: 'datetime-local', value: FMT.dateTimeFull(NoItem ? new Date() : Item.End)}))
			  )
  	      )
  	      ._(__('h3.input')
  	        ._(__('h1')._('Break'))
  	        ._(__('input#clocking_edit_break', {placeholder: 'In Minutes', value: NoItem || Item.Breaktime == 0 ? null : Item.Breaktime / 60}))
  	      )
  	    )
  	    ._(__('h1')._('Comment'))
  	    ._(__('textarea#clocking_edit_comment', {value: NoItem ? null : Item.Comment}))
  	    ._(__('div.btnWhite', {onclick: PG.showPageClockingEditHandler})._('Save Changes'))
  	);

  	PG.show('clocking_edit', pType, pHandler);
  },

  showPageClockingEditHandler: function() {
  	var Start = DT.parse($('clocking_edit_start').value),
  	    End   = DT.parse($('clocking_edit_end').value);

  	if (Start == null || End == null)
  	  alert('Incorrect date and time!');
  	else {
  		var Break = $('clocking_edit_break').value.trim() * 60;

  		if (Break == '')
  		  Break = 0;

  		if (!UTL.isNum(Break))
  		  alert('Break must be a number of minutes!');
  		else if (confirm('Do you really want save changes?')) {
  			var Item   = PG.ClockingDetails,
  	        NoItem = Item == null;

			var data = {
			  Comment   : $('clocking_edit_comment').value.trim(),
			  End       : DT.getStamp(End),
			  Start     : DT.getStamp(Start),
			  Breaktime : Break,
			  TypeId    : 1,
			  UserId    : PG.getSetting('userid'),
			  CreatorId : PG.getSetting('userid')
			}

	  		PG.load((
				NoItem
				? APIparams.clocking.add(data)
				: APIparams.clocking.update(Item.Id, data)
			  ),
			  function() {
  			  PG.remove('clocking_' + DT.getStamp(DT.mkDate(Start)));
  			  PG.remove('clocking_' + DT.getStamp(DT.mkDate(End)));

  			  if (!NoItem) {
  			  	PG.remove('clocking_' + DT.getStamp(DT.mkDate(Item.Start)));
            PG.remove('clocking_' + DT.getStamp(DT.mkDate(Item.End)));
  			  }

  			  alert('Changes successfully saved!');
  			  PG.showPageClocking(2);
	  		});
  		}
  	}
  }
};
