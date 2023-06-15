/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_viewthread.js 35221 2015-02-27 08:24:39Z nemohou $
*/

var replyreload = '', attachimgST = new Array(), zoomgroup = new Array(), zoomgroupinit = new Array();

function attachimggroup(pid) {
	if(!zoomgroupinit[pid]) {
		for(i = 0;i < aimgcount[pid].length;i++) {
			zoomgroup['aimg_' + aimgcount[pid][i]] = pid;
		}
		zoomgroupinit[pid] = true;
	}
}

function attachimgshow(pid, onlyinpost) {
	onlyinpost = !onlyinpost ? false : onlyinpost;
	aimgs = aimgcount[pid];
	aimgcomplete = 0;
	loadingcount = 0;
	for(i = 0;i < aimgs.length;i++) {
		obj = $('aimg_' + aimgs[i]);
		if(!obj) {
			aimgcomplete++;
			continue;
		}
		if(onlyinpost && !obj.getAttribute('inpost')) {
			aimgcomplete++;
			continue;
		}
		if(onlyinpost && obj.getAttribute('inpost') || !onlyinpost) {
			if(!obj.status) {
				obj.status = 1;
				if(obj.getAttribute('file')) obj.src = obj.getAttribute('file');
				loadingcount++;
			} else if(obj.status == 1) {
				if(obj.complete) {
					obj.status = 2;
				} else {
					loadingcount++;
				}
			} else if(obj.status == 2) {
				aimgcomplete++;
				if(obj.getAttribute('thumbImg')) {
					thumbImg(obj);
				}
			}
			if(loadingcount >= 10) {
				break;
			}
		}
	}
	if(aimgcomplete < aimgs.length) {
		setTimeout(function () {
			attachimgshow(pid, onlyinpost);
		}, 100);
	}
}

function attachimglstshow(pid, islazy, fid, showexif) {
	var aimgs = aimgcount[pid];
	var s = '';
	if(fid) {
		s = ' onmouseover="showMenu({\'ctrlid\':this.id, \'pos\': \'12!\'});"';
	}
	if(typeof aimgcount == 'object' && $('imagelistthumb_' + pid)) {
		for(pid in aimgcount) {
			var imagelist = '';
			for(i = 0;i < aimgcount[pid].length;i++) {
				if(!$('aimg_' + aimgcount[pid][i]) || $('aimg_' + aimgcount[pid][i]).getAttribute('inpost') || parseInt(aimgcount[pid][i]) != aimgcount[pid][i]) {
					continue;
				}
				if(fid) {
					imagelist += '<div id="pattimg_' + aimgcount[pid][i] + '_menu" class="tip tip_4" style="display: none;"><div class="tip_horn"></div><div class="tip_c"><a href="forum.php?mod=ajax&action=setthreadcover&aid=' + aimgcount[pid][i] + '&fid=' + fid + '" class="xi2" onclick="showWindow(\'setcover' + aimgcount[pid][i] + '\', this.href)">设为封面</a></div></div>';
				}
				imagelist += '<div class="pattimg">' +
					'<a id="pattimg_' + aimgcount[pid][i] + '" class="pattimg_zoom" href="javascript:;"' + s + ' onclick="zoom($(\'aimg_' + aimgcount[pid][i] + '\'), attachimggetsrc(\'aimg_' + aimgcount[pid][i] + '\'), 0, 0, ' + (parseInt(showexif) ? 1 : 0) + ')" title="点击放大">点击放大</a>' +
					'<img ' + (islazy ? 'file' : 'src') + '="forum.php?mod=image&aid=' + aimgcount[pid][i] + '&size=100x100&key=' + imagelistkey + '&atid=' + tid + '" width="100" height="100" /></div>';
			}
			if($('imagelistthumb_' + pid)) {
				$('imagelistthumb_' + pid).innerHTML = imagelist;
			}
		}
	}
}

function attachimggetsrc(img) {
	return $(img).getAttribute('zoomfile') ? $(img).getAttribute('zoomfile') : $(img).getAttribute('file');
}

function attachimglst(pid, op, islazy) {
	if(!op) {
		$('imagelist_' + pid).style.display = 'none';
		$('imagelistthumb_' + pid).style.display = '';
	} else {
		$('imagelistthumb_' + pid).style.display = 'none';
		$('imagelist_' + pid).style.display = '';
		if(islazy) {
			o = new lazyload();
			o.showImage();
		} else {
			attachimgshow(pid);
		}
	}
	doane();
}

