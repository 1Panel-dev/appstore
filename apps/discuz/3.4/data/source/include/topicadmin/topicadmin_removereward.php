<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_removereward.php 31998 2012-10-30 07:17:49Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowremovereward']) {
	showmessage('no_privilege_removereward');
}

if(!submitcheck('modsubmit')) {
	include template('forum/topicadmin_action');
} else {
	if(!is_array($thread) || $thread['special'] != '3') {
		showmessage('reward_end');
	}

	$modaction = 'RMR';
	$reason = checkreasonpm();
	$log = C::t('common_credit_log')->fetch_by_operation_relatedid('RAC', $thread['tid']);
	$answererid = $log['uid'];
	if($thread['price'] < 0) {
		$thread['price'] = abs($thread['price']);
		updatemembercount($answererid, array($_G['setting']['creditstransextra'][2] => -$thread['price']));
	}

	updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']));
	C::t('forum_thread')->update($thread['tid'], array('special'=>0, 'price'=>0), true);

	C::t('common_credit_log')->delete_by_operation_relatedid(array('RTC', 'RAC'), $thread['tid']);
	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$thread[tid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_remove_reward', 'notictype' => 'post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason, 'threadid' => $thread[tid]),
	'modtids'	=> $thread['tid']
	);
}

?>