<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wechat.inc.php 35958 2016-05-24 02:34:37Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
define('IN_WECHAT', strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false);

require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.class.php';
require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT . './source/discuz_version.php';

list($openid, $sid) = explode("\t", authcode(base64_decode($_GET['key']), 'DECODE', $_G['config']['security']['authkey']));
$keyenc = urlencode($_GET['key']);

$ac = $_GET['ac'] ? $_GET['ac'] : 'bind';
if($_GET['fromapp'] == 'index') {
	$op = 'access';
} else {
	$op = $_GET['op'];
}

$preferer = parse_url($_GET['referer']);
if(!$preferer['host'] || $preferer['host'] != 'wsq.discuz.com' && $preferer['host'] != 'wsq.discuz.qq.com') {
	$_GET['referer'] = '';
}

$selfurl = $_G['siteurl'].'plugin.php?id=wechat&mobile=2&key='.$keyenc.($_GET['referer'] ? '&referer='.urlencode($_GET['referer']) : '').($_GET['username'] ? '&username='.urlencode($_GET['username']) : '').'&ac=';

if(!$_G['wechat']['setting']['wechat_qrtype'] && IN_WECHAT && !$openid) {
	if($_G['wechat']['setting']['wechat_mtype'] != 2) {
		if(!empty($_G['cookie']['wechatopenid'])) {
			$openid = authcode($_G['cookie']['wechatopenid'], 'DECODE', $_G['config']['security']['authkey']);
		}
		if(!$openid) {
			showmessage('wechat:wechat_undefined');
		}
	} else {
		$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);
		$openid = !empty($_G['cookie']['wechatopenid']) ? authcode($_G['cookie']['wechatopenid'], 'DECODE', $_G['config']['security']['authkey']) : '';
		if(!$openid) {
			if(empty($_GET['oauth'])) {
				$redirect_uri = $wechat_client->getOauthConnectUri($selfurl.$ac.'&oauth=yes');
				dheader('location: '.$redirect_uri);
			} else {
				$tockeninfo = $wechat_client->getAccessTokenByCode($_GET['code']);
				$openid = $tockeninfo['openid'];
				dsetcookie('wechatopenid', authcode($openid, 'ENCODE', $_G['config']['security']['authkey']), 86400);
			}
		}
	}
} elseif($openid) {
	dsetcookie('wechatopenid', authcode($openid, 'ENCODE', $_G['config']['security']['authkey']), 86400);
}

wsq::report('loginevent');

require_once libfile('function/member');

if($openid) {
	if($ac == 'qqbind') {
		WeChatHook::bindOpenId($_G['uid'], $openid);
		wsq::report('bind');
		$ac = 'bind';
	}
	$wechatuser = C::t('#wechat#common_member_wechat')->fetch_by_openid($openid);
	if(!$wechatuser) {
		if($_G['uid']) {
			clearcookies();
			dheader('location: '. $selfurl.$ac);
		}
		if($_G['wechat']['setting']['wechat_allowregister'] && $_G['wechat']['setting']['wechat_allowfastregister'] && $_G['wechat']['setting']['wechat_mtype'] == 2) {
			$authcode = C::t('#wechat#mobile_wechat_authcode')->fetch($sid);
			$uid = WeChat::register(WeChat::getnewname($openid), 1);
			if($uid) {
				WeChatHook::bindOpenId($uid, $openid, 1);
				if($sid) {
					C::t('#wechat#mobile_wechat_authcode')->update($sid, array('uid' => $uid, 'status' => 1));
				}
			}
			wsq::report('register');
		}
	}
}

if($op == 'access') {
	$redirect = WeChat::redirect();
	if($redirect) {
		dheader('location: '.$redirect);
	}
}

