/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: seditor.js 28601 2012-03-06 02:49:55Z monkey $
*/

function seditor_showimgmenu(seditorkey) {
	var imgurl = $(seditorkey + '_image_param_1').value;
	var width = parseInt($(seditorkey + '_image_param_2').value);
	var height = parseInt($(seditorkey + '_image_param_3').value);
	var extparams = '';
	if(width || height) {
		extparams = '=' + width + ',' + height
	}
	seditor_insertunit(seditorkey, '[img' + extparams + ']' + imgurl, '[/img]', null, 1);
	$(seditorkey + '_image_param_1').value = '';
	hideMenu();
}

function seditor_menu(seditorkey, tag) {
	var sel = false;
	if(!isUndefined($(seditorkey + 'message').selectionStart)) {
		sel = $(seditorkey + 'message').selectionEnd - $(seditorkey + 'message').selectionStart;
	} else if(document.selection && document.selection.createRange) {
		$(seditorkey + 'message').focus();
		var sel = document.selection.createRange();
		$(seditorkey + 'message').sel = sel;
		sel = sel.text ? true : false;
	}
	if(sel) {
		seditor_insertunit(seditorkey, '[' + tag + ']', '[/' + tag + ']');
		return;
	}
	var ctrlid = seditorkey + tag;
	var menuid = ctrlid + '_menu';
	if(!$(menuid)) {
		switch(tag) {
			case 'at':
				curatli = 0;
				atsubmitid = ctrlid + '_submit';
				setTimeout(function() {atFilter('', 'at_list','atListSet');$('atkeyword').focus();}, 100);
				str = '请输用户名:<br /><input type="text" id="atkeyword" style="width:240px" value="" class="px" onkeydown="atFilter(this.value, \'at_list\',\'atListSet\',event);" /><div class="p_pop" id="at_list" style="width:250px;"><ul><li>@朋友账号，就能提醒他来看帖子</li></ul></div>';
				submitstr = 'seditor_insertunit(\'' + seditorkey + '\', \'@\' + $(\'atkeyword\').value.replace(/<\\/?b>/g, \'\')+\' \'); hideMenu();';
				break;
			case 'url':
				str = '请输入链接地址:<br /><input type="text" id="' + ctrlid + '_param_1" sautocomplete="off" style="width: 98%" value="" class="px" />' +
					'<br />请输入链接文字:<br /><input type="text" id="' + ctrlid + '_param_2" style="width: 98%" value="" class="px" />';
				submitstr = "$('" + ctrlid + "_param_2').value !== '' ? seditor_insertunit('" + seditorkey + "', '[url='+seditor_squarestrip($('" + ctrlid + "_param_1').value)+']'+$('" + ctrlid + "_param_2').value, '[/url]', null, 1) : seditor_insertunit('" + seditorkey + "', '[url]'+$('" + ctrlid + "_param_1').value, '[/url]', null, 1);hideMenu();";
				break;
			case 'code':
			case 'quote':
				var tagl = {'quote' : '请输入要插入的引用', 'code' : '请输入要插入的代码'};
					str = tagl[tag] + ':<br /><textarea id="' + ctrlid + '_param_1" style="width: 98%" cols="50" rows="5" class="txtarea"></textarea>';
				submitstr = "seditor_insertunit('" + seditorkey + "', '[" + tag + "]'+$('" + ctrlid + "_param_1').value, '[/" + tag + "]', null, 1);hideMenu();";
				break;
			case 'img':
				str = '请输入图片地址:<br /><input type="text" id="' + ctrlid + '_param_1" style="width: 98%" value="" class="px" onchange="loadimgsize(this.value, \'' + seditorkey + '\',\'' + tag + '\')" />' +
					'<p class="mtm">宽(可选): <input type="text" id="' + ctrlid + '_param_2" style="width: 15%" value="" class="px" /> &nbsp;' +
					'高(可选): <input type="text" id="' + ctrlid + '_param_3" style="width: 15%" value="" class="px" /></p>';
				submitstr = "seditor_insertunit('" + seditorkey + "', '[img' + ($('" + ctrlid + "_param_2').value !== '' && $('" + ctrlid + "_param_3').value !== '' ? '='+$('" + ctrlid + "_param_2').value+','+$('" + ctrlid + "_param_3').value : '')+']'+seditor_squarestrip($('" + ctrlid + "_param_1').value), '[/img]', null, 1);hideMenu();";
				break;
		}
		var menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = 'p_pof upf';
		menu.style.width = '270px';
		$('append_parent').appendChild(menu);
		menu.innerHTML = '<span class="y"><a onclick="hideMenu()" class="flbc" href="javascript:;">关闭</a></span><div class="p_opt cl"><form onsubmit="' + submitstr + ';return false;" autocomplete="off"><div>' + str + '</div><div class="pns mtn"><button type="submit" id="' + ctrlid + '_submit" class="pn pnc"><strong>提交</strong></button><button type="button" onClick="hideMenu()" class="pn"><em>取消</em></button></div></form></div>';
	}
	showMenu({'ctrlid':ctrlid,'evt':'click','duration':3,'cache':0,'drag':1});
}

function seditor_squarestrip(str) {
	str = str.replace('[', '%5B');
	str = str.replace(']', '%5D');
	return str;
}

function seditor_insertunit(key, text, textend, moveend, selappend) {
	if($(key + 'message')) {
		$(key + 'message').focus();
	}
	textend = isUndefined(textend) ? '' : textend;
	moveend = isUndefined(textend) ? 0 : moveend;
	selappend = isUndefined(selappend) ? 1 : selappend;
	startlen = strlen(text);
	endlen = strlen(textend);
	if(!isUndefined($(key + 'message').selectionStart)) {
		if(selappend) {
			var opn = $(key + 'message').selectionStart + 0;
			if(textend != '') {
				text = text + $(key + 'message').value.substring($(key + 'message').selectionStart, $(key + 'message').selectionEnd) + textend;
			}
			$(key + 'message').value = $(key + 'message').value.substr(0, $(key + 'message').selectionStart) + text + $(key + 'message').value.substr($(key + 'message').selectionEnd);
			if(!moveend) {
				$(key + 'message').selectionStart = opn + strlen(text) - endlen;
				$(key + 'message').selectionEnd = opn + strlen(text) - endlen;
			}
		} else {
			text = text + textend;
			$(key + 'message').value = $(key + 'message').value.substr(0, $(key + 'message').selectionStart) + text + $(key + 'message').value.substr($(key + 'message').selectionEnd);
		}
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(!sel.text.length && $(key + 'message').sel) {
			sel = $(key + 'message').sel;
			$(key + 'message').sel = null;
		}
		if(selappend) {
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
			sel.text = text + textend;
		}
	} else {
		$(key + 'message').value += text;
	}
	hideMenu(2);
	if(BROWSER.ie) {
		doane();
	}
}

function seditor_ctlent(event, script) {
	if(event.ctrlKey && event.keyCode == 13 || event.altKey && event.keyCode == 83) {
		eval(script);
	}
}

function loadimgsize(imgurl, editor, p) {
	var editor = !editor ? editorid : editor;
	var s = new Object();
	var p = !p ? '_image' : p;
	s.img = new Image();
	s.img.src = imgurl;
	s.loadCheck = function () {
		if(s.img.complete) {
			$(editor + p + '_param_2').value = s.img.width ? s.img.width : '';
			$(editor + p + '_param_3').value = s.img.height ? s.img.height : '';
		} else {
			setTimeout(function () {s.loadCheck();}, 100);
		}
	};
	s.loadCheck();
}