function attachimginfo(obj, infoobj, show, event) {
	objinfo = fetchOffset(obj);
	if(show) {
		$(infoobj).style.left = objinfo['left'] + 'px';
		$(infoobj).style.top = obj.offsetHeight < 40 ? (objinfo['top'] + obj.offsetHeight) + 'px' : objinfo['top'] + 'px';
		$(infoobj).style.display = '';
	} else {
		if(BROWSER.ie) {
			$(infoobj).style.display = 'none';
			return;
		} else {
			var mousex = document.body.scrollLeft + event.clientX;
			var mousey = document.documentElement.scrollTop + event.clientY;
			if(mousex < objinfo['left'] || mousex > objinfo['left'] + objinfo['width'] || mousey < objinfo['top'] || mousey > objinfo['top'] + objinfo['height']) {
				$(infoobj).style.display = 'none';
			}
		}
	}
}

function signature(obj) {
	if(obj.style.maxHeightIE != '') {
		var height = (obj.scrollHeight > parseInt(obj.style.maxHeightIE)) ? obj.style.maxHeightIE : obj.scrollHeight + 'px';
		if(obj.innerHTML.indexOf('<IMG ') == -1) {
			obj.style.maxHeightIE = '';
		}
		return height;
	}
}

function tagshow(event) {
	var obj = BROWSER.ie ? event.srcElement : event.target;
	ajaxmenu(obj, 0, 1, 2);
}

function parsetag(pid) {
	if(!$('postmessage_'+pid) || $('postmessage_'+pid).innerHTML.match(/<script[^\>]*?>/i)) {
		return;
	}
	var havetag = false;
	var tagfindarray = new Array();
	var str = $('postmessage_'+pid).innerHTML.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3, $4) {
		for(i in tagarray) {
			if(tagarray[i] && $3.indexOf(tagarray[i]) != -1) {
				havetag = true;
				$3 = $3.replace(tagarray[i], '<h_ ' + i + '>');
				tmp = $3.replace(/&[a-z]*?<h_ \d+>[a-z]*?;/ig, '');
				if(tmp != $3) {
					$3 = tmp;
				} else {
					tagfindarray[i] = tagarray[i];
					tagarray[i] = '';
				}
			}
		}
		return $2 + $3;
		});
		if(havetag) {
		$('postmessage_'+pid).innerHTML = str.replace(/<h_ (\d+)>/ig, function($1, $2) {
			return '<span href=\"forum.php?mod=tag&name=' + tagencarray[$2] + '\" onclick=\"tagshow(event)\" class=\"t_tag\">' + tagfindarray[$2] + '</span>';
	    	});
	}
}

function setanswer(pid, from){
	if(confirm('您确认要把该回复选为“最佳答案”吗？')){
		if(BROWSER.ie) {
			doane(event);
		}
		$('modactions').action='forum.php?mod=misc&action=bestanswer&tid=' + tid + '&pid=' + pid + '&from=' + from + '&bestanswersubmit=yes';
		$('modactions').submit();
	}
}

var authort;
function showauthor(ctrlObj, menuid) {
	authort = setTimeout(function () {
		showMenu({'menuid':menuid});
		if($(menuid + '_ma').innerHTML == '') $(menuid + '_ma').innerHTML = ctrlObj.innerHTML;
	}, 500);
	if(!ctrlObj.onmouseout) {
		ctrlObj.onmouseout = function() {
			clearTimeout(authort);
		}
	}
}