if($sid) {
	$authcode = C::t('#wechat#mobile_wechat_authcode')->fetch($sid);

	if($authcode) {
		if($_GET['confirm'] == 'delete') {
			C::t('#wechat#mobile_wechat_authcode')->delete($authcode['sid']);
			wechat_setloginstatus($authcode['uid'], false);
			include template('wechat:wechat_bind_confirm');
			exit;
		}
		if($wechatuser && !$authcode['uid']) {
			$member = getuserbyuid($wechatuser['uid'], 1);
			if(empty($_GET['confirm']) && (!$_G['wechat']['setting']['wechat_confirmtype'] && $member['adminid'] > 0 || $_G['wechat']['setting']['wechat_confirmtype'] == 1)) {
				wsq::report('showauthorized');
				include template('wechat:wechat_bind_confirm');
				exit;
			}
			setloginstatus($member, 1296000);
			C::t('#wechat#mobile_wechat_authcode')->update($sid, array('uid' => $wechatuser['uid'], 'status' => 1));
			wechat_setloginstatus($wechatuser['uid'], true);
			wsq::report('authorized');
		} elseif($authcode['uid']) {
			$member = getuserbyuid($authcode['uid'], 1);
			if(empty($_GET['confirm']) && (!$_G['wechat']['setting']['wechat_confirmtype'] && $member['adminid'] > 0 || $_G['wechat']['setting']['wechat_confirmtype'] == 1)) {
				wsq::report('showauthorized');
				include template('wechat:wechat_bind_confirm');
				exit;
			}
			if($wechatuser) {
				C::t('#wechat#common_member_wechat')->delete($wechatuser['uid']);
				wsq::report('unbind');
			}
			setloginstatus($member, 1296000);
			C::t('#wechat#mobile_wechat_authcode')->update($sid, array('status' => 1));
			WeChatHook::bindOpenId($authcode['uid'], $openid);
			wsq::report('bind');
			$wechatuser = C::t('#wechat#common_member_wechat')->fetch_by_openid($openid);
			wechat_setloginstatus($authcode['uid'], true);
			wsq::report('authorized');
		}
	}
} elseif($wechatuser) {
	$member = getuserbyuid($wechatuser['uid'], 1);
	setloginstatus($member, 1296000);
	wechat_setloginstatus($wechatuser['uid'], true);
}

if($ac == 'bind' && $_G['wechat']['setting']['wechat_qrtype']) {
	if(!$_G['uid'] && IN_WECHAT && $_G['wechat']['setting']['wechat_allowfastregister']) {
		$ac = 'wxregister';
	}
	list($_GET['username'], $wxopenid) = explode("\t", base64_decode($_GET['username']));
	$_GET['username'] = substr(WeChatEmoji::clear($_GET['username']), 0, 15);
}

