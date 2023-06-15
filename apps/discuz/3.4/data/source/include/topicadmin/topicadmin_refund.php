<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_refund.php 30872 2012-06-27 10:11:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowrefund'] || $thread['price'] <= 0) {
	showmessage('undefined_action', NULL);
}

if(!isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][1]])) {
	showmessage('credits_transaction_disabled');
}

if($thread['special'] != 0) {
	showmessage('special_refundment_invalid');
}

if(!submitcheck('modsubmit')) {

	$payment = C::t('common_credit_log')->count_stc_by_relatedid($_G['tid'], $_G['setting']['creditstransextra'][1]);
	$payment['payers'] = intval($payment['payers']);
	$payment['income'] = intval($payment['income']);

	include template('forum/topicadmin_action');

} else {

	$modaction = 'RFD';
	$modpostsnum ++;

	$reason = checkreasonpm();

	$totalamount = 0;
	$amountarray = array();

	$logarray = array();
	foreach(C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid(0, 'BTC', $_G['tid']) as $log) {
		$amount = abs($log['extcredits'.$_G['setting']['creditstransextra'][1]]);
		$totalamount += $amount;
		$amountarray[$amount][] = $log['uid'];
	}

	updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][1] => -$totalamount));
	C::t('forum_thread')->update($_G['tid'], array('price'=>-1, 'moderated'=>1));

	foreach($amountarray as $amount => $uidarray) {
		updatemembercount($uidarray, array($_G['setting']['creditstransextra'][1] => $amount));
	}

	C::t('common_credit_log')->delete_by_operation_relatedid(array('BTC', 'STC'), $_G['tid']);

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_moderate', 'notictype' => 'post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason),
	'modtids'	=> $thread['tid'],
	'modlog'	=> $thread
	);

}

?>