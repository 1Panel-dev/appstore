<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_emailcheck.php 33688 2013-08-02 03:00:15Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$uid = 0;
$email = '';
$_GET['hash'] = empty($_GET['hash']) ? '' : $_GET['hash'];
if($_GET['hash']) {
	list($uid, $email, $time) = explode("\t", authcode($_GET['hash'], 'DECODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16))));
	$uid = intval($uid);
}

if($uid && isemail($email) && $time > TIMESTAMP - 86400) {
	$member = getuserbyuid($uid);
	$member = array_merge(C::t('common_member_field_forum')->fetch($uid), $member);
	list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);
	if($dateline != $time || $operation != 3 || $idstring != substr(md5($email), 0, 6)) {
		showmessage('email_check_error', 'index.php');
	}

	$setarr = array('email'=>$email, 'emailstatus'=>'1');
	if($member['freeze'] == 2) {
		$setarr['freeze'] = 0;
	}
	loaducenter();
	$ucresult = uc_user_edit(addslashes($member['username']), '', '', $email, 1);
	if($ucresult == -8) {
		showmessage('email_check_account_invalid', '', array(), array('return' => true));
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal', '', array(), array('return' => true));
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal', '', array(), array('return' => true));
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate', '', array(), array('return' => true));
	}
	if($_G['setting']['regverify'] == 1 && $member['groupid'] == 8) {
		$membergroup = C::t('common_usergroup')->fetch_by_credits($member['credits']);
		$setarr['groupid'] = $membergroup['groupid'];
	}
	updatecreditbyaction('realemail', $uid);
	C::t('common_member')->update($uid, $setarr);
	C::t('common_member_field_forum')->update($uid, array('authstr' => ''));
	C::t('common_member_validate')->delete($uid);
	dsetcookie('newemail', "", -1);

	showmessage('email_check_sucess', 'home.php?mod=spacecp&ac=profile&op=password', array('email' => $email));
} else {
	showmessage('email_check_error', 'index.php');
}

?>