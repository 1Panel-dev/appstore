var gSetColorType = "";
var gIsIE = document.all;
var gIEVer = fGetIEVer();
var gLoaded = false;
var ev = null;
var gIsHtml = true;
var pos = 0;
var sLength = 0;

function fGetEv(e){
	ev = e;
}

function fGetIEVer(){
	var iVerNo = 0;
	var sVer = navigator.userAgent;
	if(sVer.indexOf("MSIE")>-1){
		var sVerNo = sVer.split(";")[1];
		sVerNo = sVerNo.replace("MSIE","");
		iVerNo = parseFloat(sVerNo);
	}
	return iVerNo;
}
function fSetEditable(){
	var f = window.frames["HtmlEditor"];
	f.document.designMode="on";
	if(!gIsIE)
		f.document.execCommand("useCSS",false, true);
}

function renewContent() {

	var evalevent = function (obj) {
		var script = obj.parentNode.innerHTML;
		var re = /onclick="(.+?)["|>]/ig;
		var matches = re.exec(script);
		if(matches != null) {
			matches[1] = matches[1].replace(/this\./ig, 'obj.');
			eval(matches[1]);
		}
	};

	if(window.confirm('您确定要恢复上次保存?')) {
		var data = loadUserdata('home');
		if(in_array((data = trim(data)), ['', 'null', 'false', null, false])) {
			parent.showDialog('没有可以恢复的数据！');
			return;
		}
		var data = data.split(/\x09\x09/);
		if(parent.$('subject')) {
			var formObj = parent.$('subject').form;
		} else if(parent.$('title')) {
			var formObj = parent.$('title').form;
		} else {
			return;
		}
		for(var i = 0; i < formObj.elements.length; i++) {
			var el = formObj.elements[i];
			if(el.name != '' && (el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio'))) {
				for(var j = 0; j < data.length; j++) {
					var ele = data[j].split(/\x09/);
					if(ele[0] == el.name) {
						elvalue = !isUndefined(ele[3]) ? ele[3] : '';
						if(ele[1] == 'INPUT') {
							if(ele[2] == 'text') {
								el.value = elvalue;
							} else if((ele[2] == 'checkbox' || ele[2] == 'radio') && ele[3] == el.value) {
								el.checked = true;
								evalevent(el);
							}
						} else if(ele[1] == 'TEXTAREA') {
							if(ele[0] == 'message' || ele[0] == 'content') {
								var f = window.frames["HtmlEditor"];
								f.document.body.innerHTML = elvalue;
							} else {
								el.value = elvalue;
							}
						}
						break
					}
				}
			}
		}
	}
}
function fSetFrmClick(){
	var f = window.frames["HtmlEditor"];
	f.document.onclick = function(){
		fHideMenu();
	}
	if(gIsIE) {
		f.document.attachEvent("onkeydown", listenKeyDown);
	} else {
		f.addEventListener('keydown', function(e) {listenKeyDown(e);}, true);
	}
}

function listenKeyDown(event) {
	parent.gIsEdited = true;
	parent.ctrlEnter(event, 'issuance');
}

window.onload = function(){
	try{
		gLoaded = true;
		fSetEditable();
		fSetFrmClick();
	}catch(e){
	}
}

window.onbeforeunload = parent.edit_save;

function fSetColor(){
	var dvForeColor =$("dvForeColor");
	if(dvForeColor.getElementsByTagName("TABLE").length == 1){
		dvForeColor.innerHTML = drawCube() + dvForeColor.innerHTML;
	}
}


