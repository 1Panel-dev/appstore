/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: portal.js 31313 2012-08-10 03:51:03Z zhangguosheng $
*/

function block_get_setting(classname, script, bid) {
	var x = new Ajax();
	x.get('portal.php?mod=portalcp&ac=block&op=setting&bid='+bid+'&classname='+classname+'&script='+script+'&inajax=1', function(s){
		ajaxinnerhtml($('tbody_setting'), s);
	});
}

function switch_blocktab(type) {
	if(type == 'setting') {
		$('blockformsetting').style.display = '';
		$('blockformdata').style.display = 'none';
		$('li_setting').className = 'a';
		$('li_data').className = '';
	} else {
		$('blockformsetting').style.display = 'none';
		$('blockformdata').style.display = '';
		$('li_setting').className = '';
		$('li_data').className = 'a';
	}
}

function showpicedit(pre) {
	pre = pre ? pre : 'pic';
	if($(pre+'way_remote').checked) {
		$(pre+'_remote').style.display = "block";
		$(pre+'_upload').style.display = "none";
	} else {
		$(pre+'_remote').style.display = "none";
		$(pre+'_upload').style.display = "block";
	}
}

function block_show_thumbsetting(classname, styleid, bid) {
	var x = new Ajax();
	x.get('portal.php?mod=portalcp&ac=block&op=thumbsetting&classname='+classname+'&styleid='+styleid+'&bid='+bid+'&inajax=1', function(s){
		ajaxinnerhtml($('tbody_thumbsetting'), s);
	});
}

function block_showstyle(stylename) {
	var el_span = $('span_'+stylename);
	var el_value = $('value_' + stylename);
	if (el_value.value == '1'){
		el_value.value = '0';
		el_span.className = "";
	} else {
		el_value.value = '1';
		el_span.className = "a";
	}
}

function block_pushitem(bid, itemid) {
	var id = $('push_id').value;
	var idtype = $('push_idtype').value;
	if(id && idtype) {
		var x = new Ajax();
		x.get('portal.php?mod=portalcp&ac=block&op=push&&bid='+bid+'&itemid='+itemid+'&idtype='+idtype+'&id='+id+'&inajax=1', function(s){
			ajaxinnerhtml($('tbody_pushcontent'), s);
			evalscript(s);
		});
	}
}

function block_delete_item(bid, itemid, itemtype, itemfrom, from) {
	var msg = itemtype==1 ? '您确定要删除该数据吗？' : '您确定要屏蔽该数据吗？';
	if(confirm(msg)) {
		var url = 'portal.php?mod=portalcp&ac=block&op=remove&bid='+bid+'&itemid='+itemid;
		if(itemfrom=='ajax') {
			var x = new Ajax();
			x.get(url+'&inajax=1', function(){
				if(succeedhandle_showblock) succeedhandle_showblock('', '', {'bid':bid});
				showWindow('showblock', 'portal.php?mod=portalcp&ac=block&op=data&bid='+bid+'&from='+from+'&tab=data&t='+(+ new Date()), 'get', 0);
			});
		} else {
			location.href = url;
		}
	}
	doane();
}

