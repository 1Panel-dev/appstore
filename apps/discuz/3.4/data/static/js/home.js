/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: home.js 34052 2013-09-25 06:18:43Z andyzheng $
*/

var note_step = 0;
var note_oldtitle = document.title;
var note_timer;

function addSort(obj) {
	if (obj.value == 'addoption') {
		showWindow('addoption', 'home.php?mod=spacecp&ac=blog&op=addoption&handlekey=addoption&oid='+obj.id);
 	}
}

function addOption(sid, aid) {
	var obj = $(aid);
	var newOption = $(sid).value;
	$(sid).value = "";
	if (newOption!=null && newOption!='') {
		var newOptionTag=document.createElement('option');
		newOptionTag.text=newOption;
		newOptionTag.value="new:" + newOption;
		try {
			obj.add(newOptionTag, obj.options[0]);
		} catch(ex) {
			obj.add(newOptionTag, obj.selecedIndex);
		}
		obj.value="new:" + newOption;
	} else {
		obj.value=obj.options[0].value;
	}
}

function blogAddOption(sid, aid) {
	var obj = $(aid);
	var newOption = $(sid).value;
	newOption = newOption.replace(/^\s+|\s+$/g,"");
	$(sid).value = "";
	if (newOption!=null && newOption!='') {
		var newOptionTag=document.createElement('option');
		newOptionTag.text=newOption;
		newOptionTag.value="new:" + newOption;
		try {
			obj.add(newOptionTag, obj.options[0]);
		} catch(ex) {
			obj.add(newOptionTag, obj.selecedIndex);
		}
		obj.value="new:" + newOption;
		return true;
	} else {
		alert('分类名不能为空！');
		return false;
	}
}

function blogCancelAddOption(aid) {
	var obj = $(aid);
	obj.value=obj.options[0].value;
}

function checkAll(form, name) {
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name.match(name)) {
			e.checked = form.elements['chkall'].checked;
		}
	}
}

function cnCode(str) {
	str = str.replace(/<\/?[^>]+>|\[\/?.+?\]|"/ig, "");
	str = str.replace(/\s{2,}/ig, ' ');
	return BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(str) : str;
}

function getExt(path) {
	return path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
}

function resizeImg(id,size) {
	var theImages = $(id).getElementsByTagName('img');
	for (i=0; i<theImages.length; i++) {
		theImages[i].onload = function() {
			if (this.width > size) {
				this.style.width = size + 'px';
				if (this.parentNode.tagName.toLowerCase() != 'a') {
					var zoomDiv = document.createElement('div');
					this.parentNode.insertBefore(zoomDiv,this);
					zoomDiv.appendChild(this);
					zoomDiv.style.position = 'relative';
					zoomDiv.style.cursor = 'pointer';

					this.title = '点击图片，在新窗口显示原始尺寸';

					var zoom = document.createElement('img');
					zoom.src = 'image/zoom.gif';
					zoom.style.position = 'absolute';
					zoom.style.marginLeft = size -28 + 'px';
					zoom.style.marginTop = '5px';
					this.parentNode.insertBefore(zoom,this);

					zoomDiv.onmouseover = function() {
						zoom.src = 'image/zoom_h.gif';
					};
					zoomDiv.onmouseout = function() {
						zoom.src = 'image/zoom.gif';
					};
					zoomDiv.onclick = function() {
						window.open(this.childNodes[1].src);
					};
				}
			}
		}
	}
}

function zoomTextarea(id, zoom) {
	zoomSize = zoom ? 10 : -10;
	obj = $(id);
	if(obj.rows + zoomSize > 0 && obj.cols + zoomSize * 3 > 0) {
		obj.rows += zoomSize;
		obj.cols += zoomSize * 3;
	}
}

function ischeck(id, prefix) {
	form = document.getElementById(id);
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name.match(prefix) && e.checked) {
			if(confirm("您确定要执行本操作吗？")) {
				return true;
			} else {
				return false;
			}
		}
	}
	alert('请选择要操作的对象');
	return false;
}

function copyRow(tbody) {
	var add = false;
	var newnode;
	if($(tbody).rows.length == 1 && $(tbody).rows[0].style.display == 'none') {
		$(tbody).rows[0].style.display = '';
		newnode = $(tbody).rows[0];
	} else {
		newnode = $(tbody).rows[0].cloneNode(true);
		add = true;
	}
	tags = newnode.getElementsByTagName('input');
	for(i = 0;i < tags.length;i++) {
		if(tags[i].name == 'pics[]') {
			tags[i].value = 'http://';
		}
	}
	if(add) {
		$(tbody).appendChild(newnode);
	}
}