document.onmousemove = function(e){
	if(gIsIE) var el = event.srcElement;
	else var el = e.target;
	var tdView = $("tdView");
	var tdColorCode = $("tdColorCode");

	if(el.tagName == "IMG"){
		try{
			if(fInObj(el, "dvForeColor")){
				tdView.bgColor = el.parentNode.bgColor;
				tdColorCode.innerHTML = el.parentNode.bgColor
			}
		}catch(e){}
	}
}
function fInObj(el, id){
	if(el){
		if(el.id == id){
			return true;
		}else{
			if(el.parentNode){
				return fInObj(el.parentNode, id);
			}else{
				return false;
			}
		}
	}
}
function fDisplayObj(id){
	var o = $(id);
	if(o) o.style.display = "";
}
document.onclick = function(e){
	if(gIsIE) var el = event.srcElement;
	else var el = e.target;
	var dvForeColor =$("dvForeColor");
	var dvPortrait =$("dvPortrait");

	if(el.tagName == "IMG"){
		try{
			if(fInObj(el, "dvForeColor")){
				format(gSetColorType, el.parentNode.bgColor);
				dvForeColor.style.display = "none";
				return;
			}
		}catch(e){}
		try{
			if(fInObj(el, "dvPortrait")){
				format("InsertImage", el.src);
				dvPortrait.style.display = "none";
				return;
			}
		}catch(e){}
	}
	try{
		if(fInObj(el, "createUrl") || fInObj(el, "createImg") || fInObj(el, "createSwf") || fInObj(el, "createPage")){
			return;
		}
	}catch(e){}
	fHideMenu();
	var hideId = "";
	if(arrMatch[el.id]){
		hideId = arrMatch[el.id];
		fDisplayObj(hideId);
	}
}
var arrMatch = {
	imgFontface:"fontface",
	imgFontsize:"fontsize",
	imgFontColor:"dvForeColor",
	imgBackColor:"dvForeColor",
	imgFace:"dvPortrait",
	imgAlign:"divAlign",
	imgList:"divList",
	imgInOut:"divInOut",
	faceBox:"editFaceBox",
	icoUrl:"createUrl",
	icoSwf:"createSwf",
	icoPage:"createPage"
}
function format(type, para){
	var f = window.frames["HtmlEditor"];
	var sAlert = "";
	if(!gIsIE){
		switch(type){
			case "Cut":
				sAlert = "您的浏览器安全设置不允许编辑器自动执行剪切操作,请使用键盘快捷键(Ctrl+X)来完成";
				break;
			case "Copy":
				sAlert = "您的浏览器安全设置不允许编辑器自动执行拷贝操作,请使用键盘快捷键(Ctrl+C)来完成";
				break;
			case "Paste":
				sAlert = "您的浏览器安全设置不允许编辑器自动执行粘贴操作,请使用键盘快捷键(Ctrl+V)来完成";
				break;
		}
	}
	if(sAlert != ""){
		alert(sAlert);
		return;
	}
	f.focus();
	if(!para){
		if(gIsIE){
			f.document.execCommand(type);
		}else{
			f.document.execCommand(type,false,false);
		}
	}else{
		if(type == 'insertHTML') {
			try{
				f.document.execCommand('insertHTML', false, para);
			}catch(exp){
				var obj = f.document.selection.createRange();
				obj.pasteHTML(para);
				obj.collapse(false);
				obj.select();
			}
		} else {
			try{
				f.document.execCommand(type,false,para);
			}catch(exp){}
		}
	}
	f.focus();
}
function setMode(bStatus){
	var sourceEditor = $("sourceEditor");
	var HtmlEditor = $("HtmlEditor");
	var divEditor = $("divEditor");
	var f = window.frames["HtmlEditor"];
	var body = f.document.getElementsByTagName("BODY")[0];
	if(bStatus){
		sourceEditor.style.display = "block";
		divEditor.style.display = "none";
		sourceEditor.value = body.innerHTML;
		$('uchome-editstatus').value = 'code';
	}else{
		sourceEditor.style.display = "none";
		divEditor.style.display = "";
		body.innerHTML = sourceEditor.value;
		$('uchome-editstatus').value = 'html';
	}
}
function foreColor(e) {
	fDisplayColorBoard(e);
	gSetColorType = "foreColor";
}
function faceBox(e) {
	if(gIsIE){
		var e = window.event;
	}
	var dvFaceBox = $("editFaceBox");
	var iX = e.clientX;
	var iY = e.clientY;
	dvFaceBox.style.display = "";
	dvFaceBox.style.left = (iX-140) + "px";
	dvFaceBox.style.top = 33 + "px";
	dvFaceBox.innerHTML = "";
	var faceul = document.createElement("ul");
	for(i=1; i<31; i++) {
		var faceli = document.createElement("li");
		faceli.innerHTML = '<img src="' + parent.STATICURL + 'image/smiley/comcom/'+i+'.gif" onclick="insertImg(this.src);" class="cur1" />';
		faceul.appendChild(faceli);
	}
	dvFaceBox.appendChild(faceul);
	return true;
}

function insertImg(src) {
	format("insertHTML", '<img src="' + src + '"/>');
}

function doodleBox(event, id) {
	if(parent.$('uchome-ttHtmlEditor') != null) {
		parent.showWindow(id, 'home.php?mod=magic&mid=doodle&showid=blog_doodle&target=uchome-ttHtmlEditor&from=editor');
	} else {
		alert("找不到涂鸦板初始化数据");
	}
}
function backColor(e){
	var sColor = fDisplayColorBoard(e);
	if(gIsIE)
		gSetColorType = "backcolor";
	else
		gSetColorType = "backcolor";
}

