<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_member.php 35030 2014-10-23 07:43:23Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function userlogin($username, $password, $questionid, $answer, $loginfield = 'username', $ip = '') {
	$return = array();

	if($loginfield == 'uid' && getglobal('setting/uidlogin')) {
		$isuid = 1;
	} elseif($loginfield == 'email') {
		$isuid = 2;
	} elseif($loginfield == 'auto') {
		$isuid = 3;
	} else {
		$isuid = 0;
	}

	if(!function_exists('uc_user_login')) {
		loaducenter();
	}
	if($isuid == 3) {
		if(!strcmp(dintval($username), $username) && getglobal('setting/uidlogin')) {
			$return['ucresult'] = uc_user_login($username, $password, 1, 1, $questionid, $answer, $ip);
		} elseif(isemail($username)) {
			$return['ucresult'] = uc_user_login($username, $password, 2, 1, $questionid, $answer, $ip);
		}
		if($return['ucresult'][0] <= 0 && $return['ucresult'][0] != -3) {
			$return['ucresult'] = uc_user_login(addslashes($username), $password, 0, 1, $questionid, $answer, $ip);
		}
	} else {
		$return['ucresult'] = uc_user_login(addslashes($username), $password, $isuid, 1, $questionid, $answer, $ip);
	}
	$tmp = array();
	$duplicate = '';
	list($tmp['uid'], $tmp['username'], $tmp['password'], $tmp['email'], $duplicate) = $return['ucresult'];
	$return['ucresult'] = $tmp;
	if($duplicate && $return['ucresult']['uid'] > 0 || $return['ucresult']['uid'] <= 0) {
		$return['status'] = 0;
		return $return;
	}

	$member = getuserbyuid($return['ucresult']['uid'], 1);
	if(!$member || empty($member['uid'])) {
		$return['status'] = -1;
		return $return;
	}
	$return['member'] = $member;
	$return['status'] = 1;
	if($member['_inarchive']) {
		C::t('common_member_archive')->move_to_master($member['uid']);
	}
	if($member['email'] != $return['ucresult']['email']) {
		C::t('common_member')->update($return['ucresult']['uid'], array('email' => $return['ucresult']['email']));
	}

	return $return;
}

function setloginstatus($member, $cookietime) {
	global $_G;
	$_G['uid'] = intval($member['uid']);
	$_G['username'] = $member['username'];
	$_G['adminid'] = $member['adminid'];
	$_G['groupid'] = $member['groupid'];
	$_G['formhash'] = formhash();
	$_G['session']['invisible'] = getuserprofile('invisible');
	$_G['member'] = $member;
	loadcache('usergroup_'.$_G['groupid']);
	C::app()->session->isnew = true;
	C::app()->session->updatesession();

	dsetcookie('auth', authcode("{$member['password']}\t{$member['uid']}", 'ENCODE'), $cookietime, 1, true);
	dsetcookie('loginuser');
	dsetcookie('activationauth');
	dsetcookie('pmnum');

	include_once libfile('function/stat');
	updatestat('login', 1);
	if(defined('IN_MOBILE')) {
		updatestat('mobilelogin', 1);
	}
	if($_G['setting']['connect']['allow'] && $_G['member']['conisbind']) {
		updatestat('connectlogin', 1);
	}
	$rule = updatecreditbyaction('daylogin', $_G['uid']);
	if(!$rule['updatecredit']) {
		checkusergroup($_G['uid']);
	}
}

function logincheck($username) {
	global $_G;

	$return = 0;
	$username = trim($username);
	loaducenter();
	if(function_exists('uc_user_logincheck')) {
		$return = uc_user_logincheck(addslashes($username), $_G['clientip']);
	} else {
		$login = C::t('common_failedlogin')->fetch_ip($_G['clientip']);
		$return = (!$login || (TIMESTAMP - $login['lastupdate'] > 900)) ? 5 : max(0, 5 - $login['count']);

		if(!$login) {
			C::t('common_failedlogin')->insert(array(
				'ip' => $_G['clientip'],
				'count' => 0,
				'lastupdate' => TIMESTAMP
			), false, true);
		} elseif(TIMESTAMP - $login['lastupdate'] > 900) {
			C::t('common_failedlogin')->insert(array(
				'ip' => $_G['clientip'],
				'count' => 0,
				'lastupdate' => TIMESTAMP
			), false, true);
			C::t('common_failedlogin')->delete_old(901);
		}
	}
	return $return;
}

function loginfailed($username) {
	global $_G;

	loaducenter();
	if(function_exists('uc_user_logincheck')) {
		return;
	}
	C::t('common_failedlogin')->update_failed($_G['clientip']);
}

function failedipcheck($numiptry, $timeiptry) {
	global $_G;
	if(!$numiptry) {
		return false;
	}
	list($ip1, $ip2) = explode('.', $_G['clientip']);
	$ip = $ip1.'.'.$ip2;
	return $numiptry <= C::t('common_failedip')->get_ip_count($ip, TIMESTAMP - $timeiptry);
}

function failedip() {
	global $_G;
	list($ip1, $ip2) = explode('.', $_G['clientip']);
	$ip = $ip1.'.'.$ip2;
	C::t('common_failedip')->insert_ip($ip);
}