if($ac == 'bind') {
	define('IN_MOBILE', 2);

	if($_G['wechat']['setting']['wechat_qrtype'] && $_GET['referer']) {
		$_GET['referer'] = str_replace('&state=siteregister', '&state=backlogin', $_GET['referer']);
		dheader('location: '.$_GET['referer']);
	}

	if($_G['wechat']['setting']['wechat_mtype'] == 2) {
		$defaultusername = WeChat::getnewname($openid);
	} else {
		$defaultusername = $_G['wechat']['setting']['wechat_qrtype'] ? $_GET['username'] : 'wx_'.random(5);
	}
	$defaultusername = htmlspecialchars($defaultusername);

	$connecturl = $_G['setting']['connect']['allow'] && !$_G['setting']['bbclosed'] ? $_G['siteurl'].'connect.php?mod=login&op=init&referer='.urlencode($selfurl.'qqbind').'&statfrom=login_simple' : '';

	if(IN_WECHAT) {
		if(!$_G['uid']) {
			include template('wechat:wechat_bind');
		} else {
			$redirect = WeChat::redirect();
			if($redirect) {
				dheader('location: '.$redirect);
			} else {
				dheader('location: '.$_G['siteurl']);
			}
		}
	} else {
		dheader('location: '.$_G['siteurl'].'member.php?mod=logging&action=login&referer='.dreferer());
	}
} elseif($ac == 'login' && submitcheck('submit')) {
	if(!($loginperm = logincheck($_GET['username']))) {
		showmessage('login_strike');
	}

	if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
		showmessage('profile_passwd_illegal');
	}

	if(DISCUZ_VERSION < 'X3.0') {
		$_GET['username'] = WeChatEmoji::clear($_GET['username']);
	}
	$result = userlogin($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer'], $_G['setting']['autoidselect'] ? 'auto' : $_GET['loginfield'], $_G['clientip']);

	if($result['status'] <= 0) {
		loginfailed($_GET['username']);
		failedip();
		if($loginperm == -1) {
			showmessage('login_password_invalid');
		} else {
			showmessage('login_invalid', '', array('loginperm' => $loginperm - 1));
		}
	}

	if(!$_G['wechat']['setting']['wechat_qrtype']) {
		if($wechatuser) {
			if($result['member']['uid'] != $wechatuser['uid']) {
				showmessage('wechat:wechat_openid_exists');
			}
			wechat_setloginstatus($result['member']['uid'], true);
		} else {
			WeChatHook::bindOpenId($result['member']['uid'], $openid);
			wsq::report('bind');
		}
		setloginstatus($result['member'], 1296000);

		showmessage('wechat:wechat_member_bind_succeed', $selfurl.'bind');
	} else {
		C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $result['member']['uid'], 'openid' => $_GET['wxopenid'], 'status' => 1), false, true);
		wsq::report('bind');
		$url = wsq::wxuserregisterUrl($result['member']['uid']);
		showmessage('wechat:wechat_member_bind_succeed', $url);
	}
} elseif(($ac == 'register' && submitcheck('submit') || $ac == 'wxregister') && $_G['wechat']['setting']['wechat_allowregister']) {
	if($wechatuser) {
		showmessage('wechat:wechat_openid_exists');
	} else {
		if(DISCUZ_VERSION < 'X3.0' && $_G['inajax']) {
			$_GET['username'] = WeChatEmoji::clear($_GET['username']);
		}
		if($ac == 'wxregister') {
			loaducenter();
			$user = uc_get_user($_GET['username']);
			if(!empty($user)) {
				$_GET['username'] = cutstr($_GET['username'], 7, '').'_'.random(5);
			}
		}

		$uid = WeChat::register($_GET['username'], $ac == 'wxregister');

		if($uid && $_GET['avatar']) {
			WeChat::syncAvatar($uid, $_GET['avatar']);
		}

		if(!$_G['wechat']['setting']['wechat_qrtype']) {
			WeChatHook::bindOpenId($uid, $openid, 1);
			wsq::report('register');
			showmessage('wechat:wechat_member_register_succeed', $selfurl.'bind&confirm=yes');
		} else {
			C::t('#wechat#common_member_wechatmp')->insert(array('uid' => $uid, 'openid' => $wxopenid ? $wxopenid : $_GET['wxopenid'], 'status' => 0), false, true);
			wsq::report('register');
			$url = wsq::wxuserregisterUrl($uid);
			if($ac == 'wxregister') {
				dheader('location: '.$url);
			} else {
				showmessage('wechat:wechat_member_register_succeed', $url);
			}
		}
	}
} elseif($ac == 'logout') {
	if($_GET['hash'] == formhash()) {
		wechat_setloginstatus($_G['uid'], false);
		clearcookies();
	}
	mobile_core::result(array());
} elseif($ac == 'unbind') {
	if($_GET['hash'] == formhash()) {
		if($wechatuser) {
			C::t('#wechat#common_member_wechat')->delete($wechatuser['uid']);
			wsq::report('unbind');
		}
		clearcookies();
	}
	mobile_core::result(array());
} elseif($ac == 'unbindmp') {
	if($_G['wechat']['setting']['wechat_qrtype'] && $_GET['hash'] == formhash()) {
		C::t('#wechat#common_member_wechatmp')->delete($_GET['uid']);
	}
	mobile_core::result(array());
} else {
	showmessage('undefined_action');
}

function wechat_setloginstatus($uid, $login) {
	C::t('#wechat#common_member_wechat')->update($uid, array('status' => $login ? 2 : 1));
}