function fDisplayColorBoard(e){

	if(gIsIE){
		var e = window.event;
	}
	if(gIEVer<=5.01 && gIsIE){
		var arr = showModalDialog("ColorSelect.htm", "", "font-family:Verdana; font-size:12; status:no; dialogWidth:21em; dialogHeight:21em");
		if (arr != null) return arr;
		return;
	}
	var dvForeColor =$("dvForeColor");
	var iX = e.clientX;
	var iY = e.clientY;
	dvForeColor.style.display = "";
	dvForeColor.style.left = (iX-30) + "px";
	dvForeColor.style.top = 33 + "px";
	return true;
}

function createLink(e, show) {
	if(typeof show == 'undefined') {
		var urlObj = $('insertUrl');
		var sURL = urlObj.value;
		if ((sURL!=null) && (sURL!="http://")){
			setCaret();
			format("CreateLink", sURL);
		}
		fHide($('createUrl'));
		urlObj.value = 'http://';
	} else {
		if(gIsIE){
			var e = window.event;
		}
		getCaret();
		var dvUrlBox = $("createUrl");
		var iX = e.clientX;
		var iY = e.clientY;
		dvUrlBox.style.display = "";
		dvUrlBox.style.left = (iX-300) + "px";
		dvUrlBox.style.top = 33 + "px";
	}
}
function getCaret() {
	if(gIsIE){
		window.frames["HtmlEditor"].focus();
		var ran = window.frames["HtmlEditor"].document.selection.createRange();
		pos = ran.getBookmark();
	}
}
function setCaret() {
	if(gIsIE){
		window.frames["HtmlEditor"].focus();
		var range = window.frames["HtmlEditor"].document.body.createTextRange();
        range.moveToBookmark(pos);
        range.select();
        pos = null;
	}
}

