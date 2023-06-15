var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function $(id) {
	return document.getElementById(id);
}

Array.prototype.push = function(value) {
	this[this.length] = value;
	return this.length;
}

function getcookie(name) {
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	seconds = seconds ? seconds : 8400000;
	var expires = new Date();
	expires.setTime(expires.getTime() + seconds);
	document.cookie = escape(cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function _attachEvent(obj, evt, func) {
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(obj.attachEvent) {
		obj.attachEvent("on" + evt, func);
	}
}

function _cancelBubble(e, returnValue) {
	if(!e) return ;
	if(is_ie) {
		if(!returnValue) e.returnValue = false;
		e.cancelBubble = true;
	} else {
		e.stopPropagation();
		if(!returnValue) e.preventDefault();
	}
}

function checkall(name) {
	var e = is_ie ? event : checkall.caller.arguments[0];
	obj = is_ie ? e.srcElement : e.target;
	var arr = document.getElementsByName(name);
	var k = arr.length;
	for(var i=0; i<k; i++) {
		arr[i].checked = obj.checked;
	}
}

function getposition(obj) {
	var r = new Array();
	r['x'] = obj.offsetLeft;
	r['y'] = obj.offsetTop;
	while(obj = obj.offsetParent) {
		r['x'] += obj.offsetLeft;
		r['y'] += obj.offsetTop;
	}
	return r;
}

function addMouseEvent(obj){
	var checkbox,atr,ath,i;
	atr=obj.getElementsByTagName("tr");
	for(i=0;i<atr.length;i++){
		atr[i].onclick=function(){
			ath=this.getElementsByTagName("th");
			checkbox=this.getElementsByTagName("input")[0];
			if(!ath.length && checkbox.getAttribute("type")=="checkbox"){
				if(this.className!="currenttr"){
					this.className="currenttr";
					checkbox.checked=true;
				}else{
					this.className="";
					checkbox.checked=false;
				}
			}
		}
	}
}

if(is_ie) document.documentElement.addBehavior("#default#userdata");

function setdata(key, value){
	if(is_ie){
		document.documentElement.load(key);
		document.documentElement.setAttribute("value", value);
		document.documentElement.save(key);
		return  document.documentElement.getAttribute("value");
	} else {
		sessionStorage.setItem(key,value);
	}
}

function getdata(key){
	if(is_ie){
		document.documentElement.load(key);
		return document.documentElement.getAttribute("value");
	} else {
		return sessionStorage.getItem(key) && sessionStorage.getItem(key).toString().length == 0 ? '' : (sessionStorage.getItem(key) == null ? '' : sessionStorage.getItem(key));
	}
}

function form_option_selected(obj, value) {
	for(var i=0; i<obj.options.length; i++) {
		if(obj.options[i].value == value) {
			obj.options[i].selected = true;
		}
	}
}

function switchcredit(obj, value) {
	var creditsettings = credit[value];
	var s = '<select name="credit' + obj + '">';
	for(var i in creditsettings) {
		s += '<option value="' + creditsettings[i][0] + '">' + creditsettings[i][1] + '</option>';
	}
	s += '</select>';
	$(obj).innerHTML = s;
}

function setselect(selectobj, value) {
	var len = selectobj.options.length;
	for(i = 0;i < len;i++) {
		if(selectobj.options[i].value == value) {
			selectobj.options[i].selected = true;
		}
	}
}

function show(id, display) {
	if(!$(id)) return false;
	if(display == 'auto') {
		$(id).style.display = $(id).style.display == '' ? 'none' : '';
	} else {
		$(id).style.display = display;
	}
}