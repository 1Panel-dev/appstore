/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: register.js 33433 2013-06-13 07:36:25Z nemohou $
*/

var lastusername = '', lastpassword = '', lastemail = '', lastinvitecode = '', stmp = new Array(), modifypwd = false, profileTips = '如不需要更改密码，此处请留空';

function errormessage(id, msg) {
	if($(id)) {
		try{
			showInputTip();
		} catch (e) {}
		msg = !msg ? '' : msg;
		if($('tip_' + id)) {
			if(msg == 'succeed') {
				msg = '';
				$('tip_' + id).parentNode.className = $('tip_' + id).parentNode.className.replace(/ p_right/, '');
				$('tip_' + id).parentNode.className += ' p_right';
			} else if(msg !== '') {
				$('tip_' + id).parentNode.className = $('tip_' + id).parentNode.className.replace(/ p_right/, '');
			}
		}
		if($('chk_' + id)) {
			$('chk_' + id).innerHTML = msg;
		}
		$(id).className = $(id).className.replace(/ er/, '');
		$(id).className += !msg ? '' : ' er';
	}
}

function addFormEvent(formid, focus){
	var si = 0;
	var formNode = $(formid).getElementsByTagName('input');
	for(i = 0;i < formNode.length;i++) {
		if(formNode[i].name == '') {
			formNode[i].name = formNode[i].id;
			stmp[si] = i;
			si++;
		}
		if(formNode[i].type == 'text' || formNode[i].type == 'password'){
			formNode[i].onfocus = function(){
				showInputTip(!this.id ? this.name : this.id);
			}
		}
	}
	if(!si) {
		return;
	}
	formNode[stmp[0]].onblur = function () {
		checkusername(formNode[stmp[0]].id);
	};
	checkPwdComplexity(formNode[stmp[1]], formNode[stmp[2]]);
	try {
		if(!ignoreEmail) {
			addMailEvent(formNode[stmp[3]]);
		}
	} catch(e) {}

	try {
		if(focus) {
			$('invitecode').focus();
		} else {
			formNode[stmp[0]].focus();
		}
	} catch(e) {}
}