function portal_comment_requote(cid, aid) {
	var x = new Ajax();
	x.get('portal.php?mod=portalcp&ac=comment&op=requote&cid='+cid+'&aid='+aid+'&inajax=1', function(s){
		$('message').focus();
		ajaxinnerhtml($('message'), s);
	});
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

function searchblock(from) {
	var value = $('searchkey').value;
	var targettplname = $('targettplname').value;
	value = BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(value) : (value ? value.replace(/#/g,'%23') : '');
	var url = 'portal.php?mod=portalcp&ac=portalblock&searchkey='+value+'&from='+from;
	url += targettplname != '' ? '&targettplname='+targettplname+'&type=page' : '&type=block';
	reloadselection(url);
}

function reloadselection(url) {
	ajaxget(url+'&t='+(+ new Date()), 'block_selection');
}

function getColorPalette(colorid, id, background) {
	return "<input id=\"c"+colorid+"\" onclick=\"createPalette('"+colorid+"', '"+id+"');\" type=\"button\" class=\"pn colorwd\" value=\"\" style=\"background-color: "+background+"\">";
}

function listblock_bypage(id, idtype) {
	var tpl = $('rtargettplname') ? $('rtargettplname').value : '';
	var searchkey = $('rsearchkey') ? $('rsearchkey').value.replace('#', '%23') : '';
	ajaxget('portal.php?mod=portalcp&ac=portalblock&op=recommend&getdata=yes&searchkey='+searchkey+'&targettplname='+tpl+'&id='+id+'&idtype='+idtype, 'itemeditarea');
}

function recommenditem_check() {
	var sel = $('recommend_bid');
	if(sel && sel.value) {
		document.forms['recommendform'].action = document.forms['recommendform'].action+'&bid='+sel.value;
		return true;
	} else {
		alert("请选择一个模块！");
		return false;
	}
}

function recommenditem_byblock(bid, id, idtype) {
	var editarea = $('itemeditarea');
	if(editarea) {
		var olditemeditarea = $('olditemeditarea');
		ajaxinnerhtml(olditemeditarea, editarea.innerHTML);
		if(!$('recommendback')) {
			var back = document.createElement('div');
			back.innerHTML = '<em id="recommendback" onclick="recommenditem_back()" class="cur1">&nbsp;&nbsp;&laquo;返回</em>';
			var return_mods = $('return_mods') || $('return_recommend') || $('return_');
			if(return_mods) {
				return_mods.parentNode.appendChild(back.childNodes[0]);
			}
		}
		if(bid) {
			if($('recommend_bid')) {
				$('recommend_bid').value = bid;
			}
			ajaxget('portal.php?mod=portalcp&ac=block&op=recommend&bid='+bid+'&id='+id+'&idtype='+idtype+'&handlekey=recommenditem', 'itemeditarea');
		} else {
			ajaxinnerhtml(editarea, '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>');
		}
	}
}

function delete_recommenditem(dataid, bid) {
	if(dataid && bid) {
		var x = new Ajax();
		x.get('portal.php?mod=portalcp&ac=block&op=delrecommend&bid='+bid+'&dataid='+dataid+'&inajax=1', function(s){
			$('recommenditem_'+dataid).parentNode.removeChild($('recommenditem_'+dataid));
			if(!$('recommenditem_ul').getElementsByTagName('li').length) {
				$('hasinblocks').parentNode.removeChild($('hasinblocks'));
			}
		});
	}
}

function recommenditem_back(){
	var editarea = $('itemeditarea');
	var oldeditarea = $('olditemeditarea');
	var recommendback = $('recommendback');
	if(oldeditarea){
		ajaxinnerhtml(editarea, oldeditarea.innerHTML);
		ajaxupdateevents(editarea);
	}
	if(recommendback) {
		recommendback.parentNode.removeChild(recommendback);
	}
	if($('recommend_bid')) {
		$('recommend_bid').value = '';
	}
}
function blockBindTips() {
	var elems = ($('blockformsetting') || document).getElementsByTagName('img');
	var k = 0;
	var stamp = (+new Date());
	var tips = '';
	for(var i = 0; i < elems.length; i++) {
		tips = elems[i]['tips'] || elems[i].getAttribute('tips') || '';
		if(tips && ! elems[i].isBindTips) {
			elems[i].isBindTips = '1';
			elems[i].id = elems[i].id ? elems[i].id : ('elem_' + stamp + k.toString());
			k++;
			showPrompt(elems[i].id, 'mouseover', tips, 1, true);
		}
	}
}

function blockSetCacheTime(timer) {
	$('txt_cachetime').value=timer;
	doane();
}

function toggleSettingShow() {
	if(!$('tbody_setting').style.display) {
		$('tbody_setting').style.display = 'none';
		$('a_setting_show').innerHTML = '展开设置项';
	} else {
		$('tbody_setting').style.display = '';
		$('a_setting_show').innerHTML = '收起设置项';
	}
	doane();
}
function switchSetting() {
	var checked = $('isblank').checked;
	if(checked) {
		$('tbody_setting').style.display = 'none';
		$('a_setting_show').innerHTML = '展开设置项';
	} else {
		$('tbody_setting').style.display = '';
		$('a_setting_show').innerHTML = '收起设置项';
	}
}

function checkblockname(form) {
	if(!(trim(form.name.value) > '')) {
		showDialog('模块标识不能为空', 'error', null, function(){form.name.focus();});
		return false;
	}
	if(form.summary && form.summary.value) {
		var tag = blockCheckTag(form.summary.value, true);
		if(tag) {
			showBlockSummary();
			form.summary.focus();
			showDialog('自定义内容错误，HTML代码：'+tag+' 标签不匹配', 'error', null, function(){form.summary.select();});
			return false;
		}
	}

	return true;
}

function blockCheckTag(summary, returnValue) {
	var obj = null, fn = null;
	if(typeof summary == 'object') {
		obj = summary;
		summary = summary.value;
		fn = function(){obj.focus();obj.select();};
	}
	if(trim(summary) > '') {
		var tags = ['div', 'table', 'tbody', 'tr', 'td', 'th'];
		for(var i = 0; i < tags.length; i++) {
			var tag = tags[i];
			var reg = new RegExp('<'+tag+'', 'gi');
			var preTag = [];
			var one = [];
			while (one = reg.exec(summary)) {
				preTag.push(one[0]);
			}
			reg = new RegExp('</'+tag+'>', 'gi');
			var endTag = [];
			var one = [];
			while (one = reg.exec(summary)) {
				endTag.push(one[0]);
			}
            if(!preTag && !endTag) continue;
			if((!preTag && endTag) || (preTag && !endTag) || preTag.length != endTag.length) {
				if(returnValue) {
					return tag;
				} else {
					showDialog('HTML代码：'+tag+' 标签不匹配', 'error', null, fn, true, fn);
					return false;
				}
			}
		}
	}
	return false;
}

function showBlockSummary() {
	$('block_sumamry_content').style.display='';
	$('a_summary_show').style.display='none';
	$('a_summary_hide').style.display='';
	return false;
}

function hideBlockSummary() {
	$('block_sumamry_content').style.display='none';
	$('a_summary_hide').style.display='none';
	$('a_summary_show').style.display='';
	return false;
}

function blockconver(ele,bid) {
	if(ele && bid) {
		if(confirm('您确定要转换模块的类型从 '+ele.options[0].innerHTML+' 到 '+ele.options[ele.selectedIndex].innerHTML)) {
			ajaxget('portal.php?mod=portalcp&ac=block&op=convert&bid='+bid+'&toblockclass='+ele.value,'blockshow');
		} else {
			ele.selectedIndex = 0;
		}
	}
}

function blockFavorite(bid){
	if(bid) {
		ajaxget('portal.php?mod=portalcp&ac=block&op=favorite&bid='+bid,'bfav_'+bid);
	}
}

function strLenCalc(obj, checklen, maxlen) {
	var v = obj.value, charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = 0, len = strlen(v);
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen += 2;
		} else {
			curlen += 1;
		}
	}
	checklen = $(checklen);
	if(checklen.style.display == 'none') checklen.style.display = '';
	if(curlen <= maxlen) {
		checklen.innerHTML = '已输入 <b>'+(curlen)+'</b> 个字符';
		return true;
	} else {
		checklen.innerHTML = '超出 <b style="color:red">'+(curlen - maxlen)+'</b> 个字符';
		return false;
	}
}

function check_itemdata_lentgh(form) {
	if(form.title && (!strLenCalc(form.title, "titlechk", form.title.getAttribute('_maxlength')) || !form.title.value)) {
		form.title.focus();
		showDialog('标题长度不正确', 'error', null, function(){form.title.select();});
		return false;
	}
	if(form.summary && !strLenCalc(form.summary, "summarychk", form.summary.getAttribute('_maxlength'))) {
		form.summary.focus();
		showDialog('简介长度不正确', 'error', null, function(){form.summary.select();});
		return false;
	}
	return true;
}