/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum.js 33824 2013-08-19 08:26:11Z nemohou $
*/

function saveData(ignoreempty) {
	var ignoreempty = isUndefined(ignoreempty) ? 0 : ignoreempty;
	var obj = $('postform') && (($('fwin_newthread') && $('fwin_newthread').style.display == '') || ($('fwin_reply') && $('fwin_reply').style.display == '')) ? $('postform') : ($('fastpostform') ? $('fastpostform') : $('postform'));
	if(!obj) return;
	var bbcode = (typeof wysiwyg != 'undefined' && wysiwyg == 1) ? html2bbcode(editdoc.body.innerHTML) : obj.message.value;
	if(typeof isfirstpost != 'undefined') {
		if(typeof wysiwyg != 'undefined' && wysiwyg == 1) {
			var messageisnull = trim(bbcode) === '';
		} else {
			var messageisnull = bbcode === '';
		}
		if(isfirstpost && (messageisnull && $('postform').subject.value === '')) {
			return;
		}
		if(!isfirstpost && messageisnull) {
			return;
		}
	}
	var data = subject = message = '';
	for(var i = 0; i < obj.elements.length; i++) {
		var el = obj.elements[i];
		if(el.name != '' && (el.tagName == 'SELECT' || el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio' || el.type == 'hidden' || el.type == 'select')) && el.name.substr(0, 6) != 'attach') {
			var elvalue = el.value;
			if(el.name == 'subject') {
				subject = trim(elvalue);
			} else if(el.name == 'message') {
				if(typeof wysiwyg != 'undefined' && wysiwyg == 1) {
					elvalue = bbcode;
				}
				message = trim(elvalue);
			}
			if((el.type == 'checkbox' || el.type == 'radio') && !el.checked) {
				continue;
			} else if(el.tagName == 'SELECT') {
				elvalue = el.value;
			} else if(el.type == 'hidden') {
				if(el.id) {
					eval('var check = typeof ' + el.id + '_upload == \'function\'');
					if(check) {
						elvalue = elvalue;
						if($(el.id + '_url')) {
							elvalue += String.fromCharCode(1) + $(el.id + '_url').value;
						}
					} else {
						continue;
					}
				} else {
					continue;
				}
			}
			if(trim(elvalue)) {
				data += el.name + String.fromCharCode(9) + el.tagName + String.fromCharCode(9) + el.type + String.fromCharCode(9) + elvalue + String.fromCharCode(9, 9);
			}
		}
	}

	if(!subject && !message && !ignoreempty) {
		return;
	}

	saveUserdata('forum_'+discuz_uid, data);
}

function fastUload() {
	appendscript(JSPATH + 'forum_post.js?' + VERHASH);
	safescript('forum_post_js', function () { uploadWindow(function (aid, url) {updatefastpostattach(aid, url)}, 'file') }, 100, 50);
}

function switchAdvanceMode(url) {
	var obj = $('postform') && (($('fwin_newthread') && $('fwin_newthread').style.display == '') || ($('fwin_reply') && $('fwin_reply').style.display == '')) ? $('postform') : $('fastpostform');
	if(obj && obj.message.value != '') {
		saveData();
		url += (url.indexOf('?') != -1 ? '&' : '?') + 'cedit=yes';
	}
	location.href = url;
	return false;
}

function sidebar_collapse(lang) {
	if(lang[0]) {
		toggle_collapse('sidebar', null, null, lang);
		$('wrap').className = $('wrap').className == 'wrap with_side s_clear' ? 'wrap s_clear' : 'wrap with_side s_clear';
	} else {
		var collapsed = getcookie('collapse');
		collapsed = updatestring(collapsed, 'sidebar', 1);
		setcookie('collapse', collapsed, (collapsed ? 2592000 : -2592000));
		location.reload();
	}
}

function keyPageScroll(e, prev, next, url, page) {
	if(loadUserdata('is_blindman')) {
		return true;
	}
	e = e ? e : window.event;
	var tagname = BROWSER.ie ? e.srcElement.tagName : e.target.tagName;
	if(tagname == 'INPUT' || tagname == 'TEXTAREA') return;
	actualCode = e.keyCode ? e.keyCode : e.charCode;
	if(next && actualCode == 39) {
		window.location = url + '&page=' + (page + 1);
	}
	if(prev && actualCode == 37) {
		window.location = url + '&page=' + (page - 1);
	}
}

function announcement() {
    var ann = new Object();
    ann.anndelay = 3000;
    ann.annst = 0;
    ann.annstop = 0;
    ann.annrowcount = 0;
    ann.anncount = 0;
    ann.annScrollTopBegin = 0;
    ann.annlis = $('anc').getElementsByTagName("li");
    ann.annrows = new Array();
    ann.announcementScroll = function() {
        if (this.annstop) {
            this.annst = setTimeout(function() {
                ann.announcementScroll();
            }, this.anndelay);
            return;
        }
        if (!this.annst) {
            var lasttop = -1;
            for (i = 0; i < this.annlis.length; i++) {
                if (lasttop != this.annlis[i].offsetTop) {
                    this.annrows[this.annrowcount] = this.annlis[i].offsetTop - this.annlis[0].offsetTop;
                    this.annrowcount++;
                }
                lasttop = this.annlis[i].offsetTop;
            }
            if (this.annrows.length == 1) {
                $('an').onmouseover = $('an').onmouseout = null;
            } else {
                $('ancl').innerHTML += $('ancl').innerHTML;
                this.annst = setTimeout(function() {
                    ann.announcementScroll();
                }, this.anndelay);
                $('an').onmouseover = function() {
                    ann.annstop = 1;
                };
                $('an').onmouseout = function() {
                    ann.annstop = 0;
                };
            }
            this.annrowcount = 1;
            return;
        }
        if (this.annrowcount >= this.annrows.length) {
            $('anc').scrollTop = 0;
            this.annrowcount = 1;
            this.annst = setTimeout(function() {
                ann.announcementScroll();
            }, this.anndelay);
        } else {
            this.anncount = 0;
            this.annScrollTopBegin = $('anc').scrollTop;
            this.announcementScrollnext(this.annrows[this.annrowcount]);
        }
    }
    ;
    ann.announcementScrollnext = function(targetTop) {
        $('anc').scrollTop = this.annScrollTopBegin + this.anncount;
        this.anncount++;
        if ($('anc').scrollTop < targetTop) {
            this.annst = setTimeout(function() {
                ann.announcementScrollnext(targetTop);
            }, 10);
        } else {
            this.annrowcount++;
            this.annst = setTimeout(function() {
                ann.announcementScroll();
            }, this.anndelay);
        }
    }
    ;
    ann.announcementScroll();
}

function removeindexheats() {
	return confirm('您确认要把此主题从热点主题中移除么？');
}

function showTypes(id, mod) {
	var o = $(id);
	if(!o) return false;
	var s = o.className;
	mod = isUndefined(mod) ? 1 : mod;
	var baseh = o.getElementsByTagName('li')[0].offsetHeight * 2;
	var tmph = o.offsetHeight;
	var lang = ['展开', '收起'];
	var cls = ['unfold', 'fold'];
	if(tmph > baseh) {
		var octrl = document.createElement('li');
		octrl.className = cls[mod];
		octrl.innerHTML = lang[mod];

		o.insertBefore(octrl, o.firstChild);
		o.className = s + ' cttp';
		mod && (o.style.height = 'auto');

		octrl.onclick = function () {
			if(this.className == cls[0]) {
				o.style.height = 'auto';
				this.className = cls[1];
				this.innerHTML = lang[1];
			} else {
				o.style.height = '';
				this.className = cls[0];
				this.innerHTML = lang[0];
			}
		}
	}
}

var postpt = 0;
function fastpostvalidate(theform, noajaxpost) {
	if(postpt) {
		return false;
	}
	postpt = 1;
	setTimeout(function() {postpt = 0}, 2000);
	noajaxpost = !noajaxpost ? 0 : noajaxpost;
	s = '';
	if(typeof fastpostvalidateextra == 'function') {
		var v = fastpostvalidateextra();
		if(!v) {
			return false;
		}
	}
	if(theform.message.value == '' || theform.subject.value == '') {
		s = '抱歉，您尚未输入标题或内容';
		theform.message.focus();
	} else if(mb_strlen(theform.subject.value) > 80) {
		s = '您的标题超过 80 个字符的限制';
		theform.subject.focus();
	}
	if(!disablepostctrl && ((postminchars != 0 && mb_strlen(theform.message.value) < postminchars) || (postmaxchars != 0 && mb_strlen(theform.message.value) > postmaxchars))) {
		s = '您的帖子长度不符合要求。\n\n当前长度: ' + mb_strlen(theform.message.value) + ' ' + '字节\n系统限制: ' + postminchars + ' 到 ' + postmaxchars + ' 字节';
	}
	if(s) {
		showError(s);
		doane();
		$('fastpostsubmit').disabled = false;
		return false;
	}
	$('fastpostsubmit').disabled = true;
	theform.message.value = theform.message.value.replace(/([^>=\]"'\/]|^)((((https?|ftp):\/\/)|www\.)([\w\-]+\.)*[\w\-\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!]*)+\.(jpg|gif|png|bmp))/ig, '$1[img]$2[/img]');
	theform.message.value = parseurl(theform.message.value);
	if(!noajaxpost) {
		ajaxpost('fastpostform', 'fastpostreturn', 'fastpostreturn', 'onerror', $('fastpostsubmit'));
		return false;
	} else {
		return true;
	}
}

function checkpostrule(showid, extra) {
	var x = new Ajax();
	x.get('forum.php?mod=ajax&action=checkpostrule&inajax=yes&' + extra, function(s) {
		ajaxinnerhtml($(showid), s);evalscript(s);
	});
}

function updatefastpostattach(aid, url) {
	ajaxget('forum.php?mod=ajax&action=attachlist&posttime=' + $('posttime').value + (!fid ? '' : '&fid=' + fid), 'attachlist');
	$('attach_tblheader').style.display = '';
}

function succeedhandle_fastnewpost(locationhref, message, param) {
	location.href = locationhref;
}

function errorhandle_fastnewpost() {
	$('fastpostsubmit').disabled = false;
}

function atarget(obj) {
	obj.target = getcookie('atarget') > 0 ? '_blank' : '';
}

function setatarget(v) {
	$('atarget').className = 'y atarget_' + v;
	$('atarget').onclick = function() {setatarget(v == 1 ? -1 : 1);};
	setcookie('atarget', v, 2592000);
}

function loadData(quiet, formobj) {

	var evalevent = function (obj) {
		var script = obj.parentNode.innerHTML;
		var re = /onclick="(.+?)["|>]/ig;
		var matches = re.exec(script);
		if(matches != null) {
			matches[1] = matches[1].replace(/this\./ig, 'obj.');
			eval(matches[1]);
		}
	};

	var data = '';
	data = loadUserdata('forum_'+discuz_uid);
	var formobj = !formobj ? $('postform') : formobj;

	if(in_array((data = trim(data)), ['', 'null', 'false', null, false])) {
		if(!quiet) {
			showDialog('没有可以恢复的数据！', 'notice');
		}
		return;
	}

	if(!quiet && !confirm('此操作将覆盖当前帖子内容，确定要恢复数据吗？')) {
		return;
	}

	var data = data.split(/\x09\x09/);
	for(var i = 0; i < formobj.elements.length; i++) {
		var el = formobj.elements[i];
		if(el.name != '' && (el.tagName == 'SELECT' || el.tagName == 'TEXTAREA' || el.tagName == 'INPUT' && (el.type == 'text' || el.type == 'checkbox' || el.type == 'radio' || el.type == 'hidden'))) {
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
						} else if(ele[2] == 'hidden') {
							eval('var check = typeof ' + el.id + '_upload == \'function\'');
							if(check) {
								var v = elvalue.split(/\x01/);
								el.value = v[0];
								if(el.value) {
									if($(el.id + '_url') && v[1]) {
										$(el.id + '_url').value = v[1];
									}
									eval(el.id + '_upload(\'' + v[0] + '\', \'' + v[1] + '\')');
									if($('unused' + v[0])) {
										var attachtype = $('unused' + v[0]).parentNode.parentNode.parentNode.parentNode.id.substr(11);
										$('unused' + v[0]).parentNode.parentNode.outerHTML = '';
										$('unusednum_' + attachtype).innerHTML = parseInt($('unusednum_' + attachtype).innerHTML) - 1;
										if($('unusednum_' + attachtype).innerHTML == 0 && $('attachnotice_' + attachtype)) {
											$('attachnotice_' + attachtype).style.display = 'none';
										}
									}
								}
							}

						}
					} else if(ele[1] == 'TEXTAREA') {
						if(ele[0] == 'message') {
							if(!wysiwyg) {
								textobj.value = elvalue;
							} else {
								editdoc.body.innerHTML = bbcode2html(elvalue);
							}
						} else {
							el.value = elvalue;
						}
					} else if(ele[1] == 'SELECT') {
						if($(el.id + '_ctrl_menu')) {
							var lis = $(el.id + '_ctrl_menu').getElementsByTagName('li');
							for(var k = 0; k < lis.length; k++) {
								if(ele[3] == lis[k].k_value) {
									lis[k].onclick();
									break;
								}
							}
						} else {
							for(var k = 0; k < el.options.length; k++) {
								if(ele[3] == el.options[k].value) {
									el.options[k].selected = true;
									break;
								}
							}
						}
					}
					break;
				}
			}
		}
	}
	if($('rstnotice')) {
		$('rstnotice').style.display = 'none';
	}
	extraCheckall();
}