function delRow(obj, tbody) {
	if($(tbody).rows.length == 1) {
		var trobj = obj.parentNode.parentNode;
		tags = trobj.getElementsByTagName('input');
		for(i = 0;i < tags.length;i++) {
			if(tags[i].name == 'pics[]') {
				tags[i].value = 'http://';
			}
		}
		trobj.style.display='none';
	} else {
		$(tbody).removeChild(obj.parentNode.parentNode);
	}
}

function insertWebImg(obj) {
	if(checkImage(obj.value)) {
		insertImage(obj.value);
		obj.value = 'http://';
	} else {
		alert('图片地址不正确');
	}
}

function checkFocus(target) {
	var obj = $(target);
	if(!obj.hasfocus) {
		obj.focus();
	}
}
function insertImage(text) {
	text = "\n[img]" + text + "[/img]\n";
	insertContent('message', text);
}

function insertContent(target, text) {
	var obj = $(target);
	selection = document.selection;
	checkFocus(target);
	if(!isUndefined(obj.selectionStart)) {
		var opn = obj.selectionStart + 0;
		obj.value = obj.value.substr(0, obj.selectionStart) + text + obj.value.substr(obj.selectionEnd);
	} else if(selection && selection.createRange) {
		var sel = selection.createRange();
		sel.text = text;
		sel.moveStart('character', -strlen(text));
	} else {
		obj.value += text;
	}
}

function checkImage(url) {
	var re = /^http\:\/\/.{5,200}\.(jpg|gif|png)$/i;
	return url.match(re);
}

function stopMusic(preID, playerID) {
	var musicFlash = preID.toString() + '_' + playerID.toString();
	if($(musicFlash)) {
		$(musicFlash).SetVariable('closePlayer', 1);
	}
}
function showFlash(host, flashvar, obj, shareid) {
	var flashAddr = {
		'youku.com' : 'http://player.youku.com/player.php/sid/FLASHVAR=/v.swf',
		'ku6.com' : 'http://player.ku6.com/refer/FLASHVAR/v.swf',
		'youtube.com' : 'http://www.youtube.com/v/FLASHVAR',
		'5show.com' : 'http://www.5show.com/swf/5show_player.swf?flv_id=FLASHVAR',
		'sina.com.cn' : 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
		'sohu.com' : 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
		'mofile.com' : 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
		'music' : 'FLASHVAR',
		'flash' : 'FLASHVAR'
	};
	var flash = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="480" height="400">'
	    + '<param name="movie" value="FLASHADDR" />'
	    + '<param name="quality" value="high" />'
	    + '<param name="bgcolor" value="#FFFFFF" />'
	    + '<param name="allowScriptAccess" value="never" />'
	    + '<param name="allowNetworking" value="internal" />'
	    + '<embed width="480" height="400" menu="false" quality="high" allowScriptAccess="never" allowNetworking="internal" src="FLASHADDR" type="application/x-shockwave-flash" />'
	    + '</object>';
	var videoFlash = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="480" height="450">'
		+ '<param value="transparent" name="wmode"/>'
		+ '<param value="FLASHADDR" name="movie" />'
		+ '<param name="allowScriptAccess" value="never" />'
		+ '<param name="allowNetworking" value="none" />'
		+ '<embed src="FLASHADDR" wmode="transparent" allowfullscreen="true" type="application/x-shockwave-flash" width="480" height="450" allowScriptAccess="never" allowNetworking="internal"></embed>'
		+ '</object>';
	var musicFlash = '<object id="audioplayer_SHAREID" height="24" width="290" data="' + STATICURL + 'image/common/player.swf" type="application/x-shockwave-flash">'
		+ '<param value="' + STATICURL + 'image/common/player.swf" name="movie"/>'
		+ '<param value="autostart=yes&bg=0xCDDFF3&leftbg=0x357DCE&lefticon=0xF2F2F2&rightbg=0xF06A51&rightbghover=0xAF2910&righticon=0xF2F2F2&righticonhover=0xFFFFFF&text=0x357DCE&slider=0x357DCE&track=0xFFFFFF&border=0xFFFFFF&loader=0xAF2910&soundFile=FLASHADDR" name="FlashVars"/>'
		+ '<param value="high" name="quality"/>'
		+ '<param value="false" name="menu"/>'
		+ '<param value="#FFFFFF" name="bgcolor"/>'
	    + '</object>';
	var musicMedia = '<object height="64" width="290" data="FLASHADDR" type="audio/x-ms-wma">'
	    + '<param value="FLASHADDR" name="src"/>'
	    + '<param value="1" name="autostart"/>'
	    + '<param value="true" name="controller"/>'
	    + '</object>';
	var flashHtml = videoFlash;
	var videoMp3 = true;
	if('' == flashvar) {
		alert('音乐地址错误，不能为空');
		return false;
	}
	if('music' == host) {
		var mp3Reg = new RegExp('.mp3$', 'ig');
		var flashReg = new RegExp('.swf$', 'ig');
		flashHtml = musicMedia;
		videoMp3 = false;
		if(mp3Reg.test(flashvar)) {
			videoMp3 = true;
			flashHtml = musicFlash;
		} else if(flashReg.test(flashvar)) {
			videoMp3 = true;
			flashHtml = flash;
		}
	}
	flashvar = encodeURI(flashvar);
	if(flashAddr[host]) {
		var flash = flashAddr[host].replace('FLASHVAR', flashvar);
		flashHtml = flashHtml.replace(/FLASHADDR/g, flash);
		flashHtml = flashHtml.replace(/SHAREID/g, shareid);
	}

	if(!obj) {
		$('flash_div_' + shareid).innerHTML = flashHtml;
		return true;
	}
	if($('flash_div_' + shareid)) {
		$('flash_div_' + shareid).style.display = '';
		$('flash_hide_' + shareid).style.display = '';
		obj.style.display = 'none';
		return true;
	}
	if(flashAddr[host]) {
		var flashObj = document.createElement('div');
		flashObj.id = 'flash_div_' + shareid;
		obj.parentNode.insertBefore(flashObj, obj);
		flashObj.innerHTML = flashHtml;
		obj.style.display = 'none';
		var hideObj = document.createElement('div');
		hideObj.id = 'flash_hide_' + shareid;
		var nodetxt = document.createTextNode("收起");
		hideObj.appendChild(nodetxt);
		obj.parentNode.insertBefore(hideObj, obj);
		hideObj.style.cursor = 'pointer';
		hideObj.onclick = function() {
			if(true == videoMp3) {
				stopMusic('audioplayer', shareid);
				flashObj.parentNode.removeChild(flashObj);
				hideObj.parentNode.removeChild(hideObj);
			} else {
				flashObj.style.display = 'none';
				hideObj.style.display = 'none';
			}
			obj.style.display = '';
		};
	}
}

