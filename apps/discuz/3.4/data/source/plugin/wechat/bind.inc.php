<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: bind.inc.php 34850 2014-08-14 07:03:18Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_GET['unbind'] && $_GET['unbind'] == FORMHASH) {
	require_once libfile('function/member');
	C::t('#mobile#common_member_wechat')->delete($_G['uid']);
	clearcookies();
	showmessage('wechat:wechat_message_unbinded', dreferer());
}

if($_G['uid'] && submitcheck('confirmsubmit')) {
	loaducenter();
	list($result) = uc_user_login($_G['uid'], $_GET['passwordconfirm'], 1, 0);
	if($result >= 0) {
		dsetcookie('qrauth', base64_encode(authcode($result, 'ENCODE', $_G['config']['security']['authkey'], 300)));
		showmessage('', dreferer());
	}
	showmessage('login_password_invalid');
}

if(isset($_GET['check'])) {
	$code = authcode(base64_decode($_GET['check']), 'DECODE', $_G['config']['security']['authkey']);
	 if($code) {
		$authcode = C::t('#wechat#mobile_wechat_authcode')->fetch_by_code($code);
		if($authcode['status']) {
			require_once libfile('function/member');
			$member = getuserbyuid($authcode['uid'], 1);
			setloginstatus($member, 1296000);
			dsetcookie('wechat_ticket', '', -1);
			$echostr = 'done';
		} else {
			$echostr = '1';//json_encode($authcode);
		}
	 } else {
		$echostr = '-1';
	 }

	if(!ob_start($_G['gzipcompress'] ? 'ob_gzhandler' : null)) {
		ob_start();
	}

	if($echostr === 'done'){
		C::t('#wechat#mobile_wechat_authcode')->delete($authcode['sid']);
	}

	include template('common/header_ajax');
	echo $echostr;
	include template('common/footer_ajax');
	exit;
}

if($_G['cookie']['qrauth']) {
	$qrauth = authcode(base64_decode($_G['cookie']['qrauth']), 'DECODE', $_G['config']['security']['authkey']);
}

if(!$_G['wechat']['setting']['wechat_qrtype']) {
	require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
	require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
	list($isqrapi, $qrcodeurl, $codeenc, $code) = WeChat::getqrcode();
	wsq::report('siteqrshow');
}

if($_G['uid'] && !$qrauth && in_array('qqconnect', $_G['setting']['plugins']['available'])) {
	$connect = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
	if($connect['conisregister']) {
		if(!$_G['wechat']['setting']['wechat_qrtype']) {
			$qrauth = true;
		} else {
			showmessage('', 'plugin.php?id=wechat:login', array(), array('redirectmsg' => 1, 'location' => 1));
		}

	}
}

include_once template('wechat:wechat_qrcode');