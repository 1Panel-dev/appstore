<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: connect_login.php 33793 2013-08-14 07:26:06Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = !empty($_GET['op']) ? $_GET['op'] : '';
if(!in_array($op, array('init', 'callback', 'change'))) {
	showmessage('undefined_action');
}

$referer = dreferer();

require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/ConnectOAuth.php';

try {
	$connectOAuthClient = new Cloud_Service_Client_ConnectOAuth();
} catch(Exception $e) {
	showmessage('qqconnect:connect_app_invalid');
}

if($op == 'init') {

	if($_G['member']['conisbind'] && $_GET['reauthorize']) {
		if($_GET['formhash'] == FORMHASH) {
			$connectService->connectMergeMember();
		} else {
			showmessage('submit_invalid');
		}
	}

	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']);

	try {
		dsetcookie('con_request_uri', $callback);
		$redirect = $connectOAuthClient->getOAuthAuthorizeURL_V2($callback);
		if(defined('IN_MOBILE') || $_GET['oauth_style'] == 'mobile') {
			$redirect .= '&display=mobile';
		}
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}


	dheader('Location:' . $redirect);

} elseif($op == 'callback') {

	$params = $_GET;

	if(!isset($params['receive'])) {
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		echo '<script type="text/javascript">setTimeout("window.location.href=\'connect.php?receive=yes&'.str_replace("'", "\'", $utilService->httpBuildQuery($_GET, '', '&')).'\'", 1)</script>';
		exit;
	}

	if($_GET['state'] != md5(FORMHASH)){
		showmessage('qqconnect:connect_get_access_token_failed', $referer);
	}
	try {
		$response = $connectOAuthClient->connectGetOpenId_V2($_G['cookie']['con_request_uri'], $_GET['code']);
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_access_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	dsetcookie('con_request_token');
	dsetcookie('con_request_token_secret');

	$conuintoken = $response['access_token'];
	$conopenid = strtoupper($response['openid']);
	if(!$conuintoken || !$conopenid) {
		showmessage('qqconnect:connect_get_access_token_failed', $referer);
	}

	loadcache('connect_blacklist');
	if(in_array($conopenid, array_map('strtoupper', $_G['cache']['connect_blacklist']))) {
		$change_qq_url = $_G['connect']['discuz_change_qq_url'];
		showmessage('qqconnect:connect_uin_in_blacklist', $referer, array('changeqqurl' => $change_qq_url));
	}

	$referer = $referer && (strpos($referer, 'logging') === false) && (strpos($referer, 'mod=login') === false) ? $referer : 'index.php';

	if($params['uin']) {
		$old_conuin = $params['uin'];
	}

	$is_notify = true;

	$conispublishfeed = 0;
	$conispublisht = 0;

	$is_user_info = 1;
	$is_feed = 0;

	$user_auth_fields = 1;

	$cookie_expires = 2592000;
	dsetcookie('client_created', TIMESTAMP, $cookie_expires);
	dsetcookie('client_token', $conopenid, $cookie_expires);

	$connect_member = array();
	$fields = array('uid', 'conuin', 'conuinsecret', 'conopenid');
	if($old_conuin) {
		$connect_member = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($old_conuin, $fields);
	}
	if(empty($connect_member)) {
		$connect_member = C::t('#qqconnect#common_member_connect')->fetch_fields_by_openid($conopenid, $fields);
	}
	if($connect_member) {
		$member = getuserbyuid($connect_member['uid']);
		if($member) {
			if(!$member['conisbind']) {
				C::t('#qqconnect#common_member_connect')->delete($connect_member['uid']);
				unset($connect_member);
			} else {
				$connect_member['conisbind'] = $member['conisbind'];
			}
		} else {
			C::t('#qqconnect#common_member_connect')->delete($connect_member['uid']);
			unset($connect_member);
		}
	}

	$connect_is_unbind = $params['is_unbind'] == 1 ? 1 : 0;
	if($connect_is_unbind && $connect_member && !$_G['uid'] && $is_notify) {
		dsetcookie('connect_js_name', 'user_bind', 86400);
		dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'registerbind'))), 86400);
	}

	if($_G['uid']) {

		if($connect_member && $connect_member['uid'] != $_G['uid']) {
			showmessage('qqconnect:connect_register_bind_uin_already', $referer, array('username' => $_G['member']['username']));
		}

		$isqqshow = 0;

		$current_connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		if($_G['member']['conisbind'] && $current_connect_member['conopenid']) {
			if(strtoupper($current_connect_member['conopenid']) != $conopenid) {
				showmessage('qqconnect:connect_register_bind_already', $referer);
			}
			C::t('#qqconnect#common_member_connect')->update($_G['uid'], array(
				'conuintoken' => $conuintoken,
				'conopenid' => $conopenid,
				'conisregister' => 0,
				'conisfeed' => 1,
				'conisqqshow' => $isqqshow,
			));

		} else { // debug 当前登录的论坛账号并没有绑定任何QQ号，则可以绑定当前的这个QQ号
			if(empty($current_connect_member)) {
				C::t('#qqconnect#common_member_connect')->insert(array(
					'uid' => $_G['uid'],
					'conuin' => '',
					'conuintoken' => $conuintoken,
					'conopenid' => $conopenid,
					'conispublishfeed' => $conispublishfeed,
					'conispublisht' => $conispublisht,
					'conisregister' => 0,
					'conisfeed' => 1,
					'conisqqshow' => $isqqshow,
				));
			} else {
				C::t('#qqconnect#common_member_connect')->update($_G['uid'],
					array(
						'conuintoken' => $conuintoken,
						'conopenid' => $conopenid,
						'conispublishfeed' => $conispublishfeed,
						'conispublisht' => $conispublisht,
						'conisregister' => 0,
						'conisfeed' => 1,
						'conisqqshow' => $isqqshow,
					)
				);
			}
			C::t('common_member')->update($_G['uid'], array('conisbind' => '1'));

			C::t('#qqconnect#common_connect_guest')->delete($conopenid);
		}

		if($is_notify) {
			dsetcookie('connect_js_name', 'user_bind', 86400);
			dsetcookie('connect_js_params', base64_encode(serialize(array('type' => 'loginbind'))), 86400);
		}
		dsetcookie('connect_login', 1, 31536000);
		dsetcookie('connect_is_bind', '1', 31536000);
		dsetcookie('connect_uin', $conopenid, 31536000);
		dsetcookie('stats_qc_reg', 3, 86400);

		C::t('#qqconnect#connect_memberbindlog')->insert(
			array(
				'uid' => $_G['uid'],
				'uin' => $conopenid,
				'type' => 1,
				'dateline' => $_G['timestamp'],
			)
		);

		showmessage('qqconnect:connect_register_bind_success', $referer);

	} else {

		if($connect_member) { // debug 此分支是用户直接点击QQ登录，并且这个QQ号已经绑好一个论坛账号了，将直接登进论坛了
			C::t('#qqconnect#common_member_connect')->update($connect_member['uid'], array(
				'conuintoken' => $conuintoken,
				'conopenid' => $conopenid,
				'conisfeed' => 1,
			));

			$params['mod'] = 'login';
			connect_login($connect_member);

			loadcache('usergroups');
			$usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
			$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);

			C::t('common_member_status')->update($connect_member['uid'], array('lastip'=>$_G['clientip'], 'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
			$ucsynlogin = '';
			if($_G['setting']['allowsynlogin']) {
				loaducenter();
				$ucsynlogin = uc_user_synlogin($_G['uid']);
			}

			dsetcookie('stats_qc_login', 3, 86400);
			showmessage('login_succeed', $referer, $param, array('extrajs' => $ucsynlogin));

		} else { // debug 此分支是用户直接点击QQ登录，并且这个QQ号还未绑定任何论坛账号，将将跳转到一个新页引导用户注册个新论坛账号或绑一个已有的论坛账号

			$auth_hash = authcode($conopenid, 'ENCODE');
			$insert_arr = array(
				'conuintoken' => $conuintoken,
				'conopenid' => $conopenid,
			);

			$connectGuest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
			if ($connectGuest['conqqnick']) {
				$insert_arr['conqqnick'] = $connectGuest['conqqnick'];
			} else {
				try {
					require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/ConnectOAuth.php';
					$connectOAuthClient = new Cloud_Service_Client_ConnectOAuth();
					$connectUserInfo = $connectOAuthClient->connectGetUserInfo_V2($conopenid, $conuintoken);
					if ($connectUserInfo['nickname']) {
						$connectUserInfo['nickname'] = strip_tags($connectUserInfo['nickname']);
						$insert_arr['conqqnick'] = $connectUserInfo['nickname'];
					}
				} catch(Exception $e) {
				}
			}

			if ($insert_arr['conqqnick']) {
				dsetcookie('connect_qq_nick', $insert_arr['conqqnick'], 86400);
			}

			C::t('#qqconnect#common_connect_guest')->insert($insert_arr, false, true);

			dsetcookie('con_auth_hash', $auth_hash, 86400);
			dsetcookie('connect_js_name', 'guest_ptlogin', 86400);
			dsetcookie('stats_qc_login', 4, 86400);

			require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
			$utilService = new Cloud_Service_Util();

			$refererParams = explode('/', $referer);
			$mobileId = $refererParams[count($refererParams) - 1];

			if (substr($mobileId, 0, 7) == 'Mobile_') {
				showmessage('login_succeed', $referer);
			} else {
				$referer = 'member.php?mod=connect&referer='.urlencode($referer);
				$utilService->redirect($referer);
			}

		}
	}

} elseif($op == 'change') {
	$callback = $_G['connect']['callback_url'] . '&referer=' . urlencode($_GET['referer']);

	try {
		dsetcookie('con_request_uri', $callback);
		$redirect = $connectOAuthClient->getOAuthAuthorizeURL_V2($callback);
		if(defined('IN_MOBILE') || $_GET['oauth_style'] == 'mobile') {
			$redirect .= '&display=mobile';
		}
	} catch(Exception $e) {
		showmessage('qqconnect:connect_get_request_token_failed_code', $referer, array('codeMessage' => getErrorMessage($e->getmessage()), 'code' => $e->getmessage()));
	}

	dheader('Location:' . $redirect);
}

function connect_login($connect_member) {
	global $_G;

	if(!($member = getuserbyuid($connect_member['uid'], 1))) {
		return false;
	} else {
		if(isset($member['_inarchive'])) {
			C::t('common_member_archive')->move_to_master($member['uid']);
		}
	}

	require_once libfile('function/member');
	$cookietime = 1296000;
	setloginstatus($member, $cookietime);

	dsetcookie('connect_login', 1, $cookietime);
	dsetcookie('connect_is_bind', '1', 31536000);
	dsetcookie('connect_uin', $connect_member['conopenid'], 31536000);
	return true;
}

function getErrorMessage($errroCode) {
	$str = sprintf('connect_error_code_%d', $errroCode);

	return lang('plugin/qqconnect', $str);
}