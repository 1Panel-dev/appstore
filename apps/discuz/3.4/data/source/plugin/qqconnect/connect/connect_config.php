<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_config.php 33543 2013-07-03 06:01:33Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
$referer = dreferer();

if(submitcheck('connectsubmit')) {

	if($op == 'config') { // debug 修改QQ绑定设置

		$ispublisht = !empty($_GET['ispublisht']) ? 1 : 0;
		C::t('#qqconnect#common_member_connect')->update($_G['uid'],
			array(
				'conispublisht' => $ispublisht,
			)
		);
		if (!$ispublisht) {
			dsetcookie('connect_synpost_tip');
		}
		showmessage('qqconnect:connect_config_success', $referer);

	} elseif($op == 'unbind') {

		$connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		$_G['member'] = array_merge($_G['member'], $connect_member);

		if ($connect_member['conuinsecret']) {

			if($_G['member']['conisregister']) {
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
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}

		} else { // debug 因为老用户access token等信息，所以没法通知connect，所以直接在本地解绑就行了，不fopen connect

			if($_G['member']['conisregister']) {
				if($_GET['newpassword1'] !== $_GET['newpassword2']) {
					showmessage('profile_passwd_notmatch', $referer);
				}
				if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
					showmessage('profile_passwd_illegal', $referer);
				}
			}
		}

		C::t('#qqconnect#common_member_connect')->delete($_G['uid']);

		C::t('common_member')->update($_G['uid'], array('conisbind' => 0));
		C::t('#qqconnect#connect_memberbindlog')->insert(
			array(
				'uid' => $_G['uid'],
				'uin' => $_G['member']['conopenid'],
				'type' => 2,
				'dateline' => $_G['timestamp'],
			)
		);

		if($_G['member']['conisregister']) {
			loaducenter();
			uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], null, 1);
		}

		foreach($_G['cookie'] as $k => $v) {
			dsetcookie($k);
		}

		$_G['uid'] = $_G['adminid'] = 0;
		$_G['username'] = $_G['member']['password'] = '';

		showmessage('qqconnect:connect_config_unbind_success', 'member.php?mod=logging&action=login');
	}

} else {

	if($_G['inajax'] && $op == 'synconfig') {
		C::t('#qqconnect#common_member_connect')->update($_G['uid'],
			array(
				'conispublisht' => 0,
			)
		);
		dsetcookie('connect_synpost_tip');

	} elseif($op == 'weibosign') {
		if($_GET['hash'] != formhash()) {
			showmessage('submit_invalid');
		}

		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Connect.php';
		$connectService = new Cloud_Service_Connect();
		$connectService->connectMergeMember();

		if($_G['member']['conuin'] && $_G['member']['conuinsecret']) {

			$arr = array();
			$arr['oauth_consumer_key'] = $_G['setting']['connectappid'];
			$arr['oauth_nonce'] = mt_rand();
			$arr['oauth_timestamp'] = TIMESTAMP;
			$arr['oauth_signature_method'] = 'HMAC_SHA1';
			$arr['oauth_token'] = $_G['member']['conuin'];
			ksort($arr);
			$arr['oauth_signature'] = $connectService->connectGetOauthSignature('http://api.discuz.qq.com/connect/getSignature', $arr, 'GET', $_G['member']['conuinsecret']);

			$arr['version'] = 'qzone1.0';

			require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
			$utilService = new Cloud_Service_Util();
			$result = $connectService->connectOutputPhp('http://api.discuz.qq.com/connect/getSignature?' . $utilService->httpBuildQuery($arr, '', '&'));
			if ($result['status'] == 0) {
				$connectService->connectAjaxOuputMessage('[wb=' . $result['result']['username'] . ']' . $result['result']['signature_url'] . '[/wb]', 0);
			} else {
				$connectService->connectAjaxOuputMessage('connect_wbsign_no_account', $result['status']);
			}
		} else {
			$connectService->connectAjaxOuputMessage('connect_wbsign_no_bind', -1);
		}

	} else {
		dheader('location: home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp');
	}
}