function startMarquee(h, speed, delay, sid) {
	var t = null;
	var p = false;
	var o = $(sid);
	o.innerHTML += o.innerHTML;
	o.onmouseover = function() {p = true};
	o.onmouseout = function() {p = false};
	o.scrollTop = 0;
	function start() {
	    t = setInterval(scrolling, speed);
	    if(!p) {
			o.scrollTop += 2;
		}
	}
	function scrolling() {
	    if(p) return;
		if(o.scrollTop % h != 0) {
	        o.scrollTop += 2;
	        if(o.scrollTop >= o.scrollHeight/2) o.scrollTop = 0;
	    } else {
	        clearInterval(t);
	        setTimeout(start, delay);
	    }
	}
	setTimeout(start, delay);
}

function readfeed(obj, id) {
	if(Cookie.get("read_feed_ids")) {
		var fcookie = Cookie.get("read_feed_ids");
		fcookie = id + ',' + fcookie;
	} else {
		var fcookie = id;
	}
	Cookie.set("read_feed_ids", fcookie, 48);
	obj.className = 'feedread';
}

function showreward() {
	if(Cookie.get('reward_notice_disable')) {
		return false;
	}
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=getreward', function(s){
		if(s) {
			msgwin(s, 2000);
		}
	});
}

function msgwin(s, t) {

	var msgWinObj = $('msgwin');
	if(!msgWinObj) {
		var msgWinObj = document.createElement("div");
		msgWinObj.id = 'msgwin';
		msgWinObj.style.display = 'none';
		msgWinObj.style.position = 'absolute';
		msgWinObj.style.zIndex = '100000';
		$('append_parent').appendChild(msgWinObj);
	}
	msgWinObj.innerHTML = s;
	msgWinObj.style.display = '';
	msgWinObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)';
	msgWinObj.style.opacity = 0;
	var sTop = document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
	pbegin = sTop + (document.documentElement.clientHeight / 2);
	pend = sTop + (document.documentElement.clientHeight / 5);
	setTimeout(function () {showmsgwin(pbegin, pend, 0, t)}, 10);
	msgWinObj.style.left = ((document.documentElement.clientWidth - msgWinObj.clientWidth) / 2) + 'px';
	msgWinObj.style.top = pbegin + 'px';
}