function checkPwdComplexity(firstObj, secondObj, modify) {
	modifypwd = modify || false;
	firstObj.onblur = function () {
		if(firstObj.value == '') {
			var pwmsg = !modifypwd ? '请填写密码' : profileTips;
			if(pwlength > 0) {
				pwmsg += ', 最小长度为 '+pwlength+' 个字符';
			}
			errormessage(firstObj.id, pwmsg);
		}else{
			errormessage(firstObj.id, !modifypwd ? 'succeed' : '');
		}
		checkpassword(firstObj.id, secondObj.id);
	};
	firstObj.onkeyup = function () {
		if(pwlength == 0 || $(firstObj.id).value.length >= pwlength) {
			var passlevels = new Array('','弱','中','强');
			var passlevel = checkstrongpw(firstObj.id);
			errormessage(firstObj.id, '<span class="passlevel passlevel'+passlevel+'">密码强度:'+passlevels[passlevel]+'</span>');
		}
	};
	secondObj.onblur = function () {
		if(secondObj.value == '') {
			errormessage(secondObj.id, !modifypwd ? '请再次输入密码' : profileTips);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
}

function addMailEvent(mailObj) {

	mailObj.onclick = function (event) {
		emailMenu(event, mailObj.id);
	};
	mailObj.onkeyup = function (event) {
		emailMenu(event, mailObj.id);
	};
	mailObj.onkeydown = function (event) {
		emailMenuOp(4, event, mailObj.id);
	};
	mailObj.onblur = function () {
		if(mailObj.value == '') {
			errormessage(mailObj.id, '请输入邮箱地址');
		}
		emailMenuOp(3, null, mailObj.id);
	};
	stmp['email'] = mailObj.id;
}
function checkstrongpw(id) {
	var passlevel = 0;
	if($(id).value.match(/\d+/g)) {
		passlevel ++;
	}
	if($(id).value.match(/[a-z]+/ig)) {
		passlevel ++;
	}
	if($(id).value.match(/[^a-z0-9]+/ig)) {
		passlevel ++;
	}
	return passlevel;
}
function showInputTip(id) {
	var p_tips = $('registerform').getElementsByTagName('i');
	for(i = 0;i < p_tips.length;i++){
		if(p_tips[i].className == 'p_tip'){
			p_tips[i].style.display = 'none';
		}
	}
	if($('tip_' + id)) {
		$('tip_' + id).style.display = 'block';
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

function trim(str) {
	return str.replace(/^\s*(.*?)[\s\n]*$/g, '$1');
}

var emailMenuST = null, emailMenui = 0, emaildomains = ['qq.com', '163.com', 'sina.com', 'sohu.com', 'yahoo.com', 'gmail.com', 'hotmail.com'];
function emailMenuOp(op, e, id) {
	if(op == 3 && BROWSER.ie && BROWSER.ie < 7) {
		checkemail(id);
	}
	if(!$('emailmore_menu')) {
		return;
	}
	if(op == 1) {
		$('emailmore_menu').style.display = 'none';
	} else if(op == 2) {
		showMenu({'ctrlid':'emailmore','pos': '13!'});
	} else if(op == 3) {
		emailMenuST = setTimeout(function () {
			emailMenuOp(1, id);
			checkemail(id);
		}, 500);
	} else if(op == 4) {
	       	e = e ? e : window.event;
                var obj = $(id);
        	if(e.keyCode == 13 && obj.value.indexOf('@') == -1) {
                        obj.value = obj.value + '@' + emaildomains[emailMenui];
                        doane(e);
        	}
	} else if(op == 5) {
                var as = $('emailmore_menu').getElementsByTagName('a');
                for(i = 0;i < as.length;i++){
                        as[i].className = '';
                }
	}
}

function emailMenu(e, id) {
	if(BROWSER.ie && BROWSER.ie < 7) {
		return;
	}
	e = e ? e : window.event;
        var obj = $(id);
	if(obj.value.indexOf('@') != -1) {
		$('emailmore_menu').style.display = 'none';
		return;
	}
	var value = e.keyCode;
	var v = obj.value;
	if(!obj.value.length) {
		emailMenuOp(1);
		return;
	}

        if(value == 40) {
		emailMenui++;
		if(emailMenui >= emaildomains.length) {
			emailMenui = 0;
		}
	} else if(value == 38) {
		emailMenui--;
		if(emailMenui < 0) {
			emailMenui = emaildomains.length - 1;
		}
	} else if(value == 13) {
  		$('emailmore_menu').style.display = 'none';
  		return;
 	}
        if(!$('emailmore_menu')) {
		menu = document.createElement('div');
		menu.id = 'emailmore_menu';
		menu.style.display = 'none';
		menu.className = 'p_pop';
		menu.setAttribute('disautofocus', true);
		$('append_parent').appendChild(menu);
	}
	var s = '<ul>';
	for(var i = 0; i < emaildomains.length; i++) {
		s += '<li><a href="javascript:;" onmouseover="emailMenuOp(5)" ' + (emailMenui == i ? 'class="a" ' : '') + 'onclick="$(stmp[\'email\']).value=this.innerHTML;display(\'emailmore_menu\');checkemail(stmp[\'email\']);">' + v + '@' + emaildomains[i] + '</a></li>';
	}
	s += '</ul>';
	$('emailmore_menu').innerHTML = s;
	emailMenuOp(2);
}

function checksubmit() {
	var p_chks = $('registerform').getElementsByTagName('kbd');
	for(i = 0;i < p_chks.length;i++){
		if(p_chks[i].className == 'p_chk'){
			p_chks[i].innerHTML = '';
		}
	}
	ajaxpost('registerform', 'returnmessage4', 'returnmessage4', 'onerror');
	return;
}

function checkusername(id) {
	errormessage(id);
	var username = trim($(id).value);
	if($('tip_' + id).parentNode.className.match(/ p_right/) && (username == '' || username == lastusername)) {
		return;
	} else {
		lastusername = username;
	}
	if(username.match(/<|>|"|\(|\)|'/ig)) {
		errormessage(id, '用户名包含敏感字符');
		return;
	}
	var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
	if(unlen < 3 || unlen > 15) {
		errormessage(id, unlen < 3 ? '用户名不得小于 3 个字符' : '用户名不得超过 15 个字符');
		return;
	}
	var x = new Ajax();
	$('tip_' + id).parentNode.className = $('tip_' + id).parentNode.className.replace(/ p_right/, '');
	x.get('forum.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkusername&username=' + (BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(username) : username.replace(/%/g, '%25').replace(/#/g, '%23')), function(s) {
		errormessage(id, s);
	});
}

function checkpassword(id1, id2) {
	if(!$(id1).value && !$(id2).value) {
		return;
	}
	if(pwlength > 0) {
		if($(id1).value.length < pwlength) {
			errormessage(id1, '密码太短，不得少于 '+pwlength+' 个字符');
			return;
		}
	}
	if(strongpw) {
		var strongpw_error = false, j = 0;
		var strongpw_str = new Array();
		for(var i in strongpw) {
			if(strongpw[i] === 1 && !$(id1).value.match(/\d+/g)) {
				strongpw_error = true;
				strongpw_str[j] = '数字';
				j++;
			}
			if(strongpw[i] === 2 && !$(id1).value.match(/[a-z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = '小写字母';
				j++;
			}
			if(strongpw[i] === 3 && !$(id1).value.match(/[A-Z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = '大写字母';
				j++;
			}
			if(strongpw[i] === 4 && !$(id1).value.match(/[^A-Za-z0-9]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = '特殊符号';
				j++;
			}
		}
		if(strongpw_error) {
			errormessage(id1, '密码太弱，密码中必须包含 '+strongpw_str.join('，'));
			return;
		}
	}
	errormessage(id2);
	if($(id1).value != $(id2).value) {
		errormessage(id2, '两次输入的密码不一致');
	} else {
		errormessage(id2, !modifypwd ? 'succeed' : '');
	}
}

function checkemail(id) {
	errormessage(id);
	var email = trim($(id).value);
	if($(id).parentNode.className.match(/ p_right/) && (email == '' || email == lastemail)) {
		return;
	} else {
		lastemail = email;
	}
	if(email.match(/<|"/ig)) {
		errormessage(id, 'Email 包含敏感字符');
		return;
	}
	var x = new Ajax();
	$('tip_' + id).parentNode.className = $('tip_' + id).parentNode.className.replace(/ p_right/, '');
	x.get('forum.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkemail&email=' + email, function(s) {
		errormessage(id, s);
	});
}

function checkinvite() {
	errormessage('invitecode');
	var invitecode = trim($('invitecode').value);
	if(invitecode == '' || invitecode == lastinvitecode) {
		return;
	} else {
		lastinvitecode = invitecode;
	}
	if(invitecode.match(/<|"/ig)) {
		errormessage('invitecode', '邀请码包含敏感字符');
		return;
	}
	var x = new Ajax();
	$('tip_invitecode').parentNode.className = $('tip_invitecode').parentNode.className.replace(/ p_right/, '');
	x.get('forum.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkinvitecode&invitecode=' + invitecode, function(s) {
		errormessage('invitecode', s);
	});
}