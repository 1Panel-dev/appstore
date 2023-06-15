/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: editor.js 34614 2014-06-12 02:48:35Z nemohou $
*/

var editorcurrentheight = 400, editorminheight = 400, savedataInterval = 30, editbox = null, editwin = null, editdoc = null, editcss = null, savedatat = null, savedatac = 0, autosave = 1, framemObj = null, cursor = -1, stack = [], initialized = false, postSubmited = false, editorcontroltop = false, editorcontrolwidth = false, editorcontrolheight = false, editorisfull = 0, fulloldheight = 0, savesimplodemode = null;
EXTRAFUNC['keydown'] = [];
EXTRAFUNC['keyup'] = [];
EXTRAFUNC['mouseup'] = [];
EXTRAFUNC['showEditorMenu'] = [];
var EXTRASELECTION = '', EXTRASEL = null;

function newEditor(mode, initialtext) {
	wysiwyg = parseInt(mode);
	if(!(BROWSER.ie || BROWSER.firefox || (BROWSER.opera >= 9 || BROWSER.rv))) {
		allowswitcheditor = wysiwyg = 0;
	}
	if(!allowswitcheditor) {
		$(editorid + '_switcher').style.display = 'none';
	}

	if(wysiwyg) {
		if($(editorid + '_iframe')) {
			editbox = $(editorid + '_iframe');
		} else {
			var iframe = document.createElement('iframe');
			iframe.frameBorder = '0';
			iframe.tabIndex = 2;
			iframe.hideFocus = true;
			iframe.style.display = 'none';
			editbox = textobj.parentNode.appendChild(iframe);
			editbox.id = editorid + '_iframe';
		}

		editwin = editbox.contentWindow;
		editdoc = editwin.document;
		writeEditorContents(isUndefined(initialtext) ?  textobj.value : initialtext);
	} else {
		editbox = editwin = editdoc = textobj;
		if(!isUndefined(initialtext)) {
			writeEditorContents(initialtext);
		}
		addSnapshot(textobj.value);
	}
	setEditorEvents();
	initEditor();
}

function setEditorTip(s) {
	$(editorid + '_tip').innerHTML = '&nbsp;' + s;
}

function initEditor() {
	if(BROWSER.other) {
		$(editorid + '_controls').style.display = 'none';
		return;
	}
	var buttons = $(editorid + '_controls').getElementsByTagName('a');
	initesbar();
	for(var i = 0; i < buttons.length; i++) {
		if(buttons[i].id.indexOf(editorid + '_') != -1) {
			buttons[i].href = 'javascript:;';
			if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'fullswitcher') {
				buttons[i].innerHTML = !editorisfull ? '全屏' : '返回';
				buttons[i].onmouseover = function(e) {setEditorTip(editorisfull ? '恢复编辑器大小' : '全屏方式编辑');};
				buttons[i].onclick = function(e) {editorfull();doane();}
			} else if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'simple') {
				buttons[i].innerHTML = !simplodemode ? '常用' : '高级';
				buttons[i].onclick = function(e) {editorsimple();doane();}
			} else {
				_attachEvent(buttons[i], 'mouseover', function(e) {setEditorTip(BROWSER.ie ? window.event.srcElement.title : e.target.title);});
				if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'url') {
					buttons[i].onclick = function(e) {discuzcode('unlink');discuzcode('url');doane();};
				} else {
					if(!buttons[i].getAttribute('init')) {
						buttons[i].onclick = function(e) {discuzcode(this.id.substr(this.id.indexOf('_') + 1));doane();};
					}
				}
			}
			buttons[i].onmouseout = function(e) {setEditorTip('');};
		}
	}
	setUnselectable($(editorid + '_controls'));
	if(editorcontroltop === false && (BROWSER.ie && BROWSER.ie > 6 || !BROWSER.ie)) {
		seteditorcontrolpos();
		var obj = wysiwyg ? editwin.document.body.parentNode : $(editorid + '_textarea');
		editorcontrolwidth = $(editorid + '_controls').clientWidth - 8;
		ctrlmObj = document.createElement('div');
		ctrlmObj.style.display = 'none';
		ctrlmObj.style.height = $(editorid + '_controls').clientHeight + 'px';
		ctrlmObj.id = editorid + '_controls_mask';
		$(editorid + '_controls').parentNode.insertBefore(ctrlmObj, $(editorid + '_controls'));
		_attachEvent(window, 'scroll', function () { editorcontrolpos(); }, document);
	}
	if($(editorid + '_fullswitcher') && BROWSER.ie && BROWSER.ie < 7) {
		$(editorid + '_fullswitcher').onclick = function () {
			showDialog('您的浏览器不支持此功能，请升级浏览器版本', 'notice', '友情提示');
		};
		$(editorid + '_fullswitcher').className = 'xg1';
	}
	if($(editorid + '_svdsecond') && savedatat === null) {
		savedatac = savedataInterval;
		autosave = !getcookie('editorautosave_' + editorid) || getcookie('editorautosave_' + editorid) == 1 ? 1 : 0;
		savedataTime();
		savedatat = setInterval("savedataTime()", 10000);
	}
	checkFocus();
}

function initesbar() {
	if(!$(editorid + '_adv_s3')) {
		return;
	}
	var buttons = $(editorid + '_adv_s3').getElementsByTagName('A');
	var s = '';
	for(i = 0;i < buttons.length;i++) {
		if(i/2 == parseInt(i/2)) {
			s += '<p>';
		}
		s += buttons[i].outerHTML;
	}
	$(editorid + '_adv_s3').innerHTML = s;
	for(var i = 0; i < buttons.length; i++) {
		if(buttons[i].id.indexOf(editorid + '_') != -1) {
			buttons[i].href = 'javascript:;';
			if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'fullswitcher') {
			} else if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'simple') {
			} else {
				_attachEvent(buttons[i], 'mouseover', function(e) {setEditorTip(BROWSER.ie ? window.event.srcElement.title : e.target.title);});
				if(buttons[i].id.substr(buttons[i].id.indexOf('_') + 1) == 'url') {
					buttons[i].onclick = function(e) {discuzcode('unlink');discuzcode('url');doane();};
				} else {
					if(!buttons[i].getAttribute('init')) {
						buttons[i].onclick = function(e) {discuzcode(this.id.substr(this.id.indexOf('_') + 1));doane();};
					}
				}
			}
			buttons[i].onmouseout = function(e) {setEditorTip('');};
		}
	}
}

function savedataTime() {
	if(!autosave) {
		$(editorid + '_svdsecond').innerHTML = '<a title="点击开启自动保存" href="javascript:;" onclick="setAutosave()">开启自动保存</a> ';
		return;
	}
	if(!savedatac) {
		savedatac = savedataInterval;
		saveData();
		d = new Date();
		var h = d.getHours();
		var m = d.getMinutes();
		h = h < 10 ? '0' + h : h;
		m = m < 10 ? '0' + m : m;
		setEditorTip('数据已于 ' + h + ':' + m + ' 保存');
	}
	$(editorid + '_svdsecond').innerHTML = '<a title="点击关闭自动保存" href="javascript:;" onclick="setAutosave()">' + savedatac + ' 秒后保存</a> ';
	savedatac -= 10;
}

function setAutosave() {
	autosave = !autosave;
	setEditorTip(autosave ? '数据自动保存已开启' : '数据自动保存已关闭');
	setcookie('editorautosave_' + editorid, autosave ? 1 : -1, 2592000);
	savedataTime();
}

function unloadAutoSave() {
	if(autosave) {
		saveData();
	}
}

function seteditorcontrolpos() {
	var objpos = fetchOffset($(editorid + '_controls'));
	editorcontroltop = objpos['top'];
}

