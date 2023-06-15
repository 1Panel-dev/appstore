<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp.inc.php 34575 2014-06-04 02:12:08Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('QRCODE_EXPIRE', 1800);

$inspacecp = true;

if(submitcheck('resetpwsubmit') && $_G['wechatuser']['isregister']) {
	if($_G['setting']['strongpw']) {
		$strongpw_str = array();
		if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpassword1'])) {
			$strongpw_str[] = lang('member/template', 'strongpw_1');
		}
		if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpassword1'])) {
			$strongpw_str[] = lang('member/template', 'strongpw_2');
		}
		if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpassword1'])) {
			$strongpw_str[] = lang('member/template', 'strongpw_3');
		}
		if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpassword1'])) {
			$strongpw_str[] = lang('member/template', 'strongpw_4');
		}
		if($strongpw_str) {
			showmessage(lang('member/template', 'password_weak').implode(',', $strongpw_str));
		}
	}
	if($_GET['newpassword1'] !== $_GET['newpassword2']) {
		showmessage('profile_passwd_notmatch');
	}
	if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
		showmessage('profile_passwd_illegal');
	}

	loaducenter();
	uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], null, 1);

	C::t('common_member')->update($_G['uid'], array('password' => md5(random(10))));

	if($_G['wechat']['setting']['wechat_qrtype']) {
		C::t('#wechat#common_member_wechatmp')->update($_G['uid'], array('status' => 1));
	} else {
		C::t('#wechat#common_member_wechat')->update($_G['uid'], array('isregister' => 0));
	}

	showmessage('wechat:wsq_password_reset', dreferer());
} elseif(submitcheck('unbindsubmit')) {
	require_once libfile('function/member');
	if($_G['wechat']['setting']['wechat_qrtype']) {
		require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
		$member = C::t('#wechat#common_member_wechatmp')->fetch($_G['uid']);
		if(!$member || !wsq::userunbind($_G['uid'], $member['openid'])) {
			showmessage('wechat:wechat_message_unbind_fail');
		}
		C::t('#wechat#common_member_wechatmp')->delete($_G['uid']);
	} else {
		C::t('#wechat#common_member_wechat')->delete($_G['uid']);
		require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
		wsq::report('unbind');
	}

	clearcookies();
	showmessage('wechat:wechat_message_unbinded', $_G['siteurl']);
}