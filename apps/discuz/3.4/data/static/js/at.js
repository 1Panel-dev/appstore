/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: at.js 31619 2012-09-17 01:05:07Z monkey $
*/

if(typeof EXTRAFUNC['keydown'] != "undefined") {
	EXTRAFUNC['keydown']['at'] = 'extrafunc_atMenu';
	EXTRAFUNC['keyup']['at'] = 'extrafunc_atMenuKeyUp';
	EXTRAFUNC['showEditorMenu']['at'] = 'extrafunc_atListMenu';
}

var atKeywords = null, keyMenuObj = null,atResult = [];
var curatli = 0, atliclass = '', atsubmitid = '', atkeypress = 0;

function extrafunc_atMenu() {
	if(BROWSER.opera) {
		return;
	}
	if(wysiwyg && EXTRAEVENT.shiftKey && EXTRAEVENT.keyCode == 50 && postaction && (postaction == 'newthread' || postaction == 'reply' || postaction == 'edit')) {
		keyMenu('@', atMenu);
		ctlent_enable[13] = 0;
		doane(EXTRAEVENT);
		atkeypress = 1;
	}
	if($('at_menu') && $('at_menu').style.display == '' && (EXTRAEVENT.keyCode == 38 || EXTRAEVENT.keyCode == 40 || EXTRAEVENT.keyCode == 13)) {
		doane(EXTRAEVENT);
	}
}

function extrafunc_atMenuKeyUp() {
	if(BROWSER.opera) {
		return;
	}
	if(wysiwyg && EXTRAEVENT.shiftKey && EXTRAEVENT.keyCode == 50 && postaction && (postaction == 'newthread' || postaction == 'reply' || postaction == 'edit') && !atkeypress) {
		keyBackspace();
		keyMenu('@', atMenu);
		ctlent_enable[13] = 0;
		doane(EXTRAEVENT);
	}
	if(wysiwyg && $('at_menu') && $('at_menu').style.display == '' && postaction && (postaction == 'newthread' || postaction == 'reply' || postaction == 'edit')) {
		if(EXTRAEVENT.keyCode == 32 || EXTRAEVENT.keyCode == 9 || EXTRAEVENT.keyCode == 8 && !keyMenuObj.innerHTML.substr(1).length) {
			$('at_menu').style.display = 'none';
			ctlent_enable[13] = 1;
		} else {
			atFilter(keyMenuObj.innerHTML.substr(1), 'at_menu', 'atMenuSet', EXTRAEVENT);
		}
	}
	atkeypress = 0;
}

function extrafunc_atListMenu(tag, op) {
	if(tag != 'at') {
		return false;
	}
	if(!op) {
		if($('at_menu')) {
			$('at_menu').style.display = 'none';
			ctlent_enable[13] = 1;
		}
		curatli = 0;
		setTimeout(function() {atFilter('', 'at_list','atListSet');$('atkeyword').focus();}, 100);
		return '请输用户名:<br /><input type="text" id="atkeyword" style="width:240px" value="" class="px" onkeydown="atEnter(event, \'atListSet\')" onkeyup="atFilter(this.value, \'at_list\',\'atListSet\',event, true);" /><div class="p_pop" id="at_list" style="width:250px;"><ul><li>@朋友账号，就能提醒他来看帖子</li></ul></div>';
	} else {
		if($('atkeyword').value) {
			str = '@' + $('atkeyword').value + (wysiwyg ? '&nbsp;' : ' ');
			insertText(str, strlen(str), 0, true, EXTRASEL);
		} else {
			insertText('', 0, 0, true, EXTRASEL);
		}
		checkFocus();
		return true;
	}
}

function atMenu(x, y) {
	if(!$('at_menu')) {
		div = document.createElement("div");
		div.id = "at_menu";
		document.body.appendChild(div);
		div.style.position = 'absolute';
		div.className = 'p_pop';
		div.style.zIndex = '100000';
	}
	$('at_menu').style.marginTop = (keyMenuObj.offsetHeight + 2) + 'px';
	$('at_menu').style.marginLeft = (keyMenuObj.offsetWidth + 2) + 'px';
	$('at_menu').style.left = x + 'px';
	$('at_menu').style.top = y + 'px';
	$('at_menu').style.display = '';
	$('at_menu').innerHTML = '<img src="' + IMGDIR + '/loading.gif" class="vm"> 请稍候... ';
}

function atSearch(kw, call) {
	if(atKeywords === null) {
		atKeywords = '';
		var x = new Ajax();
		x.get('misc.php?mod=getatuser&inajax=1', function(s) {
			if(s) {
				atKeywords = s.split(',');
			}
			if(call) {
				if(typeof call == 'function') {
					call();
				} else {
					eval(call);
				}
			}
		});
	}
	var lsi = 0;
	for(i in atKeywords) {
		if(atKeywords[i].indexOf(kw) !== -1 || kw === '') {
			atResult[lsi] = kw !== '' ? atKeywords[i].replace(kw, '<b>' + kw + '</b>') : atKeywords[i];
			lsi++;
			if(lsi > 10) {
				break;
			}
		}
	}
	if(kw && !lsi) {
		curatli = -1;
	}
}

function atEnter(e, call) {
	if(e) {
		if(e.keyCode == 38 && curatli > 0) {
			curatli--;
			return false;
		}
		if(e.keyCode == 40 && curatli < (atResult.length -1)) {
			curatli++;
			return false;
		}
		if(e.keyCode == 13) {
			var call = !call ? 'insertText' : call;
			if(curatli > -1) {
				eval(call+'($(\'atli_'+curatli+'\').innerText)');
			}
			hideMenu();
			doane(e);
			return true;
		}
	}
	return false;
}

function atFilter(kw, id, call, e, nae) {
	var nae = !nae ? false : nae;
	atResult = [];
	atSearch(kw, function () { atFilter(kw, id, call); });
	if(nae || !atEnter(e, call)) {
		var newlist = '';
		if(atResult.length) {
			$(id).style.visibility = 'visible';
			for(i in atResult) {
				var atclass = i == curatli ? ' class="a"' : '';
				newlist += '<li><a href="javascript:;" id="atli_'+i+'"'+atclass+' onclick="'+call+'(this.innerText)">' + atResult[i] + '</a></li>';
			}
			$(id).innerHTML = '<ul>' + newlist + '<li class="xg1">@朋友账号，就能提醒他来看帖子</li></ul>';
		} else {
			$(id).style.visibility = 'hidden';
		}
	}
}

function atListSet(kw) {
	$('atkeyword').value = kw;
	if(!atsubmitid) {
		$(editorid + '_at_submit').click();
	} else {
		$(atsubmitid).click();
	}
}

function atMenuSet(kw) {
	keyMenuObj.innerHTML = '@' + kw + (wysiwyg ? '&nbsp;' : ' ');
	$('at_menu').style.display = 'none';
	ctlent_enable[13] = 1;
	curatli = 0;
	if(BROWSER.firefox) {
		var selection = editwin.getSelection();
		var range = selection.getRangeAt(0);
		var tmp = keyMenuObj.firstChild;
		range.setStart(tmp, keyMenuObj.innerHTML.length - 5);
		range.setEnd(tmp, keyMenuObj.innerHTML.length - 5);
		selection.removeAllRanges();
		selection.addRange(range);
	}
	checkFocus();
}