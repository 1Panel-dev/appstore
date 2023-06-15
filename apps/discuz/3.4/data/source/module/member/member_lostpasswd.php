<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_lostpasswd.php 35041 2014-10-29 08:05:36Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

$discuz_action = 141;

if(submitcheck('lostpwsubmit')) {
	loaducenter();
	$_GET['email'] = strtolower(trim($_GET['email']));
	if($_GET['username']) {
		list($tmp['uid'], , $tmp['email']) = uc_get_user(addslashes($_GET['username']));
		$tmp['email'] = strtolower(trim($tmp['email']));
		if($_GET['email'] != $tmp['email']) {
			showmessage('getpasswd_account_notmatch');
		}
		$member = getuserbyuid($tmp['uid'], 1);
	} else {
		$emailcount = C::t('common_member')->count_by_email($_GET['email'], 1);
		if(!$emailcount) {
			showmessage('lostpasswd_email_not_exist');
		}
		if($emailcount > 1) {
			showmessage('lostpasswd_many_users_use_email');
		}
		$member = C::t('common_member')->fetch_by_email($_GET['email'], 1);
		list($tmp['uid'], , $tmp['email']) = uc_get_user(addslashes($member['username']));
		$tmp['email'] = strtolower(trim($tmp['email']));
	}
	if(!$member) {
		showmessage('getpasswd_account_notmatch');
	} elseif($member['adminid'] == 1 || $member['adminid'] == 2) {
		showmessage('getpasswd_account_invalid');
	}

	$table_ext = $member['_inarchive'] ? '_archive' : '';
	if($member['email'] != $tmp['email']) {
		C::t('common_member'.$table_ext)->update($tmp['uid'], array('email' => $tmp['email']));
	}

	$memberauthstr = C::t('common_member_field_forum'.$table_ext)->fetch($member['uid']);
	list($dateline, $operation, $idstring) = explode("\t", $memberauthstr['authstr']);
	$interval = $_G['setting']['mailinterval'] > 0 ? (int)$_G['setting']['mailinterval'] : 300;
	if($dateline && $operation == 1 && $dateline > TIMESTAMP - $interval) {
		showmessage('getpasswd_has_send', '', array('interval' => $interval));
	}

	$idstring = random(6);
	C::t('common_member_field_forum'.$table_ext)->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
	require_once libfile('function/mail');
	$get_passwd_subject = lang('email', 'get_passwd_subject');
	$get_passwd_message = lang(
		'email',
		'get_passwd_message',
		array(
			'username' => $member['username'],
			'bbname' => $_G['setting']['bbname'],
			'siteurl' => $_G['setting']['securesiteurl'],
			'uid' => $member['uid'],
			'idstring' => $idstring,
			'clientip' => $_G['clientip'],
			'sign' => make_getpws_sign($member['uid'], $idstring),
		)
	);
	if(!sendmail("$_GET[username] <$tmp[email]>", $get_passwd_subject, $get_passwd_message)) {
		runlog('sendmail', "$tmp[email] sendmail failed.");
	}
	showmessage('getpasswd_send_succeed', $_G['siteurl'], array(), array('showdialog' => 1, 'locationtime' => true));
}

?>