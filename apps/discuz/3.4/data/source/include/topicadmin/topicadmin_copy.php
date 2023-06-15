<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: topicadmin_copy.php 31594 2012-09-12 04:14:54Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['group']['allowcopythread'] || !$thread) {
	showmessage('no_privilege_copythread');
}

if(!submitcheck('modsubmit')) {
	require_once libfile('function/forumlist');
	$forumselect = forumselect();
	include template('forum/topicadmin_action');

} else {

	$modaction = 'CPY';
	$reason = checkreasonpm();
	$copyto = $_GET['copyto'];
	$toforum = C::t('forum_forum')->fetch_info_by_fid($copyto);
	if(!$toforum || $toforum['status'] != 1 || $toforum['type'] == 'group') {
		showmessage('admin_copy_invalid');
	} else {
		$modnewthreads = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 1) && $toforum['modnewposts'] ? 1 : 0;
		$modnewreplies = (!$_G['group']['allowdirectpost'] || $_G['group']['allowdirectpost'] == 2) && $toforum['modnewposts'] ? 1 : 0;
		if($modnewthreads || $modnewreplies) {
			showmessage('admin_copy_hava_mod');
		}
	}
	$toforum['threadsorts_arr'] = unserialize($toforum['threadsorts']);

	if($thread['sortid'] != 0 && $toforum['threadsorts_arr']['types'][$thread['sortid']]) {
		foreach(C::t('forum_typeoptionvar')->fetch_all_by_search($thread['sortid'], null, $thread['tid']) as $result) {
			$typeoptionvar[] = $result;
		}
	} else {
		$thread['sortid'] = '';
	}

	$sourcetid = $thread['tid'];
	unset($thread['tid']);
	$thread['fid'] = $copyto;
	$thread['dateline'] = $thread['lastpost'] = TIMESTAMP;
	$thread['lastposter'] = $thread['author'];
	$thread['views'] = $thread['replies'] = $thread['highlight'] = $thread['digest'] = 0;
	$thread['rate'] = $thread['displayorder'] = $thread['attachment'] = 0;
	$thread['typeid'] = $_GET['threadtypeid'];
	$thread = daddslashes($thread);

	$thread['posttableid'] = 0;
	$threadid = C::t('forum_thread')->insert($thread, true);
	C::t('forum_newthread')->insert(array(
		'tid' => $threadid,
		'fid' => $thread['fid'],
		'dateline' => $thread['dateline'],
	));
	C::t('forum_sofa')->insert(array('tid' => $threadid,'fid' => $thread['fid']));
	if($post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid'])) {
		$post['pid'] = '';
		$post['tid'] = $threadid;
		$post['fid'] = $copyto;
		$post['dateline'] = $thread['dateline'];
		$post['attachment'] = 0;
		$post['invisible'] = $post['rate'] = $post['ratetimes'] = 0;
		$post['message'] .= "\n".lang('forum/thread', 'source').": [url=forum.php?mod=viewthread&tid={$sourcetid}]{$thread['subject']}[/url]";
		$post = daddslashes($post);
		$pid = insertpost($post);
	}

	$class_tag = new tag();
	$class_tag->copy_tag($_G['tid'], $threadid, 'tid');

	if($typeoptionvar) {
		foreach($typeoptionvar AS $key => $value) {
			$value['tid'] = $threadid;
			$value['fid'] = $toforum['fid'];
			C::t('forum_typeoptionvar')->insert($value);
		}
	}
	updatepostcredits('+', $post['authorid'], 'post', $copyto);

	updateforumcount($copyto);
	updateforumcount($_G['fid']);

	$modpostsnum ++;
	$resultarray = array(
	'redirect'	=> "forum.php?mod=forumdisplay&fid=$_G[fid]",
	'reasonpm'	=> ($sendreasonpm ? array('data' => array($thread), 'var' => 'thread', 'item' => 'reason_copy', 'notictype' => 'post') : array()),
	'reasonvar'	=> array('tid' => $thread['tid'], 'subject' => $thread['subject'], 'modaction' => $modaction, 'reason' => $reason, 'threadid' => $threadid),
	'modtids'	=> $thread['tid'],
	'modlog'	=> array($thread, $other)
	);
}

?>