function showmsgwin(b, e, a, t) {
	step = (b - e) / 10;
	var msgWinObj = $('msgwin');
	newp = (parseInt(msgWinObj.style.top) - step);
	if(newp > e) {
		msgWinObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + a + ')';
		msgWinObj.style.opacity = a / 100;
		msgWinObj.style.top = newp + 'px';
		setTimeout(function () {showmsgwin(b, e, a += 10, t)}, 10);
	} else {
		msgWinObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=100)';
		msgWinObj.style.opacity = 1;
		setTimeout('displayOpacity(\'msgwin\', 100)', t);
	}
}

function displayOpacity(id, n) {
	if(!$(id)) {
		return;
	}
	if(n >= 0) {
		n -= 10;
		$(id).style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + n + ')';
		$(id).style.opacity = n / 100;
		setTimeout('displayOpacity(\'' + id + '\',' + n + ')', 50);
	} else {
		$(id).style.display = 'none';
		$(id).style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=100)';
		$(id).style.opacity = 1;
	}
}

function urlto(url) {
	window.location.href = url;
}

function explode(sep, string) {
	return string.split(sep);
}

function selector(pattern, context) {
	var re = new RegExp('([a-z0-9]*)([\.#:]*)(.*|$)', 'ig');
	var match = re.exec(pattern);
	var conditions = cc = [];
	if (match[2] == '#')	conditions.push(['id', '=', match[3]]);
	else if(match[2] == '.')	conditions.push(['className', '~=', match[3]]);
	else if(match[2] == ':')	conditions.push(['type', '=', match[3]]);
	var s = match[3].replace(/\[(.*)\]/g,'$1').split('@');
	for(var i=0; i<s.length; i++) {
		if (cc = /([\w]+)([=^%!$~]+)(.*)$/.exec(s[i]))
			conditions.push([cc[1], cc[2], cc[3]]);
	}
	var list = conditions[0] && conditions[0][0] == 'id' ? [document.getElementById(conditions[0][2])] : (context || document).getElementsByTagName(match[1] || "*");
	if(!list || !list.length)	return [];
	if(conditions) {
		var elements = [];
		var attrMapping = {'for': 'htmlFor', 'class': 'className'};
		for(var i=0; i<list.length; i++) {
			var pass = true;
			for(var j=0; j<conditions.length; j++) {
				var attr = attrMapping[conditions[j][0]] || conditions[j][0];
				var val = list[i][attr] || (list[i].getAttribute ? list[i].getAttribute(attr) : '');
				var pattern = null;
				if(conditions[j][1] == '=') {
					pattern = new RegExp('^'+conditions[j][2]+'$', 'i');
				} else if(conditions[j][1] == '^=') {
					pattern = new RegExp('^' + conditions[j][2], 'i');
				} else if(conditions[j][1] == '$=') {
					pattern = new RegExp(conditions[j][2] + '$', 'i');
				} else if(conditions[j][1] == '%=') {
					pattern = new RegExp(conditions[j][2], 'i');
				} else if(conditions[j][1] == '~=') {
					pattern = new RegExp('(^|[ ])' + conditions[j][2] + '([ ]|$)', 'i');
				}
				if(pattern && !pattern.test(val)) {
					pass = false;
					break;
				}
			}
			if(pass) elements.push(list[i]);
		}
		return elements;
	} else {
		return list;
	}
}

function showBlock(cid, oid) {
	if(parseInt(cid)) {
		var listObj = $(oid);
		var x = new Ajax();
		x.get('portal.php?mod=cp&ac=block&operation=getblock&classid='+cid, function(s){
			listObj.innerHTML = s;
		})
	}
}

function resizeTx(obj){
	var oid = obj.id + '_limit';
	if(!BROWSER.ie) obj.style.height = 0;
	obj.style.height = obj.scrollHeight + 'px';
	if($(oid)) $(oid).style.display = obj.scrollHeight > 30 ? '':'none';
}

function showFace(showid, target, dropstr) {
	if($(showid + '_menu') != null) {
		$(showid+'_menu').style.display = '';
	} else {
		var faceDiv = document.createElement("div");
		faceDiv.id = showid+'_menu';
		faceDiv.className = 'p_pop facel';
		faceDiv.style.position = 'absolute';
		faceDiv.style.zIndex = 1001;
		var faceul = document.createElement("ul");
		for(i=1; i<31; i++) {
			var faceli = document.createElement("li");
			faceli.innerHTML = '<img src="' + STATICURL + 'image/smiley/comcom/'+i+'.gif" onclick="insertFace(\''+showid+'\','+i+', \''+ target +'\', \''+dropstr+'\')" style="cursor:pointer; position:relative;" />';
			faceul.appendChild(faceli);
		}
		faceDiv.appendChild(faceul);
		$('append_parent').appendChild(faceDiv)
	}
	setMenuPosition(showid, 0);
	doane();
	_attachEvent(document.body, 'click', function(){if($(showid+'_menu')) $(showid+'_menu').style.display = 'none';});
}

