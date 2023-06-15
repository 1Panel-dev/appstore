/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_moderate.js 26484 2011-12-14 02:08:03Z svn_project_zhangjie $
*/

function modaction(action, pid, extra, mod) {
	if(!action) {
		return;
	}
	var mod = mod ? mod : 'forum.php?mod=topicadmin';
	var extra = !extra ? '' : '&' + extra;
	if(!pid && in_array(action, ['delpost', 'banpost'])) {
		var checked = 0;
		var pid = '';
		for(var i = 0; i < $('modactions').elements.length; i++) {
			if($('modactions').elements[i].name.match('topiclist')) {
				checked = 1;
				break;
			}
		}
	} else {
		var checked = 1;
	}
	if(!checked) {
		alert('请选择需要操作的帖子');
	} else {
		$('modactions').action = mod + '&action='+ action +'&fid=' + fid + '&tid=' + tid + '&handlekey=mods&infloat=yes&nopost=yes' + (!pid ? '' : '&topiclist[]=' + pid) + extra + '&r' + Math.random();
		showWindow('mods', 'modactions', 'post');
		if(BROWSER.ie) {
			doane(event);
		}
		hideMenu();
	}
}

function modthreads(optgroup, operation) {
	var operation = !operation ? '' : operation;
	$('modactions').action = 'forum.php?mod=topicadmin&action=moderate&fid=' + fid + '&moderate[]=' + tid + '&handlekey=mods&infloat=yes&nopost=yes' + (optgroup != 3 && optgroup != 2 ? '&from=' + tid : '');
	$('modactions').optgroup.value = optgroup;
	$('modactions').operation.value = operation;
	hideWindow('mods');
	showWindow('mods', 'modactions', 'post', 0);
	if(BROWSER.ie) {
		doane(event);
	}
}

function pidchecked(obj) {
	if(obj.checked) {
		try {
			var inp = document.createElement('<input name="topiclist[]" />');
		} catch(e) {
			try {
				var inp = document.createElement('input');
				inp.name = 'topiclist[]';
			} catch(e) {
				return;
			}
		}
		inp.id = 'topiclist_' + obj.value;
		inp.value = obj.value;
		inp.type = 'hidden';
		$('modactions').appendChild(inp);
	} else {
		$('modactions').removeChild($('topiclist_' + obj.value));
	}
}

var modclickcount = 0;
function modclick(obj, pid) {
	if(obj.checked) {
		modclickcount++;
	} else {
		modclickcount--;
	}
	$('mdct').innerHTML = modclickcount;
	if(modclickcount > 0) {
		var offset = fetchOffset(obj);
		$('mdly').style.top = offset['top'] - 65 + 'px';
		$('mdly').style.left = offset['left'] - 215 + 'px';
		$('mdly').style.display = '';
	} else {
		$('mdly').style.display = 'none';
	}
}

function resetmodcount() {
	modclickcount = 0;
	$('mdly').style.display = 'none';
}

function tmodclick(obj) {
	if(obj.checked) {
		modclickcount++;
	} else {
		modclickcount--;
	}
	$('mdct').innerHTML = modclickcount;
	if(modclickcount > 0) {
		var top_offset = obj.offsetTop;
		while((obj = obj.offsetParent).id != 'threadlist') {
			top_offset += obj.offsetTop;
		}
		$('mdly').style.top = top_offset - 7 + 'px';
		$('mdly').style.display = '';
	} else {
		$('mdly').style.display = 'none';
	}
}

function tmodthreads(optgroup, operation) {
	var checked = 0;
	var operation = !operation ? '' : operation;
	for(var i = 0; i < $('moderate').elements.length; i++) {
		if($('moderate').elements[i].name.match('moderate') && $('moderate').elements[i].checked) {
			checked = 1;
			break;
		}
	}
	if(!checked) {
		alert('请选择需要操作的帖子');
	} else {
		$('moderate').optgroup.value = optgroup;
		$('moderate').operation.value = operation;
		hideWindow('mods');
		showWindow('mods', 'moderate', 'post', 0);
	}
}

function getthreadclass() {
	var fid = $('fid');
	if(fid) {
		ajaxget('forum.php?mod=ajax&action=getthreadclass&fid=' + fid.value, 'threadclass', null, null, null, showthreadclass);
	}
}

function showthreadclass() {
	try{
		$('append_parent').removeChild($('typeid_ctrl_menu'));
	}catch(e) {}
	simulateSelect('typeid');
}

loadcss('forum_moderator');