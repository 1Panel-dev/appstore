var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function $(id) {
	return document.getElementById(id);
}

function fetchOffset(obj) {
	var left_offset = obj.offsetLeft;
	var top_offset = obj.offsetTop;
	while((obj = obj.offsetParent) != null) {
		left_offset += obj.offsetLeft;
		top_offset += obj.offsetTop;
	}
	return { 'left' : left_offset, 'top' : top_offset };
}

function _attachEvent(obj, evt, func) {
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(obj.attachEvent) {
		obj.attachEvent("on" + evt, func);
	}
}

function strlen(str) {
	return (is_ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

var menus = new menu_handler();

function menu_handler() {
	this.menu = Array();
}

function menuitems() {
	this.ctrlobj = null,
	this.menuobj = null;
	this.parentids = Array();
	this.allowhide = 1;
	this.hidelock = 0;
	this.clickstatus = 0;
}

function menuobjpos(id, offset) {
	if(!menus.menu[id]) {
		return;
	}
	if(!offset) {
		offset = 0;
	}
	var showobj = menus.menu[id].ctrlobj;
	var menuobj = menus.menu[id].menuobj;
	showobj.pos = fetchOffset(showobj);
	showobj.X = showobj.pos['left'];
	showobj.Y = showobj.pos['top'];
	showobj.w = showobj.offsetWidth;
	showobj.h = showobj.offsetHeight;
	menuobj.w = menuobj.offsetWidth;
	menuobj.h = menuobj.offsetHeight;
	if(offset < 3) {
		menuobj.style.left = (showobj.X + menuobj.w > document.body.clientWidth) && (showobj.X + showobj.w - menuobj.w >= 0) ? showobj.X + showobj.w - menuobj.w + 'px' : showobj.X + 'px';
		menuobj.style.top = offset == 1 ? showobj.Y + 'px' : (offset == 2 || ((showobj.Y + showobj.h + menuobj.h > document.documentElement.scrollTop + document.documentElement.clientHeight) && (showobj.Y - menuobj.h >= 0)) ? (showobj.Y - menuobj.h) + 'px' : showobj.Y + showobj.h + 'px');
	} else if(offset == 3) {
		menuobj.style.left = (document.body.clientWidth - menuobj.clientWidth) / 2 + document.body.scrollLeft + 'px';
		menuobj.style.top = (document.body.clientHeight - menuobj.clientHeight) / 2 + document.body.scrollTop + 'px';
	} else if(offset == 4) {
		menuobj.style.left = (showobj.X + menuobj.w > document.body.clientWidth) && (showobj.X + showobj.w - menuobj.w >= 0) ? showobj.X + showobj.w - menuobj.w + 'px' : showobj.X + showobj.w + 'px';
		menuobj.style.top = showobj.Y + 'px';
	}
	if(menuobj.style.clip && !is_opera) {
		menuobj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function showmenu(event, id, click, position) {
	if(isUndefined(click)) click = false;
	if(!menus.menu[id]) {
		menus.menu[id] = new menuitems();
		menus.menu[id].ctrlobj = $(id);
		if(!menus.menu[id].ctrlobj.getAttribute('parentmenu')) {
			menus.menu[id].parentids = Array();
		} else {
			menus.menu[id].parentids = menus.menu[id].ctrlobj.getAttribute('parentmenu').split(',');
		}
		menus.menu[id].menuobj = $(id + '_menu');
		menus.menu[id].menuobj.style.position = 'absolute';
		if(event.type == 'mouseover') {
			_attachEvent(menus.menu[id].ctrlobj, 'mouseout', function() { setTimeout(function() {hidemenu(id)}, 100); });
			_attachEvent(menus.menu[id].menuobj, 'mouseover', function() { lockmenu(id, 0); });
			_attachEvent(menus.menu[id].menuobj, 'mouseout', function() { lockmenu(id, 1);setTimeout(function() {hidemenu(id)}, 100); });
		} else if(click || event.type == 'click') {
			menus.menu[id].clickstatus = 1;
			lockmenu(id, 0);
		}
	} else if(menus.menu[id].clickstatus == 1) {
		lockmenu(id, 1);
		hidemenu(id);
		menus.menu[id].clickstatus = 0;
		return;
	}

	menuobjpos(id, position);
	menus.menu[id].menuobj.style.display = '';
}

function hidemenu(id) {
	if(!menus.menu[id] || !menus.menu[id].allowhide || menus.menu[id].hidelock) {
		return;
	}
	menus.menu[id].menuobj.style.display = 'none';
}

function lockmenu(id, value) {
	if(!menus.menu[id]) {
		return;
	}
	for(i = 0;i < menus.menu[id].parentids.length;i++) {
		menus.menu[menus.menu[id].parentids[i]].hidelock = value == 0 ? 1 : 0;
	}
	menus.menu[id].allowhide = value;
}

var lang = new Array();
function insertunit(text, textend, moveend) {
	$('pm_textarea').focus();
	textend = isUndefined(textend) ? '' : textend;
	moveend = isUndefined(textend) ? 0 : moveend;
	startlen = strlen(text);
	endlen = strlen(textend);
	if(!isUndefined($('pm_textarea').selectionStart)) {
		var opn = $('pm_textarea').selectionStart + 0;
		if(textend != '') {
			text = text + $('pm_textarea').value.substring($('pm_textarea').selectionStart, $('pm_textarea').selectionEnd) + textend;
		}
		$('pm_textarea').value = $('pm_textarea').value.substr(0, $('pm_textarea').selectionStart) + text + $('pm_textarea').value.substr($('pm_textarea').selectionEnd);
		if(!moveend) {
			$('pm_textarea').selectionStart = opn + strlen(text) - endlen;
			$('pm_textarea').selectionEnd = opn + strlen(text) - endlen;
		}
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(textend != '') {
			text = text + sel.text + textend;
		}
		sel.text = text.replace(/\r?\n/g, '\r\n');
		if(!moveend) {
			sel.moveStart('character', -endlen);
			sel.moveEnd('character', -endlen);
		}
		sel.select();
	} else {
		$('pm_textarea').value += text;
	}
}

function getSel() {
	if(!isUndefined($('pm_textarea').selectionStart)) {
		return $('pm_textarea').value.substr($('pm_textarea').selectionStart, $('pm_textarea').selectionEnd - $('pm_textarea').selectionStart);
	} else if(document.selection && document.selection.createRange) {
		return document.selection.createRange().text;
	} else if(window.getSelection) {
		return window.getSelection() + '';
	} else {
		return false;
	}
}

function insertlist(type) {
	txt = getSel();
	type = isUndefined(type) ? '' : '=' + type;
	if(txt) {
		var regex = new RegExp('([\r\n]+|^[\r\n]*)(?!\\[\\*\\]|\\[\\/?list)(?=[^\r\n])', 'gi');
		txt = '[list' + type + ']\n' + txt.replace(regex, '$1[*]') + '\n' + '[/list]';
		insertunit(txt);
	} else {
		insertunit('[list' + type + ']\n', '[/list]');

		while(listvalue = prompt(lang['pm_prompt_list'], '')) {
			if(is_opera > 8) {
				listvalue = '\n' + '[*]' + listvalue;
				insertunit(listvalue);
			} else {
				listvalue = '[*]' + listvalue + '\n';
				insertunit(listvalue);
			}
		}
	}
}

function inserttag(tag, type) {
	txt = getSel();
	type = isUndefined(type) ? 0 : type;
	if(!type) {
		if(!txt) {
			txt = prompt(lang['pm_prompt_' + tag], '')
		}
		if(txt) {
			insertunit('[' + tag + ']' + txt + '[/' + tag + ']');
		}
	} else {
		txt1 = prompt(lang['pm_prompt_' + tag], '');
		if(!txt) {
			txt = txt1;
		}
		if(txt1) {
			insertunit('[' + tag + '=' + txt1 + ']' + txt + '[/' + tag + ']');
		}
	}
}