function insertFace(showid, id, target, dropstr) {
	var faceText = '[em:'+id+':]';
	if($(target) != null) {
		insertContent(target, faceText);
		if(dropstr) {
			$(target).value = $(target).value.replace(dropstr, "");
		}
	}
}

function wall_add(id) {
	var obj = $('comment_ul');
	var newdl = document.createElement("dl");
	newdl.id = 'comment_'+id+'_li';
	newdl.className = 'bbda cl';
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=comment&inajax=1&cid='+id, function(s){
		newdl.innerHTML = s;
	});
	obj.insertBefore(newdl, obj.firstChild);
	if($('comment_message')) {
		$('comment_message').value= '';
	}
	showCreditPrompt();
}

function share_add(sid) {
	var obj = $('share_ul');
	var newli = document.createElement("li");
	newli.id = 'share_' + sid + '_li';
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=share&inajax=1&sid='+sid, function(s){
		newli.innerHTML = s;
	});
	obj.insertBefore(newli, obj.firstChild);
	$('share_link').value = 'http://';
	$('share_general').value = '';
	showCreditPrompt();
}

function comment_add(id) {
	var obj = $('comment_ul');
	var newdl = document.createElement("dl");
	newdl.id = 'comment_'+id+'_li';
	newdl.className = 'bbda cl';
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=comment&inajax=1&cid='+id, function(s){
		newdl.innerHTML = s;
	});
	if($('comment_prepend')){
		obj = obj.firstChild;
		while (obj && obj.nodeType != 1){
			obj = obj.nextSibling;
		}
		obj.parentNode.insertBefore(newdl, obj);
	} else {
		obj.appendChild(newdl);
	}
	if($('comment_message')) {
		$('comment_message').value= '';
	}
	if($('comment_replynum')) {
		var a = parseInt($('comment_replynum').innerHTML);
		var b = a + 1;
		$('comment_replynum').innerHTML = b + '';
	}
	showCreditPrompt();
}
function comment_edit(cid) {
	var obj = $('comment_'+ cid +'_li');
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=comment&inajax=1&cid='+ cid, function(s){
		obj.innerHTML = s;
		var elems = selector('dd[class~=magicflicker]');
		for(var i=0; i<elems.length; i++){
			magicColor(elems[i]);
		}
	});
}
function comment_delete(cid) {
	var obj = $('comment_'+ cid +'_li');
	obj.style.display = "none";
	if($('comment_replynum')) {
		var a = parseInt($('comment_replynum').innerHTML);
		var b = a - 1;
		$('comment_replynum').innerHTML = b + '';
	}
}