var checkForumcount = 0, checkForumtimeout = 30000, checkForumnew_handle;
function checkForumnew(fid, lasttime) {
	var timeout = checkForumtimeout;
	var x = new Ajax();
	x.get('forum.php?mod=ajax&action=forumchecknew&fid=' + fid + '&time=' + lasttime + '&inajax=yes', function(s){
		if(s > 0) {
			var table = $('separatorline').parentNode;
			if(!isUndefined(checkForumnew_handle)) {
				clearTimeout(checkForumnew_handle);
			}
			removetbodyrow(table, 'forumnewshow');
			var colspan = table.getElementsByTagName('tbody')[0].rows[0].children.length;
			var checknew = {'tid':'', 'thread':{'common':{'className':'', 'val':'<a href="javascript:void(0);" onclick="ajaxget(\'forum.php?mod=ajax&action=forumchecknew&fid=' + fid+ '&time='+lasttime+'&uncheck=1&inajax=yes\', \'forumnew\');">有新回复的主题，点击查看', 'colspan': colspan }}};
			addtbodyrow(table, ['tbody'], ['forumnewshow'], 'separatorline', checknew);
		} else {
			if(checkForumcount < 50) {
				if(checkForumcount > 0) {
					var multiple =  Math.ceil(50 / checkForumcount);
					if(multiple < 5) {
						timeout = checkForumtimeout * (5 - multiple + 1);
					}
				}
				checkForumnew_handle = setTimeout(function () {checkForumnew(fid, lasttime);}, timeout);
			}
		}
		checkForumcount++;
	});

}
function checkForumnew_btn(fid) {
	if(isUndefined(fid)) return;
	ajaxget('forum.php?mod=ajax&action=forumchecknew&fid=' + fid+ '&time='+lasttime+'&uncheck=2&inajax=yes', 'forumnew', 'ajaxwaitid');
	lasttime = parseInt(Date.parse(new Date()) / 1000);
}