function editorcontrolpos() {
	if(editorisfull) {
		return;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	if(scrollTop > editorcontroltop && editorcurrentheight > editorminheight) {
		$(editorid + '_controls').style.position = 'fixed';
		$(editorid + '_controls').style.top = '0px';
		$(editorid + '_controls').style.width = editorcontrolwidth + 'px';
		$(editorid + '_controls_mask').style.display = '';
	} else {
		$(editorid + '_controls').style.position = $(editorid + '_controls').style.top = $(editorid + '_controls').style.width = '';
		$(editorid + '_controls_mask').style.display = 'none';
	}
}

function editorsize(op, v) {
	var obj = wysiwyg ? editwin.document.body.parentNode : $(editorid + '_textarea');
	var editorheight = obj.clientHeight;
	if(!v) {
		if(op == '+') {
			editorheight += 200;
		} else{
			editorheight -= 200;
		}
	} else {
		editorheight = v;
	}
	editorcurrentheight = editorheight > editorminheight ? editorheight : editorminheight;
	if($(editorid + '_iframe')) {
		$(editorid + '_iframe').style.height = $(editorid + '_iframe').contentWindow.document.body.style.height = editorcurrentheight + 'px';
	}
	if(framemObj) {
		framemObj.style.height = editorcurrentheight + 'px';
	}
	$(editorid + '_textarea').style.height = editorcurrentheight + 'px';
}

var editorsizepos = [];
function editorresize(e, op) {
	op = !op ? 1 : op;
	e = e ? e : window.event;
	if(op == 1) {
		if(wysiwyg) {
			var objpos = fetchOffset($(editorid + '_iframe'));
			framemObj = document.createElement('div');
			framemObj.style.width = $(editorid + '_iframe').clientWidth + 'px';
			framemObj.style.height = $(editorid + '_iframe').clientHeight + 'px';
			framemObj.style.position = 'absolute';
			framemObj.style.left = objpos['left'] + 'px';
			framemObj.style.top = objpos['top'] + 'px';
			$('append_parent').appendChild(framemObj);
		} else {
			framemObj = null;
		}
		editorsizepos = [e.clientY, editorcurrentheight, framemObj];
		document.onmousemove = function(e) {try{editorresize(e, 2);}catch(err){}};
		document.onmouseup = function(e) {try{editorresize(e, 3);}catch(err){}};
		doane(e);
	}else if(op == 2 && editorsizepos !== []) {
		var dragnow = e.clientY;
		editorsize('', editorsizepos[1] + dragnow - editorsizepos[0]);
		doane(e);
	}else if(op == 3) {
		if(wysiwyg) {
			$('append_parent').removeChild(editorsizepos[2]);
		}
		editorsizepos = [];
		document.onmousemove = null;
		document.onmouseup = null;
	}
}

function editorfull(op) {
	var op = !op ? 0 : op, control = $(editorid + '_controls'), area = $(editorid + '_textarea').parentNode, bbar = $(editorid + '_bbar'), iswysiwyg = wysiwyg;
	if(op) {
		var editorheight = document.documentElement.clientHeight - control.offsetHeight - bbar.offsetHeight - parseInt(getCurrentStyle(area, 'paddingTop', 'padding-top')) - parseInt(getCurrentStyle(area, 'paddingBottom', 'padding-bottom'));
		area.style.position = 'fixed';
		area.style.top = control.offsetHeight + 'px';
		area.style.height = editorheight + 'px';
		editorsize('', editorheight);
		bbar.style.position = 'fixed';
		bbar.style.top = (document.documentElement.clientHeight - bbar.offsetHeight) + 'px';
		return;
	}
	if(!editorisfull) {
		savesimplodemode = 0;
		if(simplodemode) {
			savesimplodemode = 1;
			editorsimple();
		}
		$(editorid + '_simple').style.visibility = 'hidden';
		fulloldheight = editorcurrentheight;
		document.body.style.overflow = 'hidden';
		document.body.scroll = 'no';
		control.style.position = 'fixed';
		control.style.top = '0px';
		control.style.left = '0px';
		control.style.width = '100%';
		control.style.minWidth = '800px';
		area.style.backgroundColor = $(editorid + '_textarea') ? getCurrentStyle($(editorid + '_textarea'), 'backgroundColor', 'background-color') : '#fff';
		$(editorid + '_switcher').style.paddingRight = '10px';
		var editorheight = document.documentElement.clientHeight - control.offsetHeight - bbar.offsetHeight - parseInt(getCurrentStyle(area, 'paddingTop', 'padding-top')) - parseInt(getCurrentStyle(area, 'paddingBottom', 'padding-bottom'));
		area.style.position = 'fixed';
		area.style.top = control.offsetHeight + 'px';
		area.style.left = '0px';
		area.style.width = '100%';
		area.style.height = editorheight + 'px';
		editorsize('', editorheight);
		bbar.style.position = 'fixed';
		bbar.style.top = (document.documentElement.clientHeight - bbar.offsetHeight) + 'px';
		bbar.style.left = '0px';
		bbar.style.width = '100%';
		control.style.zIndex = '500';
		area.style.zIndex = bbar.style.zIndex = '200';
		if($(editorid + '_resize')) {
			$(editorid + '_resize').style.display = 'none';
		}
		window.onresize = function() { editorfull(1); };
		editorisfull = 1;
	} else {
		if(savesimplodemode) {
			editorsimple();
		}
		$(editorid + '_simple').style.visibility = 'visible';
		window.onresize = null;
		document.body.style.overflow = 'auto';
		document.body.scroll = 'yes';
		control.style.position = control.style.top = control.style.left = control.style.width = control.style.minWidth = control.style.zIndex =
			area.style.position = area.style.top = area.style.left = area.style.width = area.style.height = area.style.zIndex =
			bbar.style.position = bbar.style.top = bbar.style.left = bbar.style.width = bbar.style.zIndex = '';
		editorheight = fulloldheight;
		$(editorid + '_switcher').style.paddingRight = '0px';
		editorsize('', editorheight);
		if($(editorid + '_resize')) {
			$(editorid + '_resize').style.display = '';
		}
		editorisfull = 0;
		editorcontrolpos();
	}
	$(editorid + '_fullswitcher').innerHTML = editorisfull ? '返回' : '全屏';
	initesbar();
}

function editorsimple() {
	if($(editorid + '_body').className == 'edt') {
		v = 'none';
		$(editorid + '_simple').innerHTML = '高级';
		$(editorid + '_body').className = 'edt simpleedt';
		$(editorid + '_adv_s1').className = 'b2r';
		$(editorid + '_adv_s2').className = 'b2r nbl';
		if(allowswitcheditor) {
			$(editorid + '_switcher').style.display = 'none';
		}
		$(editorid + '_adv_s3').className = 'b2r esbs';
		simplodemode = 1;
	} else {
		v = '';
		$(editorid + '_simple').innerHTML = '常用';
		$(editorid + '_body').className = 'edt';
		$(editorid + '_adv_s1').className = 'b1r';
		$(editorid + '_adv_s2').className = 'b2r nbr nbl';
		if(allowswitcheditor) {
			$(editorid + '_switcher').style.display = '';
		}
		$(editorid + '_adv_s3').className = 'b2r esb';
		simplodemode = 0;
	}
	setcookie('editormode_' + editorid, simplodemode ? 1 : -1, 2592000);
	for(i = 1;i <= 9;i++) {
		if($(editorid + '_adv_' + i)) {
			$(editorid + '_adv_' + i).style.display = v;
		}
	}
	var btns = $(editorid + '_adv_s3').getElementsByTagName('A');
	for(i = 0;i < btns.length;i++) {
		if(btns[i].getAttribute('adv')) {
			btns[i].style.display = v;
		}
	}
}

function pasteWord(str) {
	var mstest = /<\w[^>]* class="?[MsoNormal|xl]"?/gi;
	if(mstest.test(str)){
		str = str.replace(/<!--\[if[\s\S]+?<!\[endif\]-->/gi, "");
		str = str.replace(/<(\w[^>]*) class=([^ |>]*)([^>]*)/gi, "<$1$3");
		str = str.replace(/<(\w[^>]*) style="([^"]*)"([^>]*)/gi, function ($1, $2, $3, $4) {
			var style = '';
			re = new RegExp('(^|[;\\s])color:\\s*([^;]+);?', 'ig');
			match = re.exec($3);
			if(match != null) {
				style += 'color:' + match[2] + ';';
			}
			re = new RegExp('(^|[;\\s])text-indent:\\s*([^;]+);?', 'ig');
			match = re.exec($3);
			if(match != null) {
				style += 'text-indent:' + parseInt(parseInt(match[2]) / 10) + 'em;';
			}
			re = new RegExp('(^|[;\\s])font-size:\\s*([^;]+);?', 'ig');
			match = re.exec($3);
			if(match != null) {
				style += 'font-size:' + match[2] + ';';
			}
			if(style) {
				style = ' style="' + style + '"';
			}
			return '<' + $2 + style + $4;
		});
		str = str.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");
		str = str.replace(/<\\?\?xml[^>]*>/gi, "");
		str = str.replace(/<\/?\w+:[^>]*>/gi, "");
		str = str.replace(/&nbsp;/, " ");
		var re = new RegExp("(<P)([^>]*>.*?)(<\/P>)", 'ig');
		str = str.replace(re, "<div$2</div>");
		if(!wysiwyg) {
			str = html2bbcode(str);
		}
		insertText(str, str.length, 0);
	}
}

var ctlent_enable = {8:1,9:1,13:1};
function ctlent(event) {
	if(postSubmited == false && (event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83) && editorsubmit) {
		if(in_array(editorsubmit.name, ['topicsubmit', 'replysubmit', 'editsubmit']) && !validate(editorform)) {
			doane(event);
			return;
		}
		postSubmited = true;
		editorsubmit.disabled = true;
		editorform.submit();
		return;
	}
	if(ctlent_enable[9] && event.keyCode == 9) {
		if(!wysiwyg) {
			insertText('\t', 1, 0);
		}
		doane(event);
	}
	if(ctlent_enable[8] && event.keyCode == 8 && wysiwyg) {
		var sel = getSel();
		if(sel) {
			insertText('', sel.length - 1, 0);
			doane(event);
		}
	}
}

function keyBackspace() {
	if(!wysiwyg) {
		return;
	}
	if(BROWSER.ie) {
		sel = editdoc.selection.createRange();
		sel.moveStart('character', -1);
		sel.moveEnd('character', 0);
		sel.select();
		editdoc.selection.clear();
	} else {
		editdoc.execCommand('delete', false, true);
	}
}