function share_delete(sid) {
	var obj = $('share_'+ sid +'_li');
	obj.style.display = "none";
}
function friend_delete(uid) {
	var obj = $('friend_'+ uid +'_li');
	if(obj != null) obj.style.display = "none";
	var obj2 = $('friend_tbody_'+uid);
	if(obj2 != null) obj2.style.display = "none";
}
function friend_changegroup(id, result) {
	if(result) {
		var ids = explode('_', id);
		var uid = ids[1];
		var obj = $('friend_group_'+ uid);
		var x = new Ajax();
		x.get('home.php?mod=misc&ac=ajax&op=getfriendgroup&uid='+uid, function(s){
			obj.innerHTML = s;
		});
	}
}
function friend_changegroupname(group) {
	var obj = $('friend_groupname_'+ group);
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=getfriendname&inajax=1&group='+group, function(s){
		obj.innerHTML = s;
	});
}
function post_add(pid, result) {
	if(result) {
		var obj = $('post_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('home.php?mod=misc&ac=ajax&op=post', function(s){
			newli.innerHTML = s;
		});
		obj.appendChild(newli);
		if($('message')) {
			$('message').value= '';
			newnode = $('quickpostimg').rows[0].cloneNode(true);
			tags = newnode.getElementsByTagName('input');
			for(i = 0;i < tags.length;i++) {
				if(tags[i].name == 'pics[]') {
					tags[i].value = 'http://';
				}
			}
			var allRows = $('quickpostimg').rows;
			while(allRows.length) {
				$('quickpostimg').removeChild(allRows[0]);
			}
			$('quickpostimg').appendChild(newnode);
		}
		if($('post_replynum')) {
			var a = parseInt($('post_replynum').innerHTML);
			var b = a + 1;
			$('post_replynum').innerHTML = b + '';
		}
		showCreditPrompt();
	}
}
function post_edit(id, result) {
	if(result) {
		var ids = explode('_', id);
		var pid = ids[1];

		var obj = $('post_'+ pid +'_li');
		var x = new Ajax();
		x.get('home.php?mod=misc&ac=ajax&op=post&pid='+ pid, function(s){
			obj.innerHTML = s;
		});
	}
}
function post_delete(id, result) {
	if(result) {
		var ids = explode('_', id);
		var pid = ids[1];

		var obj = $('post_'+ pid +'_li');
		obj.style.display = "none";
		if($('post_replynum')) {
			var a = parseInt($('post_replynum').innerHTML);
			var b = a - 1;
			$('post_replynum').innerHTML = b + '';
		}
	}
}
function poke_send(id, result) {
	if(result) {
		var ids = explode('_', id);
		var uid = ids[1];

		if($('poke_'+ uid)) {
			$('poke_'+ uid).style.display = "none";
		}
		showCreditPrompt();
	}
}
function myfriend_post(uid) {
	if($('friend_'+uid)) {
		$('friend_'+uid).innerHTML = '<p>你们现在是好友了，接下来，您还可以：<a href="home.php?mod=space&do=wall&uid='+uid+'" class="xi2" target="_blank">给TA留言</a> ，或者 <a href="home.php?mod=spacecp&ac=poke&op=send&uid='+uid+'&handlekey=propokehk_'+uid+'" id="a_poke_'+uid+'" class="xi2" onclick="showWindow(this.id, this.href, \'get\', 0, {\'ctrlid\':this.id,\'pos\':\'13\'});">打个招呼</a></p>';
	}
	showCreditPrompt();
}
function myfriend_ignore(id) {
	var ids = explode('_', id);
	var uid = ids[1];
	$('friend_tbody_'+uid).style.display = "none";
}

function mtag_join(tagid, result) {
	if(result) {
		location.reload();
	}
}

function resend_mail(mid) {
	if(mid) {
		var obj = $('sendmail_'+ mid +'_li');
		obj.style.display = "none";
	}
}

function docomment_get(doid, key) {
	var showid = key + '_' + doid;
	var opid = key + '_do_a_op_'+doid;
	$(showid).style.display = '';
	$(showid).className = 'cmt brm';
	ajaxget('home.php?mod=spacecp&ac=doing&op=getcomment&handlekey=msg_'+doid+'&doid='+doid+'&key='+key, showid);
	if($(opid)) {
		$(opid).innerHTML = '收起';
		$(opid).onclick = function() {
			docomment_colse(doid, key);
		}
	}
	showCreditPrompt();
}

function docomment_colse(doid, key) {
	var showid = key + '_' + doid;
	var opid = key + '_do_a_op_'+doid;

	$(showid).style.display = 'none';
	$(showid).style.className = '';

	$(opid).innerHTML = '回复';
	$(opid).onclick = function() {
		docomment_get(doid, key);
	}
}

function docomment_form(doid, id, key) {
	var showid = key + '_form_'+doid+'_'+id;
	var divid = key +'_'+ doid;
	var url = 'home.php?mod=spacecp&ac=doing&op=docomment&handlekey=msg_'+id+'&doid='+doid+'&id='+id+'&key='+key;
	if(parseInt(discuz_uid)) {
		ajaxget(url, showid);
		if($(divid)) {
			$(divid).style.display = '';
		}
	} else {
		showWindow(divid, url);
	}
}

function docomment_form_close(doid, id, key) {
	var showid = key + '_form_' + doid + '_' + id;
	var opid = key + '_do_a_op_' + doid;
	$(showid).innerHTML = '';
	$(showid).style.display = 'none';
	var liObj = $(key+'_'+doid).getElementsByTagName('li');
	if(!liObj.length) {
		$(key+'_'+doid).style.display = 'none';
		if($(opid)) {
			$(opid).innerHTML = '回复';
			$(opid).onclick = function () {
				docomment_get(doid, key);
			}
		}
	}
}

function feedcomment_get(feedid) {
	var showid = 'feedcomment_'+feedid;
	var opid = 'feedcomment_a_op_'+feedid;

	$(showid).style.display = '';
	ajaxget('home.php?mod=spacecp&ac=feed&op=getcomment&feedid='+feedid+'&handlekey=feedhk_'+feedid, showid);
	if($(opid) != null) {
		$(opid).innerHTML = '收起';
		$(opid).onclick = function() {
			feedcomment_close(feedid);
		}
	}
}