function display_blocked_thread() {
	var table = $('threadlisttableid');
	if(!table) {
		return;
	}
	var tbodys = table.getElementsByTagName('tbody');
	for(i = 0;i < tbodys.length;i++) {
		var tbody = tbodys[i];
		if(tbody.style.display == 'none') {
			table.appendChild(tbody);
			tbody.style.display = '';
		}
	}
	$('hiddenthread').style.display = 'none';
}

function addtbodyrow(table, insertID, changename, separatorid, jsonval) {
	if(isUndefined(table) || isUndefined(insertID[0])) {
		return;
	}

	var insertobj = document.createElement(insertID[0]);
	var thread = jsonval.thread;
	var tid = !isUndefined(jsonval.tid) ? jsonval.tid : '' ;

	if(!isUndefined(changename[1])) {
		removetbodyrow(table, changename[1] + tid);
	}

	insertobj.id = changename[0] + tid;
	if(!isUndefined(insertID[1])) {
		insertobj.className = insertID[1];
	}
	if($(separatorid)) {
		table.insertBefore(insertobj, $(separatorid).nextSibling);
	} else {
		table.insertBefore(insertobj, table.firstChild);
	}
	var newTH = insertobj.insertRow(-1);
	for(var value in thread) {
		if(value != 0) {
			var cell = newTH.insertCell(-1);
			if(isUndefined(thread[value]['val'])) {
				cell.innerHTML = thread[value];
			} else {
				cell.innerHTML = thread[value]['val'];
			}
			if(!isUndefined(thread[value]['className'])) {
				cell.className = thread[value]['className'];
			}
			if(!isUndefined(thread[value]['colspan'])) {
				cell.colSpan = thread[value]['colspan'];
			}
		}
	}

	if(!isUndefined(insertID[2])) {
		_attachEvent(insertobj, insertID[2], function() {insertobj.className = '';});
	}
}
function removetbodyrow(from, objid) {
	if(!isUndefined(from) && $(objid)) {
		from.removeChild($(objid));
	}
}

