<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_top.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['ranklist']['membershow']) {
	exit('Access Denied');
}

$operation = in_array($_GET['op'], array('modify')) ? trim($_GET['op']) : '';
if($_G['setting']['creditstransextra'][6]) {
	$key = 'extcredits'.intval($_G['setting']['creditstransextra'][6]);
} elseif ($_G['setting']['creditstrans']) {
	$key = 'extcredits'.intval($_G['setting']['creditstrans']);
} else {
	showmessage('trade_credit_invalid', '', array(), array('return' => 1));
}
space_merge($space, 'count');

if(submitcheck('friendsubmit')) {

	$showcredit = intval($_POST['stakecredit']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1) {
		showmessage('showcredit_error');
	}

	$_POST['fusername'] = trim($_POST['fusername']);
	$friend = C::t('home_friend')->fetch_all_by_uid_username($space['uid'], $_POST['fusername'], 0, 1);
	$friend = $friend[0];
	$fuid = $friend['fuid'];
	if(empty($_POST['fusername']) || empty($fuid) || $fuid == $space['uid']) {
		showmessage('showcredit_fuid_error', '', array(), array('return' => 1));
	}

	$count = getcount('home_show', array('uid'=>$fuid));
	if($count) {
		C::t('home_show')->update_credit_by_uid($fuid, $showcredit, false);
	} else {
		C::t('home_show')->insert(array('uid'=>$fuid, 'username'=>$_POST['fusername'], 'credit'=>$showcredit), false, true);
	}

	updatemembercount($space['uid'], array($_G['setting']['creditstransextra'][6] => (0-$showcredit)), true, 'RKC', $space['uid']);

	notification_add($fuid, 'credit', 'showcredit', array('credit'=>$showcredit));


	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit', array(
		'fusername' => "<a href=\"home.php?mod=space&uid=$fuid\">{$friend[fusername]}</a>",
		'credit' => $showcredit));
	}

	showmessage('showcredit_friend_do_success', "misc.php?mod=ranklist&type=member");

} elseif(submitcheck('showsubmit')) {

	$showcredit = intval($_POST['showcredit']);
	$unitprice = intval($_POST['unitprice']);
	if($showcredit > $space[$key]) $showcredit = $space[$key];
	if($showcredit < 1 || $unitprice < 1) {
		showmessage('showcredit_error', '', array(), array('return' => 1));
	}
	$_POST['note'] = getstr($_POST['note'], 100);
	$_POST['note'] = censor($_POST['note']);
	$showarr = C::t('home_show')->fetch($_G['uid']);
	if($showarr) {
		$notesql = $_POST['note'] ? $_POST['note'] : false;
		$unitprice = $unitprice > $showarr['credit']+$showcredit ? $showarr['credit']+$showcredit : $unitprice;
		C::t('home_show')->update_credit_by_uid($_G['uid'], $showcredit, false, $unitprice, $notesql);
	} else {
		$unitprice = $unitprice > $showcredit ? $showcredit : $unitprice;
		C::t('home_show')->insert(array('uid'=>$_G['uid'], 'username'=>$_G['username'], 'unitprice' => $unitprice, 'credit'=>$showcredit, 'note'=>$_POST['note']), false, true);
	}

	updatemembercount($space['uid'], array($_G['setting']['creditstransextra'][6] => (0-$showcredit)), true, 'RKC', $space['uid']);

	if(ckprivacy('show', 'feed')) {
		require_once libfile('function/feed');
		feed_add('show', 'feed_showcredit_self', array('credit'=>$showcredit), '', array(), $_POST['note']);
	}

	showmessage('showcredit_do_success', dreferer());
}
?>