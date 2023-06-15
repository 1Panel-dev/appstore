/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: logging.js 23838 2011-08-11 06:51:58Z monkey $
*/

function lsSubmit(op) {
	var op = !op ? 0 : op;
	if(op) {
		$('lsform').cookietime.value = 2592000;
	}
	if($('ls_username').value == '' || $('ls_password').value == '') {
		showWindow('login', 'member.php?mod=logging&action=login' + (op ? '&cookietime=1' : ''));
	} else {
		ajaxpost('lsform', 'return_ls', 'return_ls');
	}
	return false;
}

function errorhandle_ls(str, param) {
	if(!param['type']) {
		showError(str);
	}
}