function getinvite() {
	global $_G;

	if($_G['setting']['regstatus'] == 1) return array();
	$result = array();
	$cookies = empty($_G['cookie']['invite_auth']) ? array() : explode(',', $_G['cookie']['invite_auth']);
	$cookiecount = count($cookies);
	$_GET['invitecode'] = trim($_GET['invitecode']);
	if($cookiecount == 2 || $_GET['invitecode']) {
		$id = intval($cookies[0]);
		$code = trim($cookies[1]);
		if($_GET['invitecode']) {
			$invite = C::t('common_invite')->fetch_by_code($_GET['invitecode']);
			$code = trim($_GET['invitecode']);
		} else {
			$invite = C::t('common_invite')->fetch($id);
		}
		if(!empty($invite)) {
			if($invite['code'] == $code && empty($invite['fuid']) && (empty($invite['endtime']) || $_G['timestamp'] < $invite['endtime'])) {
				$result['uid'] = $invite['uid'];
				$result['id'] = $invite['id'];
				$result['appid'] = $invite['appid'];
			}
		}
	} elseif($cookiecount == 3) {
		$uid = intval($cookies[0]);
		$code = trim($cookies[1]);
		$appid = intval($cookies[2]);

		$invite_code = space_key($uid, $appid);
		if($code === $invite_code) {
			$member = getuserbyuid($uid);
			if($member) {
				$usergroup = C::t('common_usergroup')->fetch($member['groupid']);
				if(!$usergroup['allowinvite'] || $usergroup['inviteprice'] > 0) return array();
			} else {
				return array();
			}
			$result['uid'] = $uid;
			$result['appid'] = $appid;
		}
	}

	if($result['uid']) {
		$member = getuserbyuid($result['uid']);
		$result['username'] = $member['username'];
	} else {
		dsetcookie('invite_auth', '');
	}

	return $result;
}

function replacesitevar($string, $replaces = array()) {
	global $_G;
	$sitevars = array(
		'{sitename}' => $_G['setting']['sitename'],
		'{bbname}' => $_G['setting']['bbname'],
		'{time}' => dgmdate(TIMESTAMP, 'Y-n-j H:i'),
		'{adminemail}' => $_G['setting']['adminemail'],
		'{username}' => $_G['member']['username'],
		'{myname}' => $_G['member']['username']
	);
	$replaces = array_merge($sitevars, $replaces);
	return str_replace(array_keys($replaces), array_values($replaces), $string);
}

function clearcookies() {
	global $_G;
	foreach($_G['cookie'] as $k => $v) {
		if($k != 'widthauto') {
			dsetcookie($k);
		}
	}
	$_G['uid'] = $_G['adminid'] = 0;
	$_G['username'] = $_G['member']['password'] = '';
}

function crime($fun) {
	if(!$fun) {
		return false;
	}
	include_once libfile('class/member');
	$crimerecord = & crime_action_ctl::instance();
	$arg_list = func_get_args();
	if($fun == 'recordaction') {
		list(, $uid, $action, $reason) = $arg_list;
		return $crimerecord->$fun($uid, $action, $reason);
	} elseif($fun == 'getactionlist') {
		list(, $uid) = $arg_list;
		return $crimerecord->$fun($uid);
	} elseif($fun == 'getcount') {
		list(, $uid, $action) = $arg_list;
		return $crimerecord->$fun($uid, $action);
	} elseif($fun == 'search') {
		list(, $action, $username, $operator, $starttime, $endtime, $reason, $start, $limit) = $arg_list;
		return $crimerecord->$fun($action, $username, $operator, $starttime, $endtime, $reason, $start, $limit);
	} elseif($fun == 'actions') {
		return crime_action_ctl::$actions;
	}
	return false;
}
function checkfollowfeed() {
	global $_G;

	if($_G['uid']) {
		$lastcheckfeed = 0;
		if(!empty($_G['cookie']['lastcheckfeed'])) {
			$time = explode('|', $_G['cookie']['lastcheckfeed']);
			if($time[0] == $_G['uid']) {
				$lastcheckfeed = $time[1];
			}
		}
		if(!$lastcheckfeed) {
			$lastcheckfeed = getuserprofile('lastactivity');
		}
		dsetcookie('lastcheckfeed', $_G['uid'].'|'.TIMESTAMP, 31536000);
		$followuser = C::t('home_follow')->fetch_all_following_by_uid($_G['uid']);
		$uids = array_keys($followuser);
		if(!empty($uids)) {
			$count = C::t('home_follow_feed')->count_by_uid_dateline($uids, $lastcheckfeed);
			if($count) {
				notification_add($_G['uid'], 'follow', 'member_follow', array('count' => $count, 'from_id'=>$_G['uid'], 'from_idtype' => 'follow'), 1);
			}
		}
	}
	dsetcookie('checkfollow', 1, 30);
}
function checkemail($email) {
	global $_G;

	$email = strtolower(trim($email));
	if(strlen($email) > 32) {
		showmessage('profile_email_illegal', '', array(), array('handle' => false));
	}
	if($_G['setting']['regmaildomain']) {
		$maildomainexp = '/('.str_replace("\r\n", '|', preg_quote(trim($_G['setting']['maildomainlist']), '/')).')$/i';
		if($_G['setting']['regmaildomain'] == 1 && !preg_match($maildomainexp, $email)) {
			showmessage('profile_email_domain_illegal', '', array(), array('handle' => false));
		} elseif($_G['setting']['regmaildomain'] == 2 && preg_match($maildomainexp, $email)) {
			showmessage('profile_email_domain_illegal', '', array(), array('handle' => false));
		}
	}

	loaducenter();
	$ucresult = uc_user_checkemail($email);

	if($ucresult == -4) {
		showmessage('profile_email_illegal', '', array(), array('handle' => false));
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal', '', array(), array('handle' => false));
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate', '', array(), array('handle' => false));
	}
}

function make_getpws_sign($uid, $idstring) {
	global $_G;
	$link = "member.php?mod=getpasswd&uid={$uid}&id={$idstring}";
	return dsign($link);
}
?>