function clearLink() {
	format("Unlink", false);
}
function createImg(e, show) {
	if(typeof show == 'undefined') {
		var imgObj = $('imgUrl');
		var sPhoto = imgObj.value;
		if ((sPhoto!=null) && (sPhoto!="http://")){
			setCaret();
			format("InsertImage", sPhoto);
		}
		fHide($('createImg'));
		imgObj.value = 'http://';
	} else {
		if(gIsIE){
			var e = window.event;
		}
		getCaret();
		var dvImgBox = $("createImg");
		var iX = e.clientX;
		var iY = e.clientY;
		dvImgBox.style.display = "";
		dvImgBox.style.left = (iX-300) + "px";
		dvImgBox.style.top = 33 + "px";
	}
}
function createFlash(e, show) {
	if(typeof show == 'undefined') {
		var flashtag = '';
		var vObj = $('videoUrl');
		var sFlash = vObj.value;
		if ((sFlash!=null) && (sFlash!="http://")){
			setCaret();
			var sFlashType = $('vtype').value;
			if(sFlashType==1) {
				flashtag = '[flash=media]';
			} else if(sFlashType==2) {
				flashtag = '[flash=real]';
			} else if(sFlashType==3) {
				flashtag = '[flash=mp3]';
			} else {
				flashtag = '[flash]';
			}
			format("insertHTML", flashtag + sFlash + '[/flash]');
		}
		fHide($('createSwf'));
		vObj.value = 'http://';
	} else {
		if(gIsIE){
			var e = window.event;
		}
		getCaret();
		var dvSwfBox = $("createSwf");
		var iX = e.clientX;
		var iY = e.clientY;
		dvSwfBox.style.display = "";
		dvSwfBox.style.left = (iX-350) + "px";
		dvSwfBox.style.top = 33 + "px";
	}
}
String.prototype.trim = function(){
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
function fSetBorderMouseOver(obj) {
	obj.style.borderRight="1px solid #aaa";
	obj.style.borderBottom="1px solid #aaa";
	obj.style.borderTop="1px solid #fff";
	obj.style.borderLeft="1px solid #fff";
}
function fSetBorderMouseOut(obj) {
	obj.style.border="none";
}
function fSetBorderMouseDown(obj) {
	obj.style.borderRight="1px #F3F8FC solid";
	obj.style.borderBottom="1px #F3F8FC solid";
	obj.style.borderTop="1px #cccccc solid";
	obj.style.borderLeft="1px #cccccc solid";
}
function fDisplayElement(element,displayValue) {
	if(gIEVer<=5.01 && gIsIE){
		alert('只支持IE 5.01以上版本');
		return;
	}
	fHideMenu();
	if ( typeof element == "string" )
		element = $(element);
	if (element == null) return;
	element.style.display = displayValue;
	if(gIsIE){
		var e = event;
		var target = e.srcElement;
	}else{
		var e = ev;
		var target = e.target;
	}
	var iX = f_GetX(target);
	element.style.display = "";
	element.style.left = (iX) + "px";
	element.style.top = 33 + "px";
	return true;
}
function fSetModeTip(obj){
	var x = f_GetX(obj);
	var y = f_GetY(obj);
	var dvModeTip = $("dvModeTip");
	if(!dvModeTip){
		var dv = document.createElement("DIV");
		dv.style.position = "absolute";
		dv.style.top = 33 + "px";
		dv.style.left = (x-40) + "px";
		dv.style.zIndex = "999";
		dv.style.fontSize = "12px";
		dv.id = "dvModeTip";
		dv.style.padding = "2px";
		dv.style.border = "1px #000000 solid";
		dv.style.backgroundColor = "#FFFFCC";
		dv.innerHTML = "编辑源码";
		document.body.appendChild(dv);
	}else{
		dvModeTip.style.display = "";
	}
}
function fHideTip(){
	$("dvModeTip").style.display = "none";
}
function f_GetX(e)
{
	var l=e.offsetLeft;
	while(e=e.offsetParent){
		l+=e.offsetLeft;
	}
	return l;
}
function f_GetY(e)
{
	var t=e.offsetTop;
	while(e=e.offsetParent){
		t+=e.offsetTop;
	}
	return t;
}
function fHideMenu(){
	try{
		var arr = ["fontface", "fontsize", "dvForeColor", "dvPortrait", "divAlign", "divList" ,"divInOut", "editFaceBox", "createUrl", "createImg", "createSwf", "createPage"];
		for(var i=0;i<arr.length;i++){
			var obj = $(arr[i]);
			if(obj){
				obj.style.display = "none";
			}
		}
		try{
			parent.LetterPaper.control(window, "hide");
		}catch(exp){}
	}catch(exp){}
}

function $(id){
	return document.getElementById(id);
}
function fHide(obj){
	obj.style.display="none";
}

function pageBreak(e, show) {
	if(!show) {
		var obj = $('pageTitle');
		var title = obj ? obj.value : '';
		if(obj) {
			obj.value = '';
		}
		var insertText = title ? '[title='+title+']': '';
		setCaret();
		format("insertHTML", '<br /><strong>##########NextPage'+insertText+'##########</strong><br /><br />');
		if(parent.showInnerNav && typeof parent.showInnerNav == 'function') {
			parent.showInnerNav();
		}
		fHide($('createPage'));
	} else {
		if(gIsIE){
			var e = window.event;
		}
		getCaret();
		var dvSwfBox = $("createPage");
		var iX = e.clientX;
		var iY = e.clientY;
		dvSwfBox.style.display = "";
		dvSwfBox.style.left = (iX-300) + "px";
		dvSwfBox.style.top = 33 + "px";
	}
}
function changeEditType(flag, ev){
	gIsHtml = flag;
	try{
		var mod = parent.MM["compose"];
		mod.html = flag;
	}catch(exp){}
	try{
		var dvhtml = $("dvhtml");
		var dvtext = $("dvtext");
		var HtmlEditor = window.frames["HtmlEditor"];
		var ifmHtmlEditor = $("HtmlEditor");
		var sourceEditor = $("sourceEditor");
		var switchMode = $("switchMode");
		var sourceEditor = $("sourceEditor");
		var dvHtmlLnk = $("dvHtmlLnk");
		var dvToolbar = $('dvToolbar');
		if(flag){
			dvhtml.style.display = "";
			dvtext.style.display = "none";
			dvToolbar.className = 'toobar';

			if(switchMode.checked){
				sourceEditor.value = dvtext.value;
				$('uchome-editstatus').value = 'code';
			}else{
				if(document.all){
					HtmlEditor.document.body.innerText = dvtext.value;
				} else {
					HtmlEditor.document.body.innerHTML = dvtext.value.unescapeHTML();
				}
				$('uchome-editstatus').value = 'html';
			}
		}else{
			function sub1(){
				dvhtml.style.display = "none";
				dvtext.style.display = "";
				dvToolbar.className = 'toobarmini';
				if(switchMode.checked){
					dvtext.value = sourceEditor.value.unescapeHTML();
				}else{
					if(document.all){
						dvtext.value = HtmlEditor.document.body.innerText;
					}else{
						dvtext.value = HtmlEditor.document.body.innerHTML.unescapeHTML();
					}
				}
			}
			ev = ev || event;
			if(ev){
				if(window.confirm("转换为纯文本时将会遗失某些格式。\n您确定要继续吗？")){
					$('uchome-editstatus').value = 'text';
					sub1();
				}else{
					return;
				}
			}
		}
	}catch(exp){

	}
}

function changeEditFull(flag, ev) {
	if(parent.changeEditFull) {
		parent.changeEditFull(flag);
		ev = ev || event;
		var ele = ev.target || ev.srcElement;
		ele.innerHTML = flag ? '返回' : '全屏';
		ele.onclick = function() {changeEditFull(!flag, ev)};
	}
}
String.prototype.stripTags = function(){
    return this.replace(/<\/?[^>]+>/gi, '');
};
String.prototype.unescapeHTML = function(){
    var div = document.createElement('div');
    div.innerHTML = this.stripTags();
    return div.childNodes[0].nodeValue;
};
var s = "";
var hex = new Array(6)
hex[0] = "FF"
hex[1] = "CC"
hex[2] = "99"
hex[3] = "66"
hex[4] = "33"
hex[5] = "00"
function drawCell(red, green, blue) {
	var color = '#' + red + green + blue;
	if(color == "#000066") color = "#000000";
	s += '<TD BGCOLOR="' + color + '" style="height:12px;width:12px;" >';
	s += '<IMG '+ ((document.all)?"":"src='editor_none.gif'") +' HEIGHT=12 WIDTH=12>';
	s += '</TD>';
}
function drawRow(red, blue) {
	s += '<TR>';

	for (var i = 0; i < 6; ++i) {
		drawCell(red, hex[i], blue)
	}
	s += '</TR>';
}
function drawTable(blue) {
	s += '<TABLE CELLPADDING=0 CELLSPACING=0 BORDER=0>';
	for (var i = 0; i < 6; ++i) {
		drawRow(hex[i], blue)
	}
	s += '</TABLE>';
}
function drawCube() {
	s += '<TABLE CELLPADDING=0 CELLSPACING=0 style="border:1px #888888 solid"><TR>';
	for (var i = 0; i < 2; ++i) {
		s += '<TD BGCOLOR="#FFFFFF">';
		drawTable(hex[i])
		s += '</TD>';
	}
	s += '</TR><TR>';
	for (var i = 2; i < 4; ++i) {
		s += '<TD BGCOLOR="#FFFFFF">';
		drawTable(hex[i])
		s += '</TD>';
	}
	s += '</TR></TABLE>';
	return s;
}

function EV(){}
EV.getTarget		= fGetTarget;
EV.getEvent			= fGetEvent;
EV.stopEvent		= fStopEvent;
EV.stopPropagation	= fStopPropagation;
EV.preventDefault	= fPreventDefault;


function fGetTarget(ev, resolveTextNode){
	if(!ev) ev = this.getEvent();
	var t = ev.target || ev.srcElement;

	if (resolveTextNode && t && "#text" == t.nodeName) {
		return t.parentNode;
	} else {
		return t;
	}
}

function fGetEvent (e) {
	var ev = e || window.event;

	if (!ev) {
		var c = this.getEvent.caller;
		while (c) {
			ev = c.arguments[0];
			if (ev && Event == ev.constructor) {
				break;
			}
			c = c.caller;
		}
	}

	return ev;
}

function fStopEvent(ev) {
	if(!ev) ev = this.getEvent();
	this.stopPropagation(ev);
	this.preventDefault(ev);
}


function fStopPropagation(ev) {
	if(!ev) ev = this.getEvent();
	if (ev.stopPropagation) {
		ev.stopPropagation();
	} else {
		ev.cancelBubble = true;
	}
}


function fPreventDefault(ev) {
	if(!ev) ev = this.getEvent();
	if (ev.preventDefault) {
		ev.preventDefault();
	} else {
		ev.returnValue = false;
	}
}

function getExt(path) {
	return path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
}

function checkURL(obj, mod) {
	if(mod) {
		if(obj.value == 'http://') {
			obj.value = '';
		}
	} else {
		if(obj.value == '') {
			obj.value = 'http://';
		}
	}
}