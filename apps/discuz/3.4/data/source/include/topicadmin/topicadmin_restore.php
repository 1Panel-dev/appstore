<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_restore.php 27088 2012-01-05 02:36:48Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['adminid'] != '1') {
	showmessage('no_privilege_restore');
}
$archiveid = intval($_GET['archiveid']);
if(!submitcheck('modsubmit')) {
	include template('forum/topicadmin_action');
} else {
	if(!in_array($archiveid, $threadtableids)) {
		$archiveid = 0;
	}
	C::t('forum_thread')->insert_thread_copy_by_tid($_G['tid'], $archiveid, 0);
	C::t('forum_thread')->delete_by_tid($_G['tid'], false, $archiveid);

	$threadcount = C::t('forum_thread')->count_by_fid($_G['fid'], $archiveid);
	if($threadcount) {
		C::t('forum_forum_threadtable')->update($_G['fid'], $archiveid, array('threads' => $threadcount));
	} else {
		C::t('forum_forum_threadtable')->delete($_G['fid'], $archiveid);
	}
	if(!C::t('forum_forum_threadtable')->count_by_fid($_G['fid'])) {
		C::t('forum_forum')->update($_G['fid'], array('archive' => 0));
	}
	$modaction = 'RST';
	$reason = checkreasonpm();
	$resultarray = array(
		'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
		'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread') : array()),
		'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason),
		'modaction'	=> $modaction,
		'modlog'	=> $thread
	);
}

?>