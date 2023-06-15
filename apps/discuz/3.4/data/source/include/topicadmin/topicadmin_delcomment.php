<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_delcomment.php 31950 2012-10-25 09:05:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowdelpost'] || empty($_GET['topiclist'])) {
	showmessage('no_privilege_delcomment');
}

if(!submitcheck('modsubmit')) {

	$commentid = $_GET['topiclist'][0];
	$pid = C::t('forum_postcomment')->fetch($commentid);
	$pid = $pid['pid'];
	if(!$pid) {
		showmessage('postcomment_not_found');
	}
	$deleteid = '<input type="hidden" name="topiclist" value="'.$commentid.'" />';

	include template('forum/topicadmin_action');

} else {

	$reason = checkreasonpm();
	$modaction = 'DCM';

	$commentid = intval($_GET['topiclist']);
	$postcomment = C::t('forum_postcomment')->fetch($commentid);
	if(!$postcomment) {
		showmessage('postcomment_not_found');
	}
	C::t('forum_postcomment')->delete($commentid);
	$result = C::t('forum_postcomment')->count_by_pid($postcomment['pid']);
	if(!$result) {
		C::t('forum_post')->update($_G['thread']['posttableid'], $postcomment['pid'], array('comment' => 0));
	}
	if($thread['comments']) {
		C::t('forum_thread')->update($_G['tid'], array('comments' => $thread['comments'] - 1));
	}
	if(!$postcomment['rpid']) {
		updatepostcredits('-', $postcomment['authorid'], 'reply', $_G['fid']);
	}

	$totalcomment = array();
	foreach(C::t('forum_postcomment')->fetch_all_by_pid_score($postcomment['pid'], 1) as $comment) {
		if(strexists($comment['comment'], '<br />')) {
			if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
				foreach($a[1] as $k => $itemk) {
					$totalcomment[trim($itemk)][] = $a[2][$k];
				}
			}
		}
	}
	$totalv = '';
	foreach($totalcomment as $itemk => $itemv) {
		$totalv .= strip_tags(trim($itemk)).': <i>'.(sprintf('%1.1f', array_sum($itemv) / count($itemv))).'</i> ';
	}

	if($totalv) {
		C::t('forum_postcomment')->update_by_pid($postcomment['pid'], array('comment' => $totalv, 'dateline' => TIMESTAMP + 1), false, false, 0);
	} else {
		C::t('forum_postcomment')->delete_by_pid($postcomment['pid'], false, 0);
	}
	C::t('forum_postcache')->delete($postcomment['pid']);

	$resultarray = array(
	'redirect'	=> "forum.php?mod=viewthread&tid=$_G[tid]&page=$page",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($postcomment), 'var' => 'post', 'item' => 'reason_delete_comment', 'notictype' => 'pcomment') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'pid' => $postcomment['pid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason),
	'modtids'	=> 0,
	'modlog'	=> $thread
	);

}

?>