function feedcomment_add(cid, feedid) {
	var obj = $('comment_ol_'+feedid);
	var newdl = document.createElement("dl");
	newdl.id = 'comment_'+cid+'_li';
	newdl.className = 'bbda cl';
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=comment&inajax=1&cid='+cid, function(s){
		newdl.innerHTML = s;
	});
	obj.appendChild(newdl);

	$('feedmessage_'+feedid).value= '';
	showCreditPrompt();
}

function feedcomment_close(feedid) {
	var showid = 'feedcomment_'+feedid;
	var opid = 'feedcomment_a_op_'+feedid;

	$(showid).style.display = 'none';
	$(showid).style.className = '';

	$(opid).innerHTML = '评论';
	$(opid).onclick = function() {
		feedcomment_get(feedid);
	}
}

function feed_post_result(feedid, result) {
	if(result) {
		location.reload();
	}
}

function feed_more_show(feedid) {
	var showid = 'feed_more_'+feedid;
	var opid = 'feed_a_more_'+feedid;

	$(showid).style.display = '';
	$(showid).className = 'sub_doing';

	$(opid).innerHTML = '&laquo; 收起列表';
	$(opid).onclick = function() {
		feed_more_close(feedid);
	}
}

function feed_more_close(feedid) {
	var showid = 'feed_more_'+feedid;
	var opid = 'feed_a_more_'+feedid;

	$(showid).style.display = 'none';

	$(opid).innerHTML = '&raquo; 更多动态';
	$(opid).onclick = function() {
		feed_more_show(feedid);
	}
}

function poll_post_result(id, result) {
	if(result) {
		var aObj = $('__'+id).getElementsByTagName("a");
		window.location.href = aObj[0].href;
	}
}

function show_click(idtype, id, clickid) {
	ajaxget('home.php?mod=spacecp&ac=click&op=show&clickid='+clickid+'&idtype='+idtype+'&id='+id, 'click_div');
	showCreditPrompt();
}

function feed_menu(feedid, show) {
	var obj = $('a_feed_menu_'+feedid);
	if(obj) {
		if(show) {
			obj.style.display='block';
		} else {
			obj.style.display='none';
		}
	}
	var obj = $('feedmagic_'+feedid);
	if(obj) {
		if(show) {
			obj.style.display='block';
		} else {
			obj.style.display='none';
		}
	}
}

function showbirthday(){
	var el = $('birthday');
	var birthday = el.value;
	el.length=0;
	el.options.add(new Option('日', ''));
	for(var i=0;i<28;i++){
		el.options.add(new Option(i+1, i+1));
	}
	if($('birthmonth').value!="2"){
		el.options.add(new Option(29, 29));
		el.options.add(new Option(30, 30));
		switch($('birthmonth').value){
			case "1":
			case "3":
			case "5":
			case "7":
			case "8":
			case "10":
			case "12":{
				el.options.add(new Option(31, 31));
			}
		}
	} else if($('birthyear').value!="") {
		var nbirthyear=$('birthyear').value;
		if(nbirthyear%400==0 || (nbirthyear%4==0 && nbirthyear%100!=0)) el.options.add(new Option(29, 29));
	}
	el.value = birthday;
}

function magicColor(elem, t) {
	t = t || 10;
	elem = (elem && elem.constructor == String) ? $(elem) : elem;
	if(!elem){
		setTimeout(function(){magicColor(elem, t-1);}, 400);
		return;
	}
	if(window.mcHandler == undefined) {
		window.mcHandler = {elements:[]};
		window.mcHandler.colorIndex = 0;
		window.mcHandler.run = function(){
			var color = ["#CC0000","#CC6D00","#CCCC00","#00CC00","#0000CC","#00CCCC","#CC00CC"][(window.mcHandler.colorIndex++) % 7];
			for(var i = 0, L=window.mcHandler.elements.length; i<L; i++)
				window.mcHandler.elements[i].style.color = color;
		}
	}
	window.mcHandler.elements.push(elem);
	if(window.mcHandler.timer == undefined) {
		window.mcHandler.timer = setInterval(window.mcHandler.run, 500);
	}
}

function passwordShow(value) {
	if(value==4) {
		$('span_password').style.display = '';
		$('tb_selectgroup').style.display = 'none';
	} else if(value==2) {
		$('span_password').style.display = 'none';
		$('tb_selectgroup').style.display = '';
	} else {
		$('span_password').style.display = 'none';
		$('tb_selectgroup').style.display = 'none';
	}
}

function getgroup(gid) {
	if(gid) {
		var x = new Ajax();
		x.get('home.php?mod=spacecp&ac=privacy&inajax=1&op=getgroup&gid='+gid, function(s){
			s = s + ' ';
			$('target_names').innerHTML += s;
		});
	}
}

