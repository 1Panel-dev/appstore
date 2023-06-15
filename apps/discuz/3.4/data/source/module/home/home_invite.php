<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_invite.php 25756 2011-11-22 02:47:45Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = intval($_GET['id']);
$uid = intval($_GET['u']);
$appid = intval($_GET['app']);
$acceptconfirm = false;
if($_G['setting']['regstatus'] < 2) {
	showmessage('not_open_invite', '', array(), array('return' => true));
}
if($_G['uid']) {

	if($_GET['accept'] == 'yes') {
		$cookies = empty($_G['cookie']['invite_auth'])?array():explode(',', $_G['cookie']['invite_auth']);

		if(empty($cookies)) {
			showmessage('invite_code_error', '', array(), array('return' => true));
		}
		if(count($cookies) == 3) {
			$uid = intval($cookies[0]);
			$_GET['c'] = $cookies[1];
			$appid = intval($cookies[2]);
		} else {
			$id = intval($cookies[0]);
			$_GET['c'] = $cookies[1];
		}
		$acceptconfirm = true;

	} elseif($_GET['accept'] == 'no') {
		dsetcookie('invite_auth', '');
		showmessage('invite_accept_no', 'home.php');
	}
}

if($id) {

	$invite = C::t('common_invite')->fetch($id);

	if(empty($invite) || $invite['code'] != $_GET['c']) {
		showmessage('invite_code_error', '', array(), array('return' => true));
	}
	if($invite['fuid'] && $invite['fuid'] != $_G['uid']) {
		showmessage('invite_code_fuid', '', array(), array('return' => true));
	}
	if($invite['endtime'] && $_G['timestamp'] > $invite['endtime']) {
		C::t('common_invite')->delete($id);
		showmessage('invite_code_endtime_error', '', array(), array('return' => true));
	}

	$appid = $invite['appid'];
	$uid = $invite['uid'];

	$cookievar = "$id,$invite[code]";

} elseif ($uid) {

	$id = 0;
	$invite_code = space_key($uid, $appid);
	if($_GET['c'] !== $invite_code) {
		showmessage('invite_code_error', '', array(), array('return' => true));
	}
	$inviteuser = getuserbyuid($uid);
	loadcache('usergroup_'.$inviteuser['groupid']);
	if(!empty($_G['cache']['usergroup_'.$inviteuser['groupid']]) && (!$_G['cache']['usergroup_'.$inviteuser['groupid']]['allowinvite'] || $_G['cache']['usergroup_'.$inviteuser['groupid']]['inviteprice'])) {
		showmessage('invite_code_error', '', array(), array('return' => true));
	}

	$cookievar = "$uid,$invite_code,$appid";

} else {
	showmessage('invite_code_error', '', array(), array('return' => true));
}

$space = getuserbyuid($uid);
if(empty($space)) {
	showmessage('space_does_not_exist', '', array(), array('return' => true));
}
$jumpurl = 'home.php?mod=space&uid='.$uid;
if($acceptconfirm) {

	dsetcookie('invite_auth', '');

	if($_G['uid'] == $uid) {
		showmessage('should_not_invite_your_own', '', array(), array('return' => true));
	}

	require_once libfile('function/friend');
	if(friend_check($uid)) {
		showmessage('you_have_friends', $jumpurl);
	}

	friend_make($space['uid'], $space['username']);

	if($id) {
		C::t('common_invite')->update($id, array('fuid'=>$_G['uid'], 'fusername'=>$_G['username'], 'regdateline' => $_G['timestamp'], 'status' => 2));
		notification_add($uid, 'friend', 'invite_friend', array('actor' => '<a href="home.php?mod=space&uid='.$_G['uid'].'" target="_blank">'.$_G['username'].'</a>'), 1);
	}
	space_merge($space, 'field_home');
	if(!empty($space['privacy']['feed']['invite'])) {
		require_once libfile('function/feed');
		$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$_G['username'].'</a>');
		feed_add('friend', 'feed_invite', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $space['uid'], $space['username']);
	}

	if($_G['setting']['inviteconfig']['inviteaddcredit']) {
		updatemembercount($_G['uid'],
			array($_G['setting']['inviteconfig']['inviterewardcredit'] => $_G['setting']['inviteconfig']['inviteaddcredit']));
	}
	if($_G['setting']['inviteconfig']['invitedaddcredit']) {
		updatemembercount($uid,
			array($_G['setting']['inviteconfig']['inviterewardcredit'] => $_G['setting']['inviteconfig']['invitedaddcredit']));
	}

	include_once libfile('function/stat');
	updatestat($appid ? 'appinvite' : 'invite');

	showmessage('invite_friend_ok', $jumpurl);

} else {
	dsetcookie('invite_auth', $cookievar, 604800);
}

space_merge($space, 'count');
space_merge($space, 'field_home');
space_merge($space, 'profile');
$flist = array();
$query = C::t('home_friend')->fetch_all_by_uid($uid, 0, 12, true);
foreach($query as $value) {
	$value['uid'] = $value['fuid'];
	$value['username'] = $value['fusername'];
	$flist[] = $value;
}
$jumpurl = urlencode($jumpurl);
include_once template('home/invite');

?>