function keyMenu(code, func) {
	var km = 'kM' + Math.random();
	var hs = '<span id="' + km + '">' + code + '</span>';
	if(BROWSER.ie) {
		var range = document.selection.createRange();
		range.pasteHTML(hs);
		range.moveToElementText(editdoc.getElementById(km));
		range.moveStart("character");
		range.select();
	} else {
		var selection = editwin.getSelection();
		var range = selection.getRangeAt(0);
		var fragment = range.createContextualFragment(hs);
		range.insertNode(fragment);
		var tmp = editdoc.getElementById(km).firstChild;
		range.setStart(tmp, 1);
		range.setEnd(tmp, 1);
		selection.removeAllRanges();
		selection.addRange(range);
	}
	keyMenuObj = editdoc.getElementById(km);
	var b = fetchOffset(editbox);
	var o = fetchOffset(keyMenuObj);
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	func(b.left + o.left, b.top + o.top - scrollTop);
}

function checkFocus() {
	if(wysiwyg) {
		if(BROWSER.rv) {
			return;
		}
		try {
			editwin.document.body.focus();
		} catch(e) {
			editwin.focus();
		}
	} else {
		textobj.focus();
	}
}

function checklength(theform) {
	var message = wysiwyg ? html2bbcode(getEditorContents()) : (!theform.parseurloff.checked ? parseurl(theform.message.value) : theform.message.value);
	showDialog('当前长度: ' + mb_strlen(message) + ' 字节，' + (postmaxchars != 0 ? '系统限制: ' + postminchars + ' 到 ' + postmaxchars + ' 字节。' : ''), 'notice', '字数检查');
}

function setUnselectable(obj) {
	if(BROWSER.ie && BROWSER.ie > 4 && typeof obj.tagName != 'undefined') {
		if(obj.hasChildNodes()) {
			for(var i = 0; i < obj.childNodes.length; i++) {
				setUnselectable(obj.childNodes[i]);
			}
		}
		if(obj.tagName != 'INPUT') {
			obj.unselectable = 'on';
		}
	}
}

function writeEditorContents(text) {
	if(wysiwyg) {
		if(text == '' && (BROWSER.firefox || BROWSER.opera)) {
			text = '<p></p>';
		}
		if(initialized && !(BROWSER.firefox && BROWSER.firefox >= '3' || BROWSER.opera)) {
			editdoc.body.innerHTML = text;
		} else {
			text = '<!DOCTYPE html PUBLIC "-/' + '/W3C/' + '/DTD XHTML 1.0 Transitional/' + '/EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' +
				'<html><head id="editorheader"><meta http-equiv="Content-Type" content="text/html; charset=' + charset + '" />' +
				(BROWSER.ie && BROWSER.ie > 7 ? '<meta http-equiv="X-UA-Compatible" content="IE=7" />' : '' ) +
				'<link rel="stylesheet" type="text/css" href="misc.php?css=' + STYLEID + '_wysiwyg&' + VERHASH + '" />' +
				(BROWSER.ie ? '<script>window.onerror = function() { return true; }</script>' : '') +
				'</head><body>' + text + '</body></html>';
			editdoc.designMode = allowhtml ? 'on' : 'off';
			editdoc = editwin.document;
			editdoc.open('text/html', 'replace');
			editdoc.write(text);
			editdoc.close();
			if(!BROWSER.ie) {
				var scriptNode = document.createElement("script");
				scriptNode.type = "text/javascript";
				scriptNode.text = 'window.onerror = function() { return true; }';
				editdoc.getElementById('editorheader').appendChild(scriptNode);
			}
			editdoc.body.contentEditable = true;
			editdoc.body.spellcheck = false;
			initialized = true;
			if(BROWSER.safari) {
				editdoc.onclick = safariSel;
			}
		}
		if(BROWSER.ie && BROWSER.ie <= 8) {
			checkpostbg = /<style[^>]+name="editorpostbg"[^>]*>body{background-image:url\("([^\[\<\r\n;'\"\?\(\)]+?)"\);}<\/style>/ig;
			var matches = checkpostbg.exec(text);
			if(matches != null) {
				editdoc.body.innerHTML += '<style type="text/css" name="editorpostbg">body{background-image:url("'+matches[1]+'");}</style>';
			}
		}
	} else {
		textobj.value = text;
	}

	setEditorStyle();

}

function safariSel(e) {
	e = e.target;
	if(e.tagName.match(/(img|embed)/i)) {
		var sel = editwin.getSelection(),rng= editdoc.createRange(true);
		rng.selectNode(e);
		sel.removeAllRanges();
		sel.addRange(rng);
	}
}

function getEditorContents() {
	return wysiwyg ? editdoc.body.innerHTML : editdoc.value;
}

function setEditorStyle() {
	if(wysiwyg) {
		textobj.style.display = 'none';
		editbox.style.display = '';
		editbox.className = textobj.className;
		if(BROWSER.ie) {
			editdoc.body.style.border = '0px';
			editdoc.body.addBehavior('#default#userData');
			try{$('subject').focus();} catch(e) {editwin.focus();}
		}
		if($(editorid + '_iframe')) {
			$(editorid + '_iframe').style.height = $(editorid + '_iframe').contentWindow.document.body.style.height = editorcurrentheight + 'px';
		}
	} else {
		var iframe = textobj.parentNode.getElementsByTagName('iframe')[0];
		if(iframe) {
			textobj.style.display = '';
			iframe.style.display = 'none';
		}
		if(BROWSER.ie) {
			try{
				$('subject').focus();
			} catch(e) {}
		}
	}
	if($('at_menu')) {
		$('at_menu').style.display = 'none';
	}
}

function setEditorEvents() {
	if(BROWSER.firefox || BROWSER.opera) {
		editdoc.addEventListener('mouseup', function(e) {mouseUp(e)}, true);
		editdoc.addEventListener('keyup', function(e) {keyUp(e)}, true);
		editwin.addEventListener('keydown', function(e) {keyDown(e)}, true);
	} else if(editdoc.attachEvent) {
		try{
			editdoc.attachEvent('onmouseup', mouseUp);
			editdoc.attachEvent('onkeyup', keyUp);
			editdoc.attachEvent('onkeydown', keyDown);
		} catch(e) {}
	}
}

function mouseUp(event) {
	if(wysiwyg) {
		setContext();
	}
	for(i in EXTRAFUNC['mouseup']) {
		EXTRAEVENT = event;
		try {
			eval(EXTRAFUNC['mouseup'][i] + '()');
		} catch(e) {}
	}
}

function keyUp(event) {
	if(wysiwyg) {
		setContext();
	}
	for(i in EXTRAFUNC['keyup']) {
		EXTRAEVENT = event;
		try {
			eval(EXTRAFUNC['keyup'][i] + '()');
		} catch(e) {}
	}
}

function keyDown(event) {
	ctlent(event);
	for(i in EXTRAFUNC['keydown']) {
		EXTRAEVENT = event;
		try {
			eval(EXTRAFUNC['keydown'][i] + '()');
		} catch(e) {}
	}
}

function wrapTags(tagname, useoption, selection) {
	if(isUndefined(selection)) {
		var selection = getSel();
		if(selection === false) {
			selection = '';
		} else {
			selection += '';
		}
	}

	if(useoption !== false) {
		var opentag = '[' + tagname + '=' + useoption + ']';
	} else {
		var opentag = '[' + tagname + ']';
	}

	var closetag = '[/' + tagname + ']';
	var text = opentag + selection + closetag;

	insertText(text, strlen(opentag), strlen(closetag), in_array(tagname, ['code', 'quote', 'free', 'hide']) ? true : false);
}

function applyFormat(cmd, dialog, argument) {
	if(wysiwyg) {
		editdoc.execCommand(cmd, (isUndefined(dialog) ? false : dialog), (isUndefined(argument) ? true : argument));
		return;
	}
	switch(cmd) {
		case 'bold':
		case 'italic':
		case 'underline':
		case 'strikethrough':
			wrapTags(cmd.substr(0, 1), false);
			break;
		case 'inserthorizontalrule':
			insertText('[hr]', 4, 0);
			break;
		case 'justifyleft':
		case 'justifycenter':
		case 'justifyright':
			wrapTags('align', cmd.substr(7));
			break;
		case 'fontname':
			wrapTags('font', argument);
			break;
		case 'fontsize':
			wrapTags('size', argument);
			break;
		case 'forecolor':
			wrapTags('color', argument);
			break;
		case 'hilitecolor':
		case 'backcolor':
			wrapTags('backcolor', argument);
			break;
	}
}

function isEmail(email) {
	return email.length > 6 && /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(email);
}

function insertAttachTag(aid) {
	var txt = '[attach]' + aid + '[/attach]';
	if(wysiwyg) {
		insertText(txt, false);
	} else {
		insertText(txt, strlen(txt), 0);
	}
}

function insertAttachimgTag(aid) {
	if(wysiwyg) {
		insertText('<img src="' + $('image_' + aid).src + '" border="0" aid="attachimg_' + aid + '" alt="" />', false);
	} else {
		var txt = '[attachimg]' + aid + '[/attachimg]';
		insertText(txt, strlen(txt), 0);
	}
}