function leftside(id) {
	$(id).className = $(id).className == 'a' ? '' : 'a';
	if(id == 'lf_fav') {
		setcookie('leftsidefav', $(id).className == 'a' ? 0 : 1, 2592000);
	}
}
var DTimers = new Array();
var DItemIDs = new Array();
var DTimers_exists = false;
function settimer(timer, itemid) {
	if(timer && itemid) {
		DTimers.push(timer);
		DItemIDs.push(itemid);
	}
	if(!DTimers_exists) {
		setTimeout("showtime()", 1000);
		DTimers_exists = true;
	}
}
function showtime() {
	for(i=0; i<=DTimers.length; i++) {
		if(DItemIDs[i]) {
			if(DTimers[i] == 0) {
				$(DItemIDs[i]).innerHTML = '已结束';
				DItemIDs[i] = '';
				continue;
			}
			var timestr = '';
			var timer_day = Math.floor(DTimers[i] / 86400);
			var timer_hour = Math.floor((DTimers[i] % 86400) / 3600);
			var timer_minute = Math.floor(((DTimers[i] % 86400) % 3600) / 60);
			var timer_second = (((DTimers[i] % 86400) % 3600) % 60);
			if(timer_day > 0) {
				timestr += timer_day + '天';
			}
			if(timer_hour > 0) {
				timestr += timer_hour + '小时'
			}
			if(timer_minute > 0) {
				timestr += timer_minute + '分'
			}
			if(timer_second > 0) {
				timestr += timer_second + '秒'
			}
			DTimers[i] = DTimers[i] - 1;
			$(DItemIDs[i]).innerHTML = timestr;
		}
	}
	setTimeout("showtime()", 1000);
}
function fixed_top_nv(eleid, disbind) {
	this.nv = eleid && $(eleid) || $('nv');
	this.openflag = this.nv && BROWSER.ie != 6;
	this.nvdata = {};
	this.init = function (disattachevent) {
		if(this.openflag) {
			if(!disattachevent) {
				var obj = this;
				_attachEvent(window, 'resize', function(){obj.reset();obj.init(1);obj.run();});
				var switchwidth = $('switchwidth');
				if(switchwidth) {
					_attachEvent(switchwidth, 'click', function(){obj.reset();obj.openflag=false;});
				}
			}

			var next = this.nv;
			try {
				if(this.nv.parentNode.id.substr(-3) != '_ph') {
					var nvparent = document.createElement('div');
					nvparent.id = this.nv.id + '_ph';
					this.nv.parentNode.insertBefore(nvparent,this.nv);
					nvparent.appendChild(this.nv);
				}
				this.nvdata.next = this.nv.parentNode;
				this.nvdata.height = parseInt(this.nv.offsetHeight, 10);
				this.nvdata.width = parseInt(this.nv.offsetWidth, 10);
				this.nvdata.left = this.nv.getBoundingClientRect().left - document.documentElement.clientLeft;
				this.nvdata.position = this.nv.style.position;
				this.nvdata.opacity = this.nv.style.opacity;
			} catch (e) {
				this.nvdata.next = null;
			}
		}
	};

	this.run = function () {
		var fixedheight = 0;
		if(this.openflag && this.nvdata.next){
			var nvnexttop = document.body.scrollTop || document.documentElement.scrollTop;
			var dofixed = nvnexttop !== 0 && document.documentElement.clientHeight >= 15 && this.nvdata.next.getBoundingClientRect().top < 0;
			if(dofixed) {
				if(this.nv.style.position != 'fixed') {
					this.nv.style.borderLeftWidth = '0';
					this.nv.style.borderRightWidth = '0';
					this.nv.style.height = this.nvdata.height + 'px';
					this.nv.style.width = this.nvdata.width + 'px';
					this.nv.style.top = '0';
					this.nv.style.left = this.nvdata.left + 'px';
					this.nv.style.position = 'fixed';
					this.nv.style.zIndex = '199';
					this.nv.style.opacity = 0.85;
					this.nv.parentNode.style.height = this.nvdata.height + 'px';
				}
			} else {
				if(this.nv.style.position != this.nvdata.position) {
					this.reset();
				}
			}
			if(this.nv.style.position == 'fixed') {
				fixedheight = this.nvdata.height;
			}
		}
		return fixedheight;
	};
	this.reset = function () {
		if(this.nv) {
			this.nv.style.position = this.nvdata.position;
			this.nv.style.borderLeftWidth = '';
			this.nv.style.borderRightWidth = '';
			this.nv.style.height = '';
			this.nv.style.width = '';
			this.nv.style.opacity = this.nvdata.opacity;
			this.nv.parentNode.style.height = '';
		}
	};
	if(!disbind && this.openflag) {
		this.init();
		_attachEvent(window, 'scroll', this.run);
	}
}
var previewTbody = null, previewTid = null, previewDiv = null;
function previewThread(tid, tbody) {
	if(!$('threadPreviewTR_'+tid)) {
		appendscript(JSPATH + 'forum_viewthread.js?' + VERHASH);

		newTr = document.createElement('tr');
		newTr.id = 'threadPreviewTR_'+tid;
		newTr.className = 'threadpre';
		$(tbody).appendChild(newTr);
		newTd = document.createElement('td');
		newTd.colSpan = listcolspan;
		newTd.className = 'threadpretd';
		newTr.appendChild(newTd);
		newTr.style.display = 'none';

		previewTbody = tbody;
		previewTid = tid;

		if(BROWSER.ie) {
			previewDiv = document.createElement('div');
			previewDiv.id = 'threadPreview_'+tid;
			previewDiv.style.id = 'none';
			var x = Ajax();
			x.get('forum.php?mod=viewthread&tid='+tid+'&inajax=1&from=preview', function(ret) {
				var evaled = false;
				if(ret.indexOf('ajaxerror') != -1) {
					evalscript(ret);
					evaled = true;
				}
				previewDiv.innerHTML = ret;
				newTd.appendChild(previewDiv);
				if(!evaled) evalscript(ret);
				newTr.style.display = '';
			});
		} else {
			newTd.innerHTML += '<div id="threadPreview_'+tid+'"></div>';
			ajaxget('forum.php?mod=viewthread&tid='+tid+'&from=preview', 'threadPreview_'+tid, null, null, null, function() {newTr.style.display = '';});
		}
	} else {
		$(tbody).removeChild($('threadPreviewTR_'+tid));
		previewTbody = previewTid = null;
	}
}