function pmsendappend() {
	$('pm_append').style.display = '';
	$('pm_append').id = '';
	div = document.createElement('div');
	div.id = 'pm_append';
	div.style.display = 'none';
	$('pm_ul').appendChild(div);
	$('replymessage').value = '';
	showCreditPrompt();
}

function succeedhandle_pmsend(locationhref, message, param) {
	ajaxget('home.php?mod=spacecp&ac=pm&op=viewpmid&pmid=' + param['pmid'], 'pm_append', 'ajaxwaitid', '', null, 'pmsendappend()');
}

function getchatpmappendmember() {
	var users = document.getElementsByName('users[]');
	var appendmember = '';
	if(users.length) {
		var comma = '';
		for(var i = 0; i < users.length; i++) {
			appendmember += comma + users[i].value;
			if(!comma) {
				comma = ',';
			}
		}
	}
	if($('username').value) {
		appendmember = appendmember ? (appendmember + ',' + $('username').value) : $('username').value;
	}
	var href = $('a_appendmember').href + '&memberusername=' + appendmember;
	showWindow('a_appendmember', href, 'get', 0);
}

function markreadpm(markreadids) {
	var markreadidarr = markreadids.split(',');
	if(markreadidarr.length > 0) {
		for(var i = 0; i < markreadidarr.length; i++) {
			$(markreadidarr[i]).className = 'bbda cl';
		}
	}
}

function setpmstatus(form) {
	var ids_gpmid = new Array();
	var ids_plid = new Array();
	var type = '';
	var requesturl = '';
	var markreadids = new Array();

	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.id && e.id.match('a_delete') && e.checked) {
			var idarr = new Array();
			idarr = e.id.split('_');
			if(idarr[1] == 'deleteg') {
				ids_gpmid.push(idarr[2]);
				markreadids.push('gpmlist_' + idarr[2]);
			} else if(idarr[1] == 'delete') {
				ids_plid.push(idarr[2]);
				markreadids.push('pmlist_' + idarr[2]);
			}
		}
	}

	if(ids_gpmid.length > 0) {
		requesturl += '&gpmids=' + ids_gpmid.join(',');
	}
	if(ids_plid.length > 0) {
		requesturl += '&plids=' + ids_plid.join(',');
	}

	if(requesturl) {
		ajaxget('home.php?mod=spacecp&ac=pm&op=setpmstatus' + requesturl, '', 'ajaxwaitid', '', 'none', 'markreadpm(\''+ markreadids.join(',') +'\')');
	}
}

function changedeletedpm(pmid) {
	$('pmlist_' + pmid).style.display = 'none';
	var membernum = parseInt($('membernum').innerHTML);
	$('membernum').innerHTML = membernum - 1;
}

function changeOrderRange(id) {
	if(!$(id)) return false;
	var url = window.location.href;
	var a = $(id).getElementsByTagName('a');
	for(var i = 0; i < a.length; i++) {
		a[i].onclick = function () {
			if(url.indexOf("&orderby=") == -1) {
				url += "&orderby=" + this.id;
			} else {
				url = url.replace(/orderby=.*/, "orderby=" + this.id);
			}
			window.location = url;
			return false;
		}
	}
}

function addBlockLink(id, tag) {
	if(!$(id)) return false;
	var a = $(id).getElementsByTagName(tag);
	var taglist = {'A':1, 'INPUT':1, 'IMG':1};
	for(var i = 0, len = a.length; i < len; i++) {
		a[i].onmouseover = function () {
			if(this.className.indexOf(' hover') == -1) {
				this.className = this.className + ' hover';
			}
		};
		a[i].onmouseout = function () {
			this.className = this.className.replace(' hover', '');
		};
		a[i].onclick = function (e) {
			e = e ? e : window.event;
			var target = e.target || e.srcElement;
			if(!taglist[target.tagName]) {
				window.location.href = $(this.id + '_a').href;
			}
		};
	}
}

function checkSynSignature() {
	if($('to_signhtml').value == '1') {
		$('syn_signature').className = 'syn_signature';
		$('to_signhtml').value = '0';
	} else {
		$('syn_signature').className = 'syn_signature_check';
		$('to_signhtml').value = '1';
	}
}

function searchpostbyusername(keyword, srchuname) {
	window.location.href = 'search.php?mod=forum&srchtxt=' + keyword + '&srchuname=' + srchuname + '&searchsubmit=yes';
}

function removeVisitor(event, uid) {
	window.location = 'home.php?mod=space&uid='+uid+'&do=index&view=admin&additional=removevlog';
	event.preventDefault();
	event.stopPropagation();
}