function insertSmiley(smilieid) {
	checkFocus();
	var src = $('smilie_' + smilieid).src;
	var code = $('smilie_' + smilieid).alt;
	if(wysiwyg && allowsmilies && (!$('smileyoff') || $('smileyoff').checked == false)) {
		insertText('<img src="' + src + '" border="0" smilieid="' + smilieid + '" alt="" />', false);
	} else {
		code += ' ';
		insertText(code, strlen(code), 0);
	}
	hideMenu();
}

function discuzcode(cmd, arg) {
	if(cmd != 'redo') {
		addSnapshot(getEditorContents());
	}

	checkFocus();

	if(in_array(cmd, ['sml', 'inserthorizontalrule', 'url', 'quote', 'code', 'free', 'hide', 'aud', 'vid', 'fls', 'beginning', 'attach', 'image', 'pasteword', 'index', 'postbg', 'password']) || typeof EXTRAFUNC['showEditorMenu'][cmd] != 'undefined' || cmd == 'tbl' || in_array(cmd, ['fontname', 'fontsize', 'forecolor', 'backcolor']) && !arg) {
		showEditorMenu(cmd);
		return;
	} else if(cmd.substr(0, 3) == 'cst') {
		showEditorMenu(cmd.substr(5), cmd.substr(3, 1));
		return;
	} else if(cmd == 'page') {
		if(wysiwyg) {
			str = '<br /><p>[page]</p>';
		} else {
			str = '\n\n[page]';
		}
		insertText(str, strlen(str), 0);
	} else if(cmd == 'autotypeset') {
		autoTypeset();
		return;
	} else if(!wysiwyg && cmd == 'removeformat') {
		var simplestrip = new Array('b', 'i', 'u');
		var complexstrip = new Array('font', 'color', 'backcolor', 'size', 'align', 'float');

		var str = getSel();
		if(str === false) {
			return;
		}
		for(var tag in simplestrip) {
			str = stripSimple(simplestrip[tag], str);
		}
		for(var tag in complexstrip) {
			str = stripComplex(complexstrip[tag], str);
		}
		insertText(str);
	} else if(cmd == 'undo') {
		addSnapshot(getEditorContents());
		moveCursor(-1);
		if((str = getSnapshot()) !== false) {
			if(wysiwyg) {
				editdoc.body.innerHTML = str;
			} else {
				editdoc.value = str;
			}
		}
	} else if(cmd == 'redo') {
		moveCursor(1);
		if((str = getSnapshot()) !== false) {
			if(wysiwyg) {
				editdoc.body.innerHTML = str;
			} else {
				editdoc.value = str;
			}
		}
	} else if(!wysiwyg && in_array(cmd, ['insertorderedlist', 'insertunorderedlist'])) {
		var listtype = cmd == 'insertorderedlist' ? '1' : '';
		var opentag = '[list' + (listtype ? ('=' + listtype) : '') + ']\n';
		var closetag = '[/list]';

		if(txt = getSel()) {
			var regex = new RegExp('([\r\n]+|^[\r\n]*)(?!\\[\\*\\]|\\[\\/?list)(?=[^\r\n])', 'gi');
			txt = opentag + trim(txt).replace(regex, '$1[*]') + '\n' + closetag;
			insertText(txt, strlen(txt), 0);
		} else {
			insertText(opentag + closetag, opentag.length, closetag.length);

			while(listvalue = prompt('输入一个列表项目.\r\n留空或者点击取消完成此列表.', '')) {
				if(BROWSER.opera > 8) {
					listvalue = '\n' + '[*]' + listvalue;
					insertText(listvalue, strlen(listvalue) + 1, 0);
				} else {
					listvalue = '[*]' + listvalue + '\n';
					insertText(listvalue, strlen(listvalue), 0);
				}
			}
		}
	} else if(!wysiwyg && cmd == 'unlink') {
		var sel = getSel();
		sel = stripSimple('url', sel);
		sel = stripComplex('url', sel);
		insertText(sel);
	} else if(cmd == 'floatleft' || cmd == 'floatright') {
		var arg = cmd == 'floatleft' ? 'left' : 'right';
		if(wysiwyg) {
			if(txt = getSel()) {
				argm = arg == 'left' ? 'right' : 'left';
				insertText('<br style="clear: both"><table class="float" style="float: ' + arg + '; margin-' + argm + ': 5px;"><tbody><tr><td>' + txt + '</td></tr></tbody></table>', true);
			}
		} else {
			var opentag = '[float=' + arg + ']';
			var closetag = '[/float]';
			if(txt = getSel()) {
				txt = opentag + txt + closetag;
				insertText(txt, strlen(txt), 0);
			} else {
				insertText(opentag + closetag, opentag.length, closetag.length);
			}
		}
	} else if(cmd == 'rst') {
		loadData();
		setEditorTip('数据已恢复');
	} else if(cmd == 'svd') {
		saveData();
		setEditorTip('数据已保存');
	} else if(cmd == 'chck') {
		checklength(editorform);
	} else if(cmd == 'tpr') {
		if(confirm('您确认要清除所有内容吗？')) {
			clearContent();
		}
	} else if(cmd == 'downremoteimg') {
		showDialog('<div id="remotedowninfo"><p class="mbn">正在下载远程附件，请稍等……</p><p><img src="' + STATICURL + 'image/common/uploading.gif" alt="" /></p></div>', 'notice', '', null, 1);
		var message = wysiwyg ? html2bbcode(getEditorContents()) : (!editorform.parseurloff.checked ? parseurl(editorform.message.value) : editorform.message.value);
		var oldValidate = editorform.onsubmit;
		var oldAction = editorform.action;
		editorform.onsubmit = '';
		editorform.action = 'forum.php?mod=ajax&action=downremoteimg&inajax=1&fid='+fid+'&wysiwyg='+(wysiwyg ? 1 : 0);
		editorform.target = "ajaxpostframe";
		editorform.message.value = message;
		editorform.submit();
		editorform.onsubmit = oldValidate;
		editorform.action = oldAction;
		editorform.target = "";
	} else {
		var formatcmd = cmd == 'backcolor' && !BROWSER.ie ? 'hilitecolor' : cmd;
		try {
			var ret = applyFormat(formatcmd, false, (isUndefined(arg) ? true : arg));
		} catch(e) {
			var ret = false;
		}
	}

	if(cmd != 'undo') {
		addSnapshot(getEditorContents());
	}
	if(wysiwyg) {
		setContext(cmd);
	}
	if(in_array(cmd, ['bold', 'italic', 'underline', 'strikethrough', 'fontname', 'fontsize', 'forecolor', 'backcolor', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', 'floatleft', 'floatright', 'removeformat', 'unlink', 'undo', 'redo'])) {
		hideMenu();
	}
	doane();
	return ret;
}

function setContext(cmd) {
	var cmd = !cmd ? '' : cmd;
	var contextcontrols = new Array('bold', 'italic', 'underline', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist');
	for(var i in contextcontrols) {
		var controlid = contextcontrols[i];
		var obj = $(editorid + '_' + controlid);
		if(obj != null) {
			if(cmd == 'clear') {
				obj.className = '';
				continue;
			}
			try {
				var state = editdoc.queryCommandState(contextcontrols[i]);
			} catch(e) {
				var state = false;
			}
			if(isUndefined(obj.state)) {
				obj.state = false;
			}
			if(obj.state != state) {
				obj.state = state;
				buttonContext(obj, state ? 'mouseover' : 'mouseout');
			}
		}
	}

	try {
		var fs = editdoc.queryCommandValue('fontname');
	} catch(e) {
		fs = null;
	}
	if(fs == '' && !BROWSER.ie && window.getComputedStyle) {
		fs = editdoc.body.style.fontFamily;
	} else if(fs == null) {
		fs = '';
	}
	fs = fs && cmd != 'clear' ? fs : '字体';
	if(fs != $(editorid + '_font').fontstate) {
		thingy = fs.indexOf(',') > 0 ? fs.substr(0, fs.indexOf(',')) : fs;
		$(editorid + '_font').innerHTML = thingy;
		$(editorid + '_font').fontstate = fs;
	}
	try {
		var ss = editdoc.queryCommandValue('fontsize');
		if(ss == null || ss == '' || cmd == 'clear') {
			ss = formatFontsize(editdoc.body.style.fontSize);
		} else {
			var ssu = ss.substr(-2);
			if(ssu == 'px' || ssu == 'pt') {
				ss = formatFontsize(ss);
			}
		}
	} catch(e) {
		ss = '大小';
	}

	if(ss != $(editorid + '_size').sizestate) {
		if($(editorid + '_size').sizestate == null) {
			$(editorid + '_size').sizestate = '';
		}
		$(editorid + '_size').innerHTML = ss;
		$(editorid + '_size').sizestate = ss;
	}
}

function buttonContext(obj, state) {
	if(state == 'mouseover') {
		obj.style.cursor = 'pointer';
		var mode = obj.state ? 'down' : 'hover';
		if(obj.mode != mode) {
			obj.mode = mode;
			obj.className = 'hover';
		}
	} else {
		var mode = obj.state ? 'selected' : 'normal';
		if(obj.mode != mode) {
			obj.mode = mode;
			obj.className = mode == 'selected' ? 'hover' : '';
		}
	}
}

function formatFontsize(csssize) {
	switch(csssize) {
		case '7.5pt':
		case '10px': return 1;
		case '13px':
		case '10pt': return 2;
		case '16px':
		case '12pt': return 3;
		case '18px':
		case '14pt': return 4;
		case '24px':
		case '18pt': return 5;
		case '32px':
		case '24pt': return 6;
		case '48px':
		case '36pt': return 7;
		default: return '大小';
	}
}

function showEditorMenu(tag, params) {
	var sel, selection;
	var str = '', strdialog = 0, stitle = '';
	var ctrlid = editorid + (params ? '_cst' + params + '_' : '_') + tag;
	var opentag = '[' + tag + ']';
	var closetag = '[/' + tag + ']';
	var menu = $(ctrlid + '_menu');
	var pos = [0, 0];
	var menuwidth = 270;
	var menupos = '43!';
	var menutype = 'menu';

	try {
		sel = wysiwyg ? editdoc.selection.createRange() : document.selection.createRange();
		selection = wysiwyg ? sel.htmlText : sel.text;
	} catch(e) {
		if (wysiwyg) {
			var gSel = editdoc.getSelection();
			if (gSel.rangeCount > 0) {
				sel = gSel.getRangeAt(0);
			}
		}
		selection = getSel();
	}

	if(menu) {
		if($(ctrlid).getAttribute('menupos') !== null) {
			menupos = $(ctrlid).getAttribute('menupos');
		}
		if($(ctrlid).getAttribute('menuwidth') !== null) {
			menu.style.width = $(ctrlid).getAttribute('menuwidth') + 'px';
		}
		if(menupos == '00') {
			menu.className = 'fwinmask';
			if($(editorid + '_' + tag + '_menu').style.visibility == 'hidden') {
				$(editorid + '_' + tag + '_menu').style.visibility = 'visible';
			} else {
				showMenu({'ctrlid':ctrlid,'mtype':'win','evt':'click','pos':menupos,'timeout':250,'duration':3,'drag':ctrlid + '_ctrl'});
			}
		} else {
			showMenu({'ctrlid':ctrlid,'evt':'click','pos':menupos,'timeout':250,'duration':in_array(tag, ['fontname', 'fontsize', 'sml']) ? 2 : 3,'drag':1});
		}


	} else {
		switch(tag) {
			case 'url':
				str = '请输入链接地址:<br /><input type="text" id="' + ctrlid + '_param_1" style="width: 98%" value="" class="px" />'+
					(selection ? '' : '<br />请输入链接文字:<br /><input type="text" id="' + ctrlid + '_param_2" style="width: 98%" value="" class="px" />');
				break;
			case 'forecolor':
				showColorBox(ctrlid, 1);
				return;
			case 'backcolor':
				showColorBox(ctrlid, 1, '', 1);
				return;
			case 'inserthorizontalrule':
				showHrBox(ctrlid);
				break;
			case 'postbg':
				showHrBox(ctrlid, 'postbg');
				break;
			case 'password':
				str = '<p class="pbn">请输入帖子密码: <input type="text" id="' + ctrlid + '_param_1" size="10" value="" class="px" /></p>';
				break;
			case 'code':
				if(wysiwyg) {
					opentag = '<div class="blockcode"><blockquote>';
					closetag = '</blockquote></div><br />';
				}
			case 'quote':
				if(wysiwyg && tag == 'quote') {
					opentag = '<div class="quote"><blockquote>';
					closetag = '</blockquote></div><br />';
				}
			case 'hide':
			case 'free':
				if(selection) {
					return insertText((opentag + selection + closetag), strlen(opentag), strlen(closetag), true, sel);
				}
				var lang = {'quote' : '请输入要插入的引用', 'code' : '请输入要插入的代码', 'hide' : '请输入要隐藏的信息内容', 'free' : '如果您设置了帖子售价，请输入购买前免费可见的信息内容'};
				str += lang[tag] + ':<br /><textarea id="' + ctrlid + '_param_1" style="width: 98%" cols="50" rows="5" class="txtarea"></textarea>' +
					(tag == 'hide' ? '<br /><label><input type="radio" name="' + ctrlid + '_radio" id="' + ctrlid + '_radio_1" class="pc" checked="checked" />只有当浏览者回复本帖时才显示</label><br /><label><input type="radio" name="' + ctrlid + '_radio" id="' + ctrlid + '_radio_2" class="pc" />只有当浏览者积分高于</label> <input type="text" size="3" id="' + ctrlid + '_param_2" class="px pxs" /> 时才显示<br /><br /><label>有效天数:</label> <input type="text" size="3" id="' + ctrlid + '_param_3" class="px pxs" /> <br />距离发帖日期大于这个天数时标签自动失效' : '');
				break;
			case 'tbl':
				str = '<p class="pbn">表格行数: <input type="text" id="' + ctrlid + '_param_1" size="2" value="2" class="px" /> &nbsp; 表格列数: <input type="text" id="' + ctrlid + '_param_2" size="2" value="2" class="px" /></p><p class="pbn">表格宽度: <input type="text" id="' + ctrlid + '_param_3" size="2" value="" class="px" /> &nbsp; 背景颜色: <input type="text" id="' + ctrlid + '_param_4" size="2" class="px" onclick="showColorBox(this.id, 2)" /></p><p class="xg2 pbn" style="cursor:pointer" onclick="showDialog($(\'tbltips_msg\').innerHTML, \'notice\', \'小提示\', null, 0)"><img id="tbltips" title="小提示" class="vm" src="' + IMGDIR + '/info_small.gif"> 快速书写表格提示</p>';
				str += '<div id="tbltips_msg" style="display: none">“[tr=颜色]” 定义行背景<br />“[td=宽度]” 定义列宽<br />“[td=列跨度,行跨度,宽度]” 定义行列跨度<br /><br />快速书写表格范例：<div class=\'xs0\' style=\'margin:0 5px\'>[table]<br />Name:|Discuz!<br />Version:|X1<br />[/table]</div>用“|”分隔每一列，表格中如有“|”用“\\|”代替，换行用“\\n”代替。</div>';
				break;
			case 'aud':
				str = '<p class="pbn">请输入音乐文件地址:</p><p class="pbn"><input type="text" id="' + ctrlid + '_param_1" class="px" value="" style="width: 220px;" /></p><p class="xg2 pbn">支持 wma mp3 ra rm 等音乐格式<br />示例: http://server/audio.wma</p>';
				break;
			case 'vid':
				str = '<p class="pbn">请输入视频地址:</p><p class="pbn"><input type="text" value="" id="' + ctrlid + '_param_1" style="width: 220px;" class="px" /></p><p class="pbn">宽: <input id="' + ctrlid + '_param_2" size="5" value="500" class="px" /> &nbsp; 高: <input id="' + ctrlid + '_param_3" size="5" value="375" class="px" /></p><p class="xg2 pbn">支持优酷、土豆、56、酷6等视频站的视频网址<br />支持 wmv avi rmvb mov swf flv 等视频格式<br />示例: http://server/movie.wmv</p>';
				break;
			case 'fls':
				str = '<p class="pbn">请输入 Flash 文件地址:</p><p class="pbn"><input type="text" id="' + ctrlid + '_param_1" class="px" value="" style="width: 220px;" /></p><p class="pbn">宽: <input id="' + ctrlid + '_param_2" size="5" value="" class="px" /> &nbsp; 高: <input id="' + ctrlid + '_param_3" size="5" value="" class="px" /></p><p class="xg2 pbn">支持 swf flv 等 Flash 网址<br />示例: http://server/flash.swf</p>';
				break;
			case 'beginning':
				str = '<p class="pbn">请输入开头动画 Flash 或 图片 地址:</p><p class="pbn"><input type="text" id="' + ctrlid + '_param_1" class="px" value="" style="width: 220px;" /></p>';
				str += '<p class="pbn">点击链接地址:</p><p class="pbn"><input type="text" id="' + ctrlid + '_param_2" class="px" value="" style="width: 220px;" /></p>';
				str += '<p class="pbn">宽: <input id="' + ctrlid + '_param_3" size="5" value="" class="px" /> &nbsp; 高: <input id="' + ctrlid + '_param_4" size="5" value="" class="px" /></p>';
				str += '<p class="pbn">停留秒数: <input id="' + ctrlid + '_param_8" size="5" value="" class="px" /></p>';
				str += '<p class="pbn">载入、消失的效果: </p><p class="pbn"><input id="' + ctrlid + '_param_7" type="radio" name="effect" checked />无 &nbsp; <input id="' + ctrlid + '_param_5" type="radio" name="effect" />淡入淡出 &nbsp; <input id="' + ctrlid + '_param_6" type="radio" name="effect" />展开闭合</p>';
				str += '<p class="xg2 pbn">支持 swf flv jpg gif png 网址<br />宽高范围: 宽400~1024 高300~640<br />示例: http://server/flash.swf</p>';
				break;
			case 'pasteword':
				stitle = '从 Word 粘贴内容';
				str = '<p class="px" style="height:300px"><iframe id="' + ctrlid + '_param_1" frameborder="0" style="width:100%;height:100%" onload="this.contentWindow.document.body.style.width=\'550px\';this.contentWindow.document.body.contentEditable=true;this.contentWindow.document.body.focus();this.onload=null"></iframe></p><p class="xg2 pbn">请通过快捷键(Ctrl+V)把 Word 文件中的内容粘贴到上方</p>';
				menuwidth = 600;
				menupos = '00';
				menutype = 'win';
				break;
			case 'index':
				stitle = '创建帖子目录';
				str = '<p class="pbn">[index]<br />\n\
					[#<span class="xi1">页码</span>]<span class="xi1">标题</span> &nbsp;&nbsp;<span class="xg1">跳转到指定的页</span><br />\n\
					<span class="xi1">*</span>[#<span class="xi1">tid,pid</span>]<span class="xi1">标题</span> &nbsp;&nbsp;<span class="xg1">跳转到指定的帖子</span><br />\n\
					[/index]<br />\n\
					<br />\n\
					<span class="xi1">页码</span> &nbsp;&nbsp;<span class="xg1">用 [page] 对当前帖子分页后的页码</span><br />\n\
					<span class="xi1">tid,pid</span> &nbsp;&nbsp;<span class="xg1">帖子的 TID 和 PID</span><br />\n\
					<span class="xi1">*</span> &nbsp;&nbsp;<span class="xg1">添加行首缩进</span></p>';
				break;
			default:
				for(i in EXTRAFUNC['showEditorMenu']) {
					EXTRASELECTION = selection;
					EXTRASEL = sel;
					try {
						eval('str = ' + EXTRAFUNC['showEditorMenu'][i] + '(\'' + tag + '\', 0)');
					} catch(e) {}
				}
				if(!str) {
					str = '';
					var haveSel = selection == null || selection == false || in_array(trim(selection), ['', 'null', 'undefined', 'false']) ? 0 : 1;
					if(params == 1 && haveSel) {
						return insertText((opentag + selection + closetag), strlen(opentag), strlen(closetag), true, sel);
					}
					var promptlang = custombbcodes[tag]['prompt'].split("\t");
					for(var i = 1; i <= params; i++) {
						if(i != params || !haveSel) {
							str += (promptlang[i - 1] ? promptlang[i - 1] : '请输入第 ' + i + ' 个参数:') + '<br /><input type="text" id="' + ctrlid + '_param_' + i + '" style="width: 98%" value="" class="px" />' + (i < params ? '<br />' : '');
						}
					}
				}
				break;
		}

		var menu = document.createElement('div');
		menu.id = ctrlid + '_menu';
		menu.style.display = 'none';
		menu.className = 'p_pof upf';
		menu.style.width = menuwidth + 'px';
		if(menupos == '00') {
			menu.className = 'fwinmask';
			s = '<table width="100%" cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c">'
				+ '<h3 class="flb"><em>' + stitle + '</em><span><a onclick="hideMenu(\'\', \'win\');return false;" class="flbc" href="javascript:;">关闭</a></span></h3><div class="c">' + str + '</div>'
				+ '<p class="o pns"><button type="submit" id="' + ctrlid + '_submit" class="pn pnc"><strong>提交</strong></button></p>'
				+ '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
		} else {
			s = '<div class="p_opt cl"><span class="y" style="margin:-10px -10px 0 0"><a onclick="hideMenu();return false;" class="flbc" href="javascript:;">关闭</a></span><div>' + str + '</div><div class="pns mtn"><button type="submit" id="' + ctrlid + '_submit" class="pn pnc"><strong>提交</strong></button></div></div>';
		}
		menu.innerHTML = s;
		$(editorid + '_editortoolbar').appendChild(menu);
		showMenu({'ctrlid':ctrlid,'mtype':menutype,'evt':'click','duration':3,'cache':0,'drag':1,'pos':menupos});
	}

	try {
		if($(ctrlid + '_param_1')) {
			$(ctrlid + '_param_1').focus();
		}
	} catch(e) {}
	var objs = menu.getElementsByTagName('*');
	for(var i = 0; i < objs.length; i++) {
		_attachEvent(objs[i], 'keydown', function(e) {
			e = e ? e : event;
			obj = BROWSER.ie ? event.srcElement : e.target;
			if((obj.type == 'text' && e.keyCode == 13) || (obj.type == 'textarea' && e.ctrlKey && e.keyCode == 13)) {
				if($(ctrlid + '_submit') && tag != 'image') $(ctrlid + '_submit').click();
				doane(e);
			} else if(e.keyCode == 27) {
				hideMenu();
				doane(e);
			}
		});
	}
	if($(ctrlid + '_submit')) $(ctrlid + '_submit').onclick = function() {
		checkFocus();
		switch(tag) {
			case 'url':
				var href = $(ctrlid + '_param_1').value;
				href = (isEmail(href) ? 'mailto:' : '') + href;
				if(href != '') {
					var v = selection ? selection : ($(ctrlid + '_param_2').value ? $(ctrlid + '_param_2').value : href);
					str = wysiwyg ? ('<a href="' + href + '">' + v + '</a>') : '[url=' + squarestrip(href) + ']' + v + '[/url]';
					if(wysiwyg) {
						insertText(str, str.length - v.length, 0, (selection ? true : false), sel);
					} else {
						insertText(str, str.length - v.length - 6, 6, (selection ? true : false), sel);
					}
				}
				break;
			case 'code':
				if(wysiwyg) {
					var isCodeTag = 1 ;
					opentag = '<div class="blockcode"><blockquote>';
					closetag = '</blockquote></div><br />';
					if(!BROWSER.ie) {
						selection = selection ? selection : '\n';
					}
				}
			case 'quote':
				if(wysiwyg && tag == 'quote') {
					opentag = '<div class="quote"><blockquote>';
					closetag = '</blockquote></div><br />';
					if(!BROWSER.ie) {
						selection = selection ? selection : '\n';
					}
				}
			case 'hide':
			case 'free':
				if(tag == 'hide') {
					var mincredits = parseInt($(ctrlid + '_param_2').value);
					var expire = parseInt($(ctrlid + '_param_3').value);
					if(expire > 0 || (mincredits > 0 && $(ctrlid + '_radio_2').checked)) {
						opentag = '[hide=';
						if(expire > 0) {
							opentag += 'd'+expire;
						}
						if(mincredits > 0 && $(ctrlid + '_radio_2').checked) {
							opentag += (expire > 0 ? ',' : '')+mincredits;
						}
						opentag += ']';
					} else {
						opentag = '[hide]';
					}
				}
				str = $(ctrlid + '_param_1') && $(ctrlid + '_param_1').value ? $(ctrlid + '_param_1').value : (selection ? selection : '');
				if(wysiwyg) {
					if(typeof isCodeTag != 'undefined') {
						str = str.replace(/&/g, '&amp;');
					}
					str = preg_replace(['<', '>'], ['&lt;', '&gt;'], str);
					str = str.replace(/\r?\n/g, '<br />');
				}
				str = opentag + str + closetag;
				insertText(str, strlen(opentag), strlen(closetag), false, sel);
				break;
			case 'password':
				str = $(ctrlid + '_param_1') && $(ctrlid + '_param_1').value ? $(ctrlid + '_param_1').value : (selection ? selection : '');
				str = opentag + str + closetag;
				insertText(str, strlen(opentag), strlen(closetag), false, sel);
				break;
			case 'tbl':
				var rows = $(ctrlid + '_param_1').value;
				var columns = $(ctrlid + '_param_2').value;
				var width = $(ctrlid + '_param_3').value;
				var bgcolor = $(ctrlid + '_param_4').value;
				rows = /^[-\+]?\d+$/.test(rows) && rows > 0 && rows <= 30 ? rows : 2;
				columns = /^[-\+]?\d+$/.test(columns) && columns > 0 && columns <= 30 ? columns : 2;
				width = width.substr(width.length - 1, width.length) == '%' ? (width.substr(0, width.length - 1) <= 98 ? width : '98%') : (width <= 560 ? width : '98%');
				bgcolor = /[\(\)%,#\w]+/.test(bgcolor) ? bgcolor : '';
				if(wysiwyg) {
					str = '<table cellspacing="0" cellpadding="0" style="width:' + (width ? width : '50%') + '" class="t_table"' + (bgcolor ? ' bgcolor="' + bgcolor + '"' : '') + '>';
					for (var row = 0; row < rows; row++) {
						str += '<tr>\n';
						for (col = 0; col < columns; col++) {
							str += '<td>&nbsp;</td>\n';
						}
						str += '</tr>\n';
					}
					str += '</table>\n';
				} else {
					str = '[table=' + (width ? width : '50%') + (bgcolor ? ',' + bgcolor : '') + ']\n';
					for (var row = 0; row < rows; row++) {
						str += '[tr]';
						for (col = 0; col < columns; col++) {
							str += '[td] [/td]';
						}
						str += '[/tr]\n';
					}
					str += '[/table]\n';
				}
				insertText(str, str.length, 0, false, sel);
				break;
			case 'aud':
				insertText('[audio]' + $(ctrlid + '_param_1').value + '[/audio]', 7, 8, false, sel);
				break;
			case 'fls':
				if($(ctrlid + '_param_2').value && $(ctrlid + '_param_3').value) {
					insertText('[flash=' + parseInt($(ctrlid + '_param_2').value) + ',' + parseInt($(ctrlid + '_param_3').value) + ']' + squarestrip($(ctrlid + '_param_1').value) + '[/flash]', 7, 8, false, sel);
				} else {
					insertText('[flash]' + squarestrip($(ctrlid + '_param_1').value) + '[/flash]', 7, 8, false, sel);
				}
				break;
			case 'beginning':
				if($(ctrlid + '_param_1').value) {
					insertText('[begin=' + squarestrip($(ctrlid + '_param_2').value) + ',' +
						($(ctrlid + '_param_3').value ? parseInt($(ctrlid + '_param_3').value) : 0) + ',' +
						($(ctrlid + '_param_4').value ? parseInt($(ctrlid + '_param_4').value) : 0) + ',' +
						($(ctrlid + '_param_5').checked ? 2 : ($(ctrlid + '_param_6').checked ? 1 : 0)) + ',' +
						($(ctrlid + '_param_8').value ? parseInt($(ctrlid + '_param_8').value) : 0) + ']' +
						squarestrip($(ctrlid + '_param_1').value) + '[/begin]', 7, 8, false, sel);
				}
				break;
			case 'vid':
				var mediaUrl = $(ctrlid + '_param_1').value;
				var auto = '';
				var posque = mediaUrl.lastIndexOf('?');
				posque = posque === -1 ? mb_strlen(mediaUrl) : posque;
				var ext = mediaUrl.lastIndexOf('.') === -1 ? '' : mediaUrl.substring(mediaUrl.lastIndexOf('.') + 1, posque).toLowerCase();
				ext = in_array(ext, ['mp3', 'wav', 'wma', 'ra', 'rm', 'ram', 'mid', 'asx', 'wmv', 'avi', 'mpg', 'mpeg', 'rmvb', 'asf', 'mov', 'flv', 'swf', 'mp4', 'm4a', 'm4v', '3gp', 'ogv', 'ogg', 'webm', 'weba', 'aac', 'flac']) ? ext : 'x';
				if(ext == 'x') {
					if(/^mms:\/\//.test(mediaUrl)) {
						ext = 'mms';
					} else if(/^(rtsp|pnm):\/\//.test(mediaUrl)) {
						ext = 'rtsp';
					}
				}
				var str = '[media=' + ext + ',' + $(ctrlid + '_param_2').value + ',' + $(ctrlid + '_param_3').value + ']' + squarestrip(mediaUrl) + '[/media]';
				insertText(str, str.length, 0, false, sel);
				break;
			case 'image':
				var width = parseInt($(ctrlid + '_param_2').value);
				var height = parseInt($(ctrlid + '_param_3').value);
				var src = $(ctrlid + '_param_1').value;
				var style = '';
				if(wysiwyg) {
					style += width ? ' width=' + width : '';
					style += height ? ' _height=' + height : '';
					var str = '<img src=' + src + style + ' border=0 />';
					insertText(str, str.length, 0, false, sel);
				} else {
					style += width || height ? '=' + width + ',' + height : '';
					insertText('[img' + style + ']' + squarestrip(src) + '[/img]', 0, 0, false, sel);
				}
				hideMenu('', 'win');
				$(ctrlid + '_param_1').value = '';
				break;
			case 'pasteword':
				pasteWord($(ctrlid + '_param_1').contentWindow.document.body.innerHTML);
				hideMenu('', 'win');
				break;
			case 'index':
				if(wysiwyg) {
					str = '[index]<br />[#]<br />[#]<br />[/index]';
				} else {
					str = '[index]\n[#]\n[#]\n[/index]';
				}
				insertText(str, str.length, 0, false, sel);
				hideMenu('', 'win');
				break;
			default:
				for(i in EXTRAFUNC['showEditorMenu']) {
					EXTRASELECTION= selection;
					try {
						eval('str = ' + EXTRAFUNC['showEditorMenu'][i] + '(\'' + tag + '\', 1)');
					} catch(e) {}
				}
				if(!str) {
					str = '';
					var first = $(ctrlid + '_param_1').value;
					if($(ctrlid + '_param_2')) var second = $(ctrlid + '_param_2').value;
					if($(ctrlid + '_param_3')) var third = $(ctrlid + '_param_3').value;
					if((params == 1 && first) || (params == 2 && first && (haveSel || second)) || (params == 3 && first && second && (haveSel || third))) {
						if(params == 1) {
							str = first;
						} else if(params == 2) {
							str = haveSel ? selection : second;
							opentag = '[' + tag + '=' + first + ']';
						} else {
							str = haveSel ? selection : third;
							opentag = '[' + tag + '=' + first + ',' + second + ']';
						}
						insertText((opentag + str + closetag), strlen(opentag), strlen(closetag), true, sel);
					}
				}
				break;
		}
		hideMenu();
	};
}

function autoTypeset() {
	var sel;
	if(BROWSER.ie) {
		sel = wysiwyg ? editdoc.selection.createRange() : document.selection.createRange();
	}
	var selection = sel ? (wysiwyg ? sel.htmlText.replace(/<\/?p>/ig, '<br />') : sel.text) : getSel();
	selection = trim(selection);
	selection = wysiwyg ? selection.replace(/<br( \/)?>(<br( \/)?>)+/ig, '</p>\n<p style="line-height: 30px; text-indent: 2em;">') : selection.replace(/\n\n+/g, '[/p]\n[p=30, 2, left]');
	opentag = wysiwyg ? '<p style="line-height: 30px; text-indent: 2em;">' : '[p=30, 2, left]';
	var s = opentag + selection + (wysiwyg ? '</p>' : '[/p]');
	insertText(s, strlen(opentag), 4, false, sel);
	hideMenu();
}

function getSel() {
	if(wysiwyg) {
		try {
			selection = editwin.getSelection();
			checkFocus();
			range = selection ? selection.getRangeAt(0) : editdoc.createRange();
			return readNodes(range.cloneContents(), false);
		} catch(e) {
			try {
				var range = editdoc.selection.createRange();
				if(range.htmlText && range.text) {
					return range.htmlText;
				} else {
					var htmltext = '';
					for(var i = 0; i < range.length; i++) {
						htmltext += range.item(i).outerHTML;
					}
					return htmltext;
				}
			} catch(e) {
				return '';
			}
		}
	} else {
		if(!isUndefined(editdoc.selectionStart)) {
			return editdoc.value.substr(editdoc.selectionStart, editdoc.selectionEnd - editdoc.selectionStart);
		} else if(document.selection && document.selection.createRange) {
			return document.selection.createRange().text;
		} else if(window.getSelection) {
			return window.getSelection() + '';
		} else {
			return false;
		}
	}
}

function insertText(text, movestart, moveend, select, sel) {
	checkFocus();
	if(wysiwyg) {
		try {
			if(!editdoc.execCommand('insertHTML', false, text)) {
				throw 'insertHTML Err';
			}
		} catch(e) {
			try {
				if(!isUndefined(editdoc.selection) && editdoc.selection.type != 'Text' && editdoc.selection.type != 'None') {
					movestart = false;
					editdoc.selection.clear();
				}
				range = isUndefined(sel) ? editdoc.selection.createRange() : sel;
				range.pasteHTML(text);
				if(text.indexOf('\n') == -1) {
					if(!isUndefined(movestart)) {
						range.moveStart('character', -strlen(text) + movestart);
						range.moveEnd('character', -moveend);
					} else if(movestart != false) {
						range.moveStart('character', -strlen(text));
					}
					if(!isUndefined(select) && select) {
						range.select();
					}
				}
			} catch(e) {
				if(!sel) {
					var sel = editdoc.getSelection();
					if (sel.rangeCount == 0) {
						sel.collapse(editdoc.body, 0);
					}
					var range = sel.getRangeAt(0);
				} else {
					var range = sel;
				}
				if(range && range.insertNode) {
					range.deleteContents();
				}
				var frag = range.createContextualFragment(text);
				range.insertNode(frag);
			}
		}
	} else {
		if(!isUndefined(editdoc.selectionStart)) {
			if(editdoc._selectionStart) {
				editdoc.selectionStart = editdoc._selectionStart;
				editdoc.selectionEnd = editdoc._selectionEnd;
				editdoc._selectionStart = 0;
				editdoc._selectionEnd = 0;
			}
			var opn = editdoc.selectionStart + 0;
			editdoc.value = editdoc.value.substr(0, editdoc.selectionStart) + text + editdoc.value.substr(editdoc.selectionEnd);

			if(!isUndefined(movestart)) {
				editdoc.selectionStart = opn + movestart;
				editdoc.selectionEnd = opn + strlen(text) - moveend;
			} else if(movestart !== false) {
				editdoc.selectionStart = opn;
				editdoc.selectionEnd = opn + strlen(text);
			}
		} else if(document.selection && document.selection.createRange) {
			if(isUndefined(sel)) {
				sel = document.selection.createRange();
			}
			if(editbox.sel) {
				sel = editbox.sel;
				editbox.sel = null;
			}
			sel.text = text.replace(/\r?\n/g, '\r\n');
			if(!isUndefined(movestart)) {
				sel.moveStart('character', -strlen(text) +movestart);
				sel.moveEnd('character', -moveend);
			} else if(movestart !== false) {
				sel.moveStart('character', -strlen(text));
			}
			sel.select();
		} else {
			editdoc.value += text;
		}
	}
	checkFocus();
}

function stripSimple(tag, str, iterations) {
	var opentag = '[' + tag + ']';
	var closetag = '[/' + tag + ']';

	if(isUndefined(iterations)) {
		iterations = -1;
	}
	while((startindex = stripos(str, opentag)) !== false && iterations != 0) {
		iterations --;
		if((stopindex = stripos(str, closetag)) !== false) {
			var text = str.substr(startindex + opentag.length, stopindex - startindex - opentag.length);
			str = str.substr(0, startindex) + text + str.substr(stopindex + closetag.length);
		} else {
			break;
		}
	}
	return str;
}

function readNodes(root, toptag) {
	var html = "";
	var moz_check = /_moz/i;

	switch(root.nodeType) {
		case Node.ELEMENT_NODE:
		case Node.DOCUMENT_FRAGMENT_NODE:
			var closed;
			if(toptag) {
				closed = !root.hasChildNodes();
				html = '<' + root.tagName.toLowerCase();
				var attr = root.attributes;
				for(var i = 0; i < attr.length; ++i) {
					var a = attr.item(i);
					if(!a.specified || a.name.match(moz_check) || a.value.match(moz_check)) {
						continue;
					}
					html += " " + a.name.toLowerCase() + '="' + a.value + '"';
				}
				html += closed ? " />" : ">";
			}
			for(var i = root.firstChild; i; i = i.nextSibling) {
				html += readNodes(i, true);
			}
			if(toptag && !closed) {
				html += "</" + root.tagName.toLowerCase() + ">";
			}
			break;

		case Node.TEXT_NODE:
			html = htmlspecialchars(root.data);
			break;
	}
	return html;
}

function stripComplex(tag, str, iterations) {
	var opentag = '[' + tag + '=';
	var closetag = '[/' + tag + ']';

	if(isUndefined(iterations)) {
		iterations = -1;
	}
	while((startindex = stripos(str, opentag)) !== false && iterations != 0) {
		iterations --;
		if((stopindex = stripos(str, closetag)) !== false) {
			var openend = stripos(str, ']', startindex);
			if(openend !== false && openend > startindex && openend < stopindex) {
				var text = str.substr(openend + 1, stopindex - openend - 1);
				str = str.substr(0, startindex) + text + str.substr(stopindex + closetag.length);
			} else {
				break;
			}
		} else {
			break;
		}
	}
	return str;
}

function stripos(haystack, needle, offset) {
	if(isUndefined(offset)) {
		offset = 0;
	}
	var index = haystack.toLowerCase().indexOf(needle.toLowerCase(), offset);

	return (index == -1 ? false : index);
}

function switchEditor(mode) {
	if(mode == wysiwyg || !allowswitcheditor)  {
		return;
	}
	if(!mode) {
		var controlbar = $(editorid + '_controls');
		var controls = [];
		var buttons = controlbar.getElementsByTagName('a');
		var buttonslength = buttons.length;
		for(var i = 0; i < buttonslength; i++) {
			if(buttons[i].id) {
				controls[controls.length] = buttons[i].id;
			}
		}
		var controlslength = controls.length;
		for(var i = 0; i < controlslength; i++) {
			var control = $(controls[i]);

			if(control.id.indexOf(editorid + '_') != -1) {
				control.state = false;
				control.mode = 'normal';
			} else if(control.id.indexOf(editorid + '_popup_') != -1) {
				control.state = false;
			}
		}
		setContext('clear');
	}
	cursor = -1;
	stack = [];
	var parsedtext = getEditorContents();
	parsedtext = mode ? bbcode2html(parsedtext) : html2bbcode(parsedtext);
	wysiwyg = mode;
	$(editorid + '_mode').value = mode;

	newEditor(mode, parsedtext);
	setEditorStyle();
	editwin.focus();
	setCaretAtEnd();
}

function setCaretAtEnd() {
	if(wysiwyg) {
		editdoc.body.innerHTML += '';
	} else {
		editdoc.value += '';
	}
}

function moveCursor(increment) {
	var test = cursor + increment;
	if(test >= 0 && stack[test] != null && !isUndefined(stack[test])) {
		cursor += increment;
	}
}

function addSnapshot(str) {
	if(stack[cursor] == str) {
		return;
	} else {
		cursor++;
		stack[cursor] = str;

		if(!isUndefined(stack[cursor + 1])) {
			stack[cursor + 1] = null;
		}
	}
}

function getSnapshot() {
	if(!isUndefined(stack[cursor]) && stack[cursor] != null) {
		return stack[cursor];
	} else {
		return false;
	}
}

function squarestrip(str) {
	str = str.replace('[', '%5B');
	str = str.replace(']', '%5D');
	return str;
}

function loadimgsize(imgurl, editor, p) {
	var editor = !editor ? editorid : editor;
	var s = new Object();
	var p = !p ? '_image' : p;
	$(editor + p + '_param_2').value = '';
	$(editor + p + '_param_3').value = '';
	s.img = new Image();
	s.img.src = imgurl;
	s.loadCheck = function () {
		if($(editor + p + '_param_1').value == imgurl) {
			if(s.img.complete) {
				$(editor + p + '_param_2').value = s.img.width ? s.img.width : '';
				$(editor + p + '_param_3').value = s.img.height ? s.img.height : '';
			} else {
				setTimeout(function () {s.loadCheck();}, 100);
			}
		}
	};
	s.loadCheck();
}

function showHrBox(ctrlid, boxtype) {
	if(typeof postimg_type == 'undefined') {
		var scriptNode = document.createElement("script");
		scriptNode.type = "text/javascript";
		scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
		scriptNode.src = 'data/cache/common_postimg.js?' + VERHASH;
		$('append_parent').appendChild(scriptNode);
		if(BROWSER.ie) {
			scriptNode.onreadystatechange = function() {
				_initHrBox(ctrlid, boxtype);
			};
		} else {
			scriptNode.onload = function() {
				_initHrBox(ctrlid, boxtype);
			};
		}
	} else {
		_initHrBox(ctrlid, boxtype);
	}
}
function _initHrBox(ctrlid, boxtype) {
	if(!$(ctrlid + '_menu')) {
		var menu = document.createElement('div');
		menu.id = ctrlid + '_menu';
		menu.unselectable = true;
		menu.style.display = 'none';
		if(boxtype == 'postbg') {
			menu.className = 'p_pop postbgbox';
			var imglist = postimg_type['postbg'];
			var str = '';
			for(var i = 0; i < imglist.length; i++) {
				str += '<input type="button" style="background-image: url(\''+ STATICURL+'image/postbg/'+imglist[i]+ '\');"' +  ' onclick="'
				+ 'insertPostBackground(\''+imglist[i]+'\');'
				+ '" />' ;
			}
		} else {
			menu.className = 'p_pop hrbox';
			var hrimglist = postimg_type['hrline'];
			var str = '';
			for(var i = 0; i < hrimglist.length; i++) {
				inserturl = (hrimglist[i] != '0.gif') ? "'"+STATICURL+'image/hrline/'+hrimglist[i]+"'" : 'false';
				str += '<input type="button" style="background-image: url(\'' + STATICURL+'image/hrline/'+hrimglist[i]+ '\');"' +  ' onclick="'
				+ 'insertHrImage('+inserturl+');'
				+ '" />' ;
			}
		}
		menu.innerHTML = str;
		$('append_parent').appendChild(menu);
	}
}
function insertHrImage(url) {
	checkFocus();
	if(wysiwyg) {
		if(url !== false) {
			insertText('<br><img src="' + url + '" border="0" alt="" /><br><br>', false);
		} else {
			insertText('<hr class="l">', 14);
		}
	} else {
		if(url !== false) {
			code = '\n[img]'+url+'[/img]\n';
		} else {
			code = '[hr]';
		}
		insertText(code, strlen(code), 0);
	}
	hideMenu();
}
function insertPostBackground(img) {
	checkFocus();
	if(img != '0.gif') {
		code = '[postbg]'+img+'[/postbg]';
		if(wysiwyg) {
			postbgElement = !BROWSER.ie ? editdoc.getElementsByName('editorpostbg') : editdoc.getElementsByTagName('style');
			for(var i = 0; i < postbgElement.length; i++) {
				postbgElement[i].parentNode.removeChild(postbgElement[i]);
			}
			editdoc.body.innerHTML += '<style type="text/css" name="editorpostbg">body{background-image:url("'+STATICURL+'image/postbg/'+img+'");}</style>';
		} else {
			editdoc.value = editdoc.value.replace(/\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]/ig, '');
			editdoc.value = editdoc.value+code;
		}
	} else {
		if(wysiwyg) {
			postbgElement = !BROWSER.ie ? editdoc.getElementsByName('editorpostbg') : editdoc.getElementsByTagName('style');
			for(var i = 0; i < postbgElement.length; i++) {
				postbgElement[i].parentNode.removeChild(postbgElement[i]);
			}
		} else {
			editdoc.value = editdoc.value.replace(/\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]/ig, '');
		}
	}
	hideMenu();
}

if(typeof jsloaded == 'function') {
	jsloaded('editor');
}