function fastpostappendreply() {
	if($('fastpostrefresh') != null) {
		setcookie('fastpostrefresh', $('fastpostrefresh').checked ? 1 : 0, 2592000);
		if($('fastpostrefresh').checked) {
			location.href = 'forum.php?mod=redirect&tid='+tid+'&goto=lastpost&random=' + Math.random() + '#lastpost';
			return;
		}
	}
	newpos = fetchOffset($('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	$('post_new').style.display = '';
	$('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	$('postlistreply').appendChild(div);
	$('fastpostsubmit').disabled = false;
	if($('fastpostmessage')) {
		$('fastpostmessage').value = '';
	} else {
		editdoc.body.innerHTML = BROWSER.firefox ? '<br />' : '';
	}
	if($('fastpostform').seccodehash){
		updateseccode($('fastpostform').seccodehash.value);
		$('fastpostform').seccodeverify.value = '';
	}
	if($('fastpostform').secqaahash){
		updatesecqaa($('fastpostform').secqaahash.value);
		$('fastpostform').secanswer.value = '';
	}
	showCreditPrompt();
}

function succeedhandle_fastpost(locationhref, message, param) {
	var pid = param['pid'];
	var tid = param['tid'];
	var from = param['from'];
	if(pid) {
		ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + pid + '&from=' + from, 'post_new', 'ajaxwaitid', '', null, 'fastpostappendreply()');
		if(replyreload) {
			var reloadpids = replyreload.split(',');
			for(var i = 1;i < reloadpids.length;i++) {
				ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + reloadpids[i] + '&from=' + from, 'post_' + reloadpids[i], 'ajaxwaitid');
			}
		}
		$('fastpostreturn').className = '';
	} else {
		if(!message) {
			message = '本版回帖需要审核，您的帖子将在通过审核后显示';
		}
		$('post_new').style.display = $('fastpostmessage').value = $('fastpostreturn').className = '';
		$('fastpostreturn').innerHTML = message;
	}
	if(param['sechash']) {
		updatesecqaa(param['sechash']);
		updateseccode(param['sechash']);
	}
	if($('attach_tblheader')) {
		$('attach_tblheader').style.display = 'none';
	}
	if($('attachlist')) {
		$('attachlist').innerHTML = '';
	}
}

function errorhandle_fastpost() {
	$('fastpostsubmit').disabled = false;
}

function succeedhandle_comment(locationhref, message, param) {
	ajaxget('forum.php?mod=misc&action=commentmore&tid=' + param['tid'] + '&pid=' + param['pid'], 'comment_' + param['pid']);
	hideWindow('comment');
	showCreditPrompt();
}

function succeedhandle_postappend(locationhref, message, param) {
	ajaxget('forum.php?mod=viewthread&tid=' + param['tid'] + '&viewpid=' + param['pid'], 'post_' + param['pid'], 'ajaxwaitid');
	hideWindow('postappend');
}

function recommendupdate(n) {
	if(getcookie('recommend')) {
		var objv = n > 0 ? $('recommendv_add') : $('recommendv_subtract');
		objv.style.display = '';
		objv.innerHTML = parseInt(objv.innerHTML) + 1;
		setTimeout(function () {
			$('recommentc').innerHTML = parseInt($('recommentc').innerHTML) + n;
			$('recommentv').style.display = 'none';
		}, 1000);
		setcookie('recommend', '');
	}
}

function postreviewupdate(pid, n) {
	var objv = n > 0 ? $('review_support_'+pid) : $('review_against_'+pid);
	objv.innerHTML = parseInt(objv.innerHTML ? objv.innerHTML : 0) + 1;
}

function favoriteupdate() {
	var obj = $('favoritenumber');
	obj.style.display = '';
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function switchrecommendv() {
	display('recommendv');
	display('recommendav');
}

function appendreply() {
	newpos = fetchOffset($('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	$('post_new').style.display = '';
	$('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	$('postlistreply').appendChild(div);
	if($('postform')) {
		$('postform').replysubmit.disabled = false;
	}
	showCreditPrompt();
}

function poll_checkbox(obj) {
	if(obj.checked) {
		p++;
		for (var i = 0; i < $('poll').elements.length; i++) {
			var e = $('poll').elements[i];
			if(p == max_obj) {
				if(e.name.match('pollanswers') && !e.checked) {
					e.disabled = true;
				}
			}
		}
	} else {
		p--;
		for (var i = 0; i < $('poll').elements.length; i++) {
			var e = $('poll').elements[i];
			if(e.name.match('pollanswers') && e.disabled) {
				e.disabled = false;
			}
		}
	}
	$('pollsubmit').disabled = p <= max_obj && p > 0 ? false : true;
}

function itemdisable(i) {
	if($('itemt_' + i).className == 'z') {
		$('itemt_' + i).className = 'z xg1';
		$('itemc_' + i).value = '';
		itemset(i);
	} else {
		$('itemt_' + i).className = 'z';
		$('itemc_' + i).value = $('itemc_' + i).value > 0 ? $('itemc_' + i).value : 0;
	}
}
function itemop(i, v) {
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	$('item_' + i).style.backgroundPosition = '10px ' + h;
}
function itemclk(i, v) {
	$('itemc_' + i).value = v;
	$('itemt_' + i).className = 'z';
}
function itemset(i) {
	var v = $('itemc_' + i).value;
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	$('item_' + i).style.backgroundPosition = '10px ' + h;
}

function checkmgcmn(id) {
	if($('mgc_' + id) && !$('mgc_' + id + '_menu').getElementsByTagName('li').length) {
		$('mgc_' + id).innerHTML = '';
		$('mgc_' + id).style.display = 'none';
	}
}

function toggleRatelogCollapse(tarId, ctrlObj) {
	if($(tarId).className == 'rate') {
		$(tarId).className = 'rate rate_collapse';
		setcookie('ratecollapse', 1, 2592000);
		ctrlObj.innerHTML = '展开';
	} else {
		$(tarId).className = 'rate';
		setcookie('ratecollapse', 0, -2592000);
		ctrlObj.innerHTML = '收起';
	}
}

function copyThreadUrl(obj, bbname) {
	bbname = bbname || SITEURL;
	setCopy($('thread_subject').innerHTML.replace(/&amp;/g, '&') + '\n' + obj.href + '\n' + '(出处: '+bbname+')' + '\n', '帖子地址已经复制到剪贴板');
	return false;
}

function replyNotice() {
	var newurl = 'forum.php?mod=misc&action=replynotice&tid=' + tid + '&op=';
	var replynotice = $('replynotice');
	var status = replynotice.getAttribute("status");
	if(status == 1) {
		replynotice.href = newurl + 'receive';
		replynotice.innerHTML = '接收回复通知';
		replynotice.setAttribute("status", 0);
	} else {
		replynotice.href = newurl + 'ignore';
		replynotice.innerHTML = '取消回复通知';
		replynotice.setAttribute("status", 1);
	}
}

function lazyload(className) {
	var obj = this;
	lazyload.className = className;
	this.getOffset = function (el, isLeft) {
		var  retValue  = 0 ;
		while  (el != null ) {
			retValue  +=  el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return  retValue;
	};
	this.initImages = function (ele) {
		lazyload.imgs = [];
		var eles = lazyload.className ? $C(lazyload.className, ele) : [document.body];
		for (var i = 0; i < eles.length; i++) {
			var imgs = eles[i].getElementsByTagName('IMG');
			for(var j = 0; j < imgs.length; j++) {
				if(imgs[j].getAttribute('file') && !imgs[j].getAttribute('lazyloaded')) {
					if(this.getOffset(imgs[j]) > document.documentElement.clientHeight) {
						lazyload.imgs.push(imgs[j]);
					} else {
						imgs[j].onload = function(){thumbImg(this);};
						imgs[j].setAttribute('src', imgs[j].getAttribute('file'));
						imgs[j].setAttribute('lazyloaded', 'true');
					}
				}
			}
		}
	};
	this.showImage = function() {
		this.initImages();
		if(!lazyload.imgs.length) return false;
		var imgs = [];
		var scrollTop = Math.max(document.documentElement.scrollTop , document.body.scrollTop);
		for (var i=0; i<lazyload.imgs.length; i++) {
			var img = lazyload.imgs[i];
			var offsetTop = this.getOffset(img);
			if (!img.getAttribute('lazyloaded') && offsetTop > document.documentElement.clientHeight && (offsetTop  - scrollTop < document.documentElement.clientHeight)) {
				var dom = document.createElement('div');
				var width = img.getAttribute('width') ? img.getAttribute('width') : 100;
				var height = img.getAttribute('height') ? img.getAttribute('height') : 100;
				dom.innerHTML = '<div style="width: '+width+'px; height: '+height+'px;background: url('+IMGDIR + '/loading.gif) no-repeat center center;"></div>';
				img.parentNode.insertBefore(dom.childNodes[0], img);
				img.onload = function () {
					if(!this.getAttribute('_load')) {
						this.setAttribute('_load', 1);
						this.style.width = this.style.height = '';
						this.parentNode.removeChild(this.previousSibling);
						if(this.getAttribute('lazyloadthumb')) {
							thumbImg(this);
						}
					}
				};
				img.style.width = img.style.height = '1px';
				img.setAttribute('src', img.getAttribute('file') ? img.getAttribute('file') : img.getAttribute('src'));
				img.setAttribute('lazyloaded', true);
			} else {
				imgs.push(img);
			}
		}
		lazyload.imgs = imgs;
		return true;
	};
	this.showImage();
	_attachEvent(window, 'scroll', function(){obj.showImage();});
}
function update_collection(){
	var obj = $('collectionnumber');
	sum = 1;
	obj.style.display = '';
	obj.innerText = parseInt(obj.innerText)+sum;
}
function display_blocked_post() {
	var movehiddendiv = (!$('hiddenposts').innerHTML) ? true : false;
	for (var i = 0; i < blockedPIDs.length; i++) {
		if(movehiddendiv) {
			$('hiddenposts').appendChild($("post_"+blockedPIDs[i]));
		}
		display("post_"+blockedPIDs[i]);
	}
	var postlistreply = $('postlistreply').innerHTML;
	$('hiddenpoststip').parentNode.removeChild($('postlistreply'));
	$('hiddenpoststip').parentNode.removeChild($('hiddenpoststip'));
	$('hiddenposts').innerHTML+='<div id="postlistreply" class="pl">'+postlistreply+'</div>';
}

function show_threadpage(pid, current, maxpage, ispreview) {
	if(!$('threadpage') || typeof tid == 'undefined') {
		return;
	};
	var clickvalue = function (page) {
		return 'ajaxget(\'forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + pid + '&cp=' + page + (ispreview ? '&from=preview' : '') + '\', \'post_' + pid + '\', \'ajaxwaitid\');';
	};
	var pstart = current - 1;
	pstart = pstart < 1 ? 1 : pstart;
	var pend = current + 1;
	pend = pend > maxpage ? maxpage : pend;
	var s = '<div class="cm pgs mtm mbm cl"><div class="pg">';
	if(pstart > 1) {
		s += '<a href="javascript:;" onclick="' + clickvalue(1) + '">1 ...</a>';
	}
	for(i = pstart;i <= pend;i++) {
		s += i == current ? '<strong>' + i + '</strong>' : '<a href="javascript:;" onclick="' + clickvalue(i)+ '">' + i + '</a>';
	}
	if(pend < maxpage) {
		s += '<a href="javascript:;" onclick="' + clickvalue(maxpage)+ '">... ' + maxpage + '</a>';
	}
	if(current < maxpage) {
		s += '<a href="javascript:;" onclick="' + clickvalue(current + 1) + '" class="nxt">下一页</a>';
	}
	s += '<a href="javascript:;" onclick="' + clickvalue('all') + '">查看所有</a>';
	s += '</div></div>';
	$('threadpage').innerHTML = s;
}

var show_threadindex_data = '';
function show_threadindex(pid, ispreview) {
	if(!show_threadindex_data) {
		var s = '<div class="tindex"><h3>目录</h3><ul>';
		for(i in $('threadindex').childNodes) {
			o = $('threadindex').childNodes[i];
			if(o.tagName == 'A') {
				var sub = o.getAttribute('sub').length * 2;
				o.href = "javascript:;";
				if(o.getAttribute('page')) {
					s += '<li style="margin-left:' + sub + 'em" onclick="ajaxget(\'forum.php?mod=viewthread&threadindex=yes&tid=' + tid + '&viewpid=' + pid + '&cp=' + o.getAttribute('page') + (ispreview ? '&from=preview' : '') + '\', \'post_' + pid + '\', \'ajaxwaitid\')">' + o.innerHTML + '</li>';
				} else if(o.getAttribute('tid') && o.getAttribute('pid')) {
					s += '<li style="margin-left:' + sub + 'em" onclick="ajaxget(\'forum.php?mod=viewthread&threadindex=yes&tid=' + o.getAttribute('tid') + '&viewpid=' + o.getAttribute('pid') + (ispreview ? '&from=preview' : '') + '\', \'post_' + pid + '\', \'ajaxwaitid\')">' + o.innerHTML + '</li>';
				}
			}
		}
		s += '</ul></div>';
		$('threadindex').innerHTML = s;
		show_threadindex_data = s;
	} else {
		$('threadindex').innerHTML = show_threadindex_data;
	}
}
function ctrlLeftInfo(sli_staticnum) {
	var sli = $('scrollleftinfo');
	var postlist_bottom = parseInt($('postlist').getBoundingClientRect().bottom);
	var sli_bottom = parseInt(sli.getBoundingClientRect().bottom);
	if(postlist_bottom < sli_staticnum && postlist_bottom != sli_bottom) {
		sli.style.top = (postlist_bottom - sli.offsetHeight - 5)+'px';
	} else{
		sli.style.top = 'auto';
	}
}

function fixed_avatar(pids, fixednv) {
	var fixedtopnv = fixednv ? new fixed_top_nv('nv', true) : false;
	if(fixednv) {
		fixedtopnv.init();
	}
	function fixedavatar(e) {
		var avatartop = fixednv ? fixedtopnv.run() : 0;
		for(var i = 0; i < pids.length; i++) {
			var pid = pids[i];
			var posttable = $('pid'+pid);
			var postavatar = $('favatar'+pid);
			if(!$('favatar'+pid)) {
				return;
			}
			var nextpost = $('_postposition'+pid);
			if(!postavatar || !nextpost || posttable.offsetHeight - 100 < postavatar.offsetHeight) {
				if(postavatar.style.position == 'fixed') {
					postavatar.style.position = '';
				}
				continue;
			}
			var avatarstyle = postavatar.style;
			posttabletop = parseInt(posttable.getBoundingClientRect().top);
			nextposttop = parseInt(nextpost.getBoundingClientRect().top);
			if(nextposttop > avatartop && nextposttop <= postavatar.offsetHeight + avatartop) {
				if(avatarstyle.position != 'absolute') {
					postavatar.parentNode.style.position = 'relative';
					avatarstyle.top = '';
					avatarstyle.bottom = '0px';
					avatarstyle.position = 'absolute';
				}
			} else if(posttabletop < avatartop && nextposttop > avatartop) {
					if(postavatar.parentNode.style.position != '') {
						postavatar.parentNode.style.position = '';
					}
					if(avatarstyle.position != 'fixed' || parseInt(avatarstyle.top) != avatartop) {
						avatarstyle.bottom = '';
						avatarstyle.top = avatartop + 'px';
						avatarstyle.position = 'fixed';
					}
			} else if(avatarstyle.position != '') {
				avatarstyle.position = '';
			}
		}
	}
	if(!(BROWSER.ie && BROWSER.ie < 7)) {
		_attachEvent(window, 'load', function(){_attachEvent(window, 'scroll', fixedavatar);});
	}
}

function submitpostpw(pid, tid) {
	var obj = $('postpw_' + pid);
	appendscript(JSPATH + 'md5.js?' + VERHASH);
	safescript('md5_js', function () {
		setcookie('postpw_' + pid, hex_md5(obj.value));
		if(!tid) {
			location.href = location.href;
		} else {
			location.href = 'forum.php?mod=viewthread&tid='+tid;
		}
	}, 100, 50);
}

function threadbegindisplay(type, w, h, s) {

	$('begincloseid').onclick = function() {
		$('threadbeginid').style.display = 'none';
	};
	var imgobj = $('threadbeginid');
	imgobj.style.left = (document.body.clientWidth - w)/2 + 'px';
	imgobj.style.top = (document.body.clientHeight - h)/2 + 'px';
	if(type == 1) {
		autozoom(w, h, s);
	} else if(type == 2) {
		autofade(w, h, s);
	} else {
		setTimeout(function() {
			$('threadbeginid').style.display = 'none';
		}, s);
	}
}

function autofade(w, h, s) {
	this.imgobj = $('threadbeginid');
	this.opacity = 0;
	this.fadein = function() {
		if(BROWSER.ie) {
			this.imgobj.filters.alpha.opacity = this.opacity;
		} else {
			this.imgobj.style.opacity = this.opacity/100;
		}
		if(this.opacity >= 100) {
			setTimeout(this.fadeout, s);
			return;
		}
		this.opacity++;
		setTimeout(this.fadein, 50);
	};
	this.fadeout = function() {
		if(BROWSER.ie) {
			this.imgobj.filters.alpha.opacity = this.opacity;
		} else {
			this.imgobj.style.opacity = this.opacity/100;
		}
		if(this.opacity <= 0) {
			this.imgobj.style.display = 'none';
			return;
		}
		this.opacity--;
		setTimeout(this.fadeout, 50);
	};
	this.fadein();
}

function autozoom(w, h, s) {
	this.height = 0;
	this.imgobj = $('threadbeginid');
	this.imgobj.style.overflow = 'hidden';
	this.imgobj.style.display = '';
	this.autozoomin = function() {
		this.height += 5;
		if(this.height >= h) {
			this.imgobj.style.height = h + 'px';
			setTimeout(this.autozoomout, s);
			return;
		}
		this.imgobj.style.height = this.height + 'px';
		setTimeout(this.autozoomin, 50);
	};
	this.autozoomout = function() {
		this.height -= 5;
		if(this.height <= 0) {
			this.imgobj.style.height = 0 + 'px';
			this.imgobj.style.display = 'none';
			return;
		}
		this.imgobj.style.height = this.height + 'px';
		setTimeout(this.autozoomout, 50);
	};
	this.autozoomin();
}

function readmode(title, pid) {

	var imagelist = '';
	if(aimgcount[pid]) {
		for(var i = 0; i < aimgcount[pid].length;i++) {
			var aimgObj = $('aimg_'+aimgcount[pid][i]);
			if(aimgObj.parentElement.className!="mbn") {
				var src = aimgObj.getAttribute('file');
				imagelist += '<div class="mbn"><img src="' + src + '" width="600" /></div>';
			}
		}
	}
	msg = $('postmessage_'+pid).innerHTML+imagelist;
	msg = '<div style="width:800px;max-height:500px; overflow-y:auto; padding: 10px;" class="pcb">'+msg+'</div>';
	showDialog(msg, 'info', title, null, 1);
	var coverObj = $('fwin_dialog_cover');
	coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=90)';
	coverObj.style.opacity = 0.9;
}

function changecontentdivid(tid) {
	if($('postlistreply')) {
		objtid = $('postlistreply').getAttribute('tid');
		if(objtid == tid) {
			return;
		}
		$('postlistreply').id = 'postlistreply_'+objtid;
		postnewdiv = $('postlistreply_'+objtid).childNodes;
		postnewdiv[postnewdiv.length-1].id = 'post_new_'+objtid;
	}
	$('postlistreply_'+tid).id = 'postlistreply';
	postnewdiv = $('postlistreply').childNodes;
	postnewdiv[postnewdiv.length-1].id = 'post_new';
}

function showmobilebbs(obj) {
	var content = '<h3 class="flb" style="cursor:move;"><em>下载掌上论坛</em><span><a href="javascript:;" class="flbc" onclick="hideWindow(\'mobilebbs\')" title="{lang close}">{lang close}</a></span></h3><div class="c"><h4>Andriod版本，扫描二维码可以直接下载到手机</h4><p class="mtm mbm vm"><span class="code_bg"><img src="'+ STATICURL +'image/common/zslt_andriod.png" alt="" /></span><img src="'+ STATICURL +'image/common/andriod.png" alt="适用于装有安卓系统的三星/HTC/小米等手机" /></p><h4>iPhone版本，扫描二维码可以直接下载到手机</h4><p class="mtm mbm vm"><span class="code_bg"><img src="'+ STATICURL +'image/common/zslt_ios.png" alt="" /></span><img src="'+ STATICURL +'image/common/ios.png" alt="适用于苹果手机" /></p></div>';
	showWindow('mobilebbs', content, 'html');
}

function succeedhandle_vfastpost(url, message, param) {
	$('vmessage').value = '';
	succeedhandle_fastpost(url, message, param);
	showCreditPrompt();
}

function vmessage() {
	var vf_tips = '#在这里快速回复#';
	$('vmessage').value = vf_tips;
	$('vmessage').style.color = '#CDCDCD';
	$('vmessage').onclick = function() {
		if($('vmessage').value==vf_tips) {
			$('vmessage').value='';
			$('vmessage').style.color="#000";
		}
	};
	$('vmessage').onblur = function() {
		if(!$('vmessage').value) {
			$('vmessage').value=vf_tips;
			$('vmessage').style.color="#CDCDCD";
		}
	};
	$('vreplysubmit').onclick = function() {
		if($('vmessage').value == vf_tips) {
			return false;
		}
	};
	$('vmessage').onfocus = function() {
		ajaxget('forum.php?mod=ajax&action=checkpostrule&ac=reply', 'vfastpostseccheck');
		$('vmessage').onfocus = null;
	};
}