var Ajaxs = new Array();
function Ajax(waitId) {
	var aj = new Object();
	aj.waitId = waitId ? $(waitId) : null;
	aj.targetUrl = '';
	aj.sendString = '';
	aj.resultHandle = null;
	aj.loading = '<img src="image/common/loading.gif" style="margin: 3px; vertical-align: middle" />Loading... ';
	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) request.overrideMimeType('text/xml');
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) return request;
				} catch(e) {/*alert(e.message);*/}
			}
		}
		return request;
	}

	aj.request = aj.createXMLHttpRequest();

	if(aj.waitId) {
		aj.waitId.orgdisplay = aj.waitId.style.display;
		aj.waitId.style.display = '';
		aj.waitId.innerHTML = aj.loading;
	}

	aj.processHandle = function() {
		if(aj.request.readyState == 4 && aj.request.status == 200) {
			for(k in Ajaxs) {
				if(Ajaxs[k] == aj.targetUrl) Ajaxs[k] = null;
			}
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
				aj.waitId.style.display = aj.waitId.orgdisplay;
			}
			aj.resultHandle(aj.request.responseXML.lastChild.firstChild.nodeValue);
		}
	}

	aj.get = function(targetUrl, resultHandle) {
		if(in_array(targetUrl, Ajaxs)) {
			return false;
		} else {
			Ajaxs.push(targetUrl);
		}

		aj.targetUrl = targetUrl;
		aj.request.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		if(window.XMLHttpRequest) {
			aj.request.open('GET', aj.targetUrl);
			aj.request.send(null);
		} else {
		        aj.request.open("GET", targetUrl, true);
		        aj.request.send();
		}
	}

	return aj;
}

function show(id, display) {
	if(display == 'auto') {
		$(id).style.display = $(id).style.display == '' ? 'none' : '';
	} else {
		$(id).style.display = display;
	}
}

function ajaxget(url, showId, waitId, display, recall) {
	e = is_ie ? event : ajaxget.caller.arguments[0];
	ajaxget2(e, url, showId, waitId, display, recall);
	_cancelBubble(e);
}

function ajaxget2(e, url, showId, waitId, display, recall) {
	target = e ? (is_ie ? e.srcElement : e.target) : null;
	display = display ? display : '';
	var x = new Ajax(waitId);
	x.showId = showId;
	x.display = display;
	var sep = url.indexOf('?') != -1 ? '&' : '?';
	x.target = target;
	x.recall = recall;
	x.get(url+sep+'inajax=1', function(s) {
		if(x.display == 'auto' && x.target) {
			x.target.onclick = newfunc('show', x.showId, 'auto');
		}
		show(x.showId, x.display);
		$(x.showId).innerHTML = s;
		evalscript(s);
		if(x.recall)eval(x.recall);
	});
	_cancelBubble(e);
}


var evalscripts = new Array();
function evalscript(s) {
	if(!s || s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?src=\"([^\x00]+?)\"[^\>]*( reload=\"1\")?><\/script>/ig;
	var arr = new Array();
	while(arr = p.exec(s)) appendscript(arr[1], '', arr[2]);
	p = /<script[^\>]*?( reload=\"1\")?>([^\x00]+?)<\/script>/ig;
	while(arr = p.exec(s)) appendscript('', arr[2], arr[1]);
	return s;
}

function appendscript(src, text, reload) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $(id)) {
		$(id).parentNode.removeChild($(id));
	}
	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	if(src) {
		scriptNode.src = src;
	} else if(text){
		scriptNode.text = text;
	}
	$('append').appendChild(scriptNode);
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function in_array(needle, haystack) {
	for(var i in haystack) 	{if(haystack[i] == needle) return true;}
	return false;
}

function newfunc(func){
	var args = new Array();
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(e){
		window[func].apply(window, args);
		_cancelBubble(is_ie ? event : e);
	}
}

function ajaxmenu(url, position) {
	e = is_ie ? event : ajaxmenu.caller.arguments[0];
	controlid = is_ie ? e.srcElement : e.target;
	var menuid = hash(url);
	createmenu(menuid);

	showmenu2(e, menuid, position, controlid);
	if(!$(menuid).innerHTML) {
		ajaxget2(e, url, menuid, menuid, '', "setposition('" + menuid + "', '" + position + "', '" + controlid + "')");
	} else {
	}
	_cancelBubble(e);
}

var ajaxpostHandle = null;
function ajaxpost(formid, showid, recall) {
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $(ajaxframeid);
	if(ajaxframe == null) {
		if (is_ie) {
			ajaxframe = document.createElement("<iframe name='" + ajaxframeid + "' id='" + ajaxframeid + "'></iframe>");
		} else {
			ajaxframe = document.createElement("iframe");
			ajaxframe.name = ajaxframeid;
			ajaxframe.id = ajaxframeid;
		}
		ajaxframe.style.display = 'none';
		$('append').appendChild(ajaxframe);
	}
	$(formid).target = ajaxframeid;
	ajaxpostHandle = [formid, showid, ajaxframeid, recall];
	_attachEvent(ajaxframe, 'load', ajaxpost_load);
	$(formid).submit();
	return false;
}

function ajaxpost_load() {
	var s = (is_ie && $(ajaxpostHandle[2])) ? $(ajaxpostHandle[2]).contentWindow.document.XMLDocument.text : $(ajaxpostHandle[2]).contentWindow.document.documentElement.firstChild.nodeValue;
	evalscript(s);
	if(s) {
		$(ajaxpostHandle[1]).innerHTML = s;
		if(ajaxpostHandle[3]) {
			eval(ajaxpostHandle[3]);
		}
	}
}