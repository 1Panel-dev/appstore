<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: login.inc.php 34989 2014-09-24 07:22:03Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['wechat']['setting']) {
	$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
}

if(!$_G['wechat']['setting']['wechat_qrtype']) {
	showmessage('undefined_action');
}

if(!$_G['wechat']['setting']['wsq_siteid']) {
	showmessage('wechat:wechat_login_closed');
}

require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';

$ac = !empty($_GET['ac']) ? $_GET['ac'] : 'login';

if($ac == 'login') {
	$qrauth = $_G['cookie']['qrauth'] ? authcode(base64_decode($_G['cookie']['qrauth']), 'DECODE', $_G['config']['security']['authkey']) : '';
	if($_G['uid'] && !$qrauth) {
		$showtip = true;
		if(in_array('qqconnect', $_G['setting']['plugins']['available'])) {
			$connect = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
			if($connect['conisregister']) {
				$showtip = false;
			}
		}
		if($showtip) {
			dsetcookie('qrauth', '', -1);
			showmessage('wechat:wechat_member_bind_qrauth_lost');
		}
	}
	$url = wsq::qrconnectUrl($_G['uid'], dreferer());
	dheader('location: '.$url);
} elseif($ac == 'callback') {
	if(!wsq::checksign($_GET) || $_G['uid'] && $_GET['siteuid'] != $_G['uid']) {
		showmessage('wechat:wechat_member_auth_fail');
	}
	require_once libfile('function/member');
	if($_GET['siteuid'] && ($member = getuserbyuid($_GET['siteuid'], 1))) {
		setloginstatus($member, 1296000);
		if(!C::t('#wechat#common_member_wechatmp')->fetch($member['uid'])) {
			C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $_G['uid'], 'openid' => $_GET['openid'], 'status' => $_G['cookie']['qrauth'] ? 1: 0), false, true);
		}

		dheader('location: '.($_GET['referer'] ? $_GET['referer'] : $_G['siteurl']));
	} else {
		require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.class.php';
		require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
		$defaultusername = WeChatEmoji::clear($_GET['nickname']);
		if(!$_G['wechat']['setting']['wechat_allowfastregister']) {
			redirectregister($defaultusername);
		}

		loaducenter();
		$user = uc_get_user($defaultusername);
		if(!empty($user)) {
			$defaultusername = cutstr($defaultusername, 7, '').'_'.random(5);
		}
		$uid = WeChat::register($defaultusername, 1, 8);
		if(!$uid) {
			redirectregister($defaultusername);
		}
		C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $uid, 'openid' => $_GET['openid'], 'status' => 0), false, true);
		$url = wsq::userregisterUrl($uid, $_GET['openid'], $_GET['openidsign'], $_GET['referer']);
		dheader('location: '.$url);
	}
} elseif($ac == 'regcallback' && $_G['uid']) {
	list($openid, $openidsign, $qrreferer) = explode("\t", authcode(base64_decode($_GET['auth']), 'DECODE'));
	if(!$openid) {
		showmessage('wechat:wechat_member_auth_fail');
	}
	C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $_G['uid'], 'openid' => $openid, 'status' => 1), false, true);
	$url = wsq::userregisterUrl($_G['uid'], $openid, $openidsign, $qrreferer);
	dheader('location: '.$url);
} elseif($ac == 'regverify' && $_G['uid']) {
	if(!wsq::checksign($_GET)) {
		showmessage('wechat:wechat_member_auth_fail');
	}
	if($_GET['code']) {
		showmessage('wechat:wechat_member_register_faild');
	}
	$member = C::t('#wechat#common_member_wechatmp')->fetch($_G['uid']);
	if(!$member) {
		showmessage('wechat:wechat_member_register_faild');
	}
	$groupid = $_G['wechat']['setting']['wechat_newusergroupid'] ? $_G['wechat']['setting']['wechat_newusergroupid'] : $_G['setting']['newusergroupid'];
	C::t('common_member')->update($_G['uid'], array('groupid' => $groupid));
	dheader('location: '.($_G['referer'] ? $_GET['referer'] : $_G['siteurl']));
} elseif($ac == 'wxlogin') {
	unset($_GET['mapifrom'], $_GET['charset']);
	if(wsq::checksign($_GET)) {
		$member = getuserbyuid($_GET['siteuid'], 1);
		if($member) {
			require_once libfile('function/member');
			setloginstatus($member, 1296000);
		}
	}
} elseif($ac == 'wxregverify') {
	if(!wsq::checksign($_GET)) {
		showmessage('wechat:wechat_member_auth_fail');
	}
	$member = getuserbyuid($_GET['siteuid'], 1);
	if($member) {
		require_once libfile('function/member');
		setloginstatus($member, 1296000);
	}
	if($_G['cookie']['wxnewuser']) {
		$groupid = $_G['wechat']['setting']['wechat_newusergroupid'] ? $_G['wechat']['setting']['wechat_newusergroupid'] : $_G['setting']['newusergroupid'];
		C::t('common_member')->update($_G['uid'], array('groupid' => $groupid));
		dsetcookie('wxnewuser', '', -1);
	}
	dheader('location: '.($_GET['referer'] ? $_GET['referer'] : $_G['siteurl']));
} else {
	showmessage('undefined_action');
}

function redirectregister($username) {
	global $_G;
	$defaultusername = substr($username, 0, 15);
	loaducenter();
	$user = uc_get_user($defaultusername);
	if(!empty($user)) {
		$defaultusername = cutstr($defaultusername, 7, '').'_'.random(5);
	}
	$auth = urlencode(base64_encode(authcode($_GET['openid']."\t".$_GET['openidsign']."\t".$_GET['referer'], 'ENCODE')));
	$referer = urlencode($_G['siteurl'].'plugin.php?id=wechat:login&ac=regcallback&auth='.$auth);
	dheader('location: '.$_G['siteurl'].'member.php?mod='.$_G['setting']['regname'].'&referer='.$referer.'&defaultusername='.urlencode($defaultusername));
}