function hideStickThread(tid) {
	var pre = 'stickthread_';
	var tids = (new Function("return ("+(loadUserdata('sticktids') || '[]')+")"))();
	var format = function (data) {
		var str = '{';
		for (var i in data) {
			if(data[i] instanceof Array) {
				str += i + ':' + '[';
				for (var j = data[i].length - 1; j >= 0; j--) {
					str += data[i][j] + ',';
				};
				str = str.substr(0, str.length -1);
				str += '],';
			}
		}
		str = str.substr(0, str.length -1);
		str += '}';
		return str;
	};
	if(!tid) {
		if(tids.length > 0) {
			for (var i = tids.length - 1; i >= 0; i--) {
				var ele = $(pre+tids[i]);
				if(ele) {
					ele.parentNode.removeChild(ele);
				}
			};
		}
	} else {
		var eletbody = $(pre+tid);
		if(eletbody) {
			eletbody.parentNode.removeChild(eletbody);
			tids.push(tid);
			saveUserdata('sticktids', '['+tids.join(',')+']');
		}
	}
	var clearstickthread = $('clearstickthread');
	if(clearstickthread) {
		if(tids.length > 0) {
			$('clearstickthread').style.display = '';
		} else {
			$('clearstickthread').style.display = 'none';
		}
	}
	var separatorline = $('separatorline');
	if(separatorline) {
		try {
			if(typeof separatorline.previousElementSibling === 'undefined') {
				var findele = separatorline.previousSibling;
				while(findele && findele.nodeType != 1){
					findele = findele.previousSibling;
				}
				if(findele === null) {
					separatorline.parentNode.removeChild(separatorline);
				}
			} else {
				if(separatorline.previousElementSibling === null) {
					separatorline.parentNode.removeChild(separatorline);
				}
			}
		} catch(e) {
		}
	}
}
function viewhot() {
	var obj = $('hottime');
	window.location.href = "forum.php?mod=forumdisplay&filter=hot&fid="+obj.getAttribute('fid')+"&time="+obj.value;
}
function clearStickThread () {
	saveUserdata('sticktids', '[]');
	location.reload();
}