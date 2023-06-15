<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_printable.php 33149 2013-04-28 01:53:37Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$thisbg = '#FFFFFF';
if(!getstatus($_G['forum_thread']['status'], 2)) {
	$posts = C::t('forum_post')->fetch_all_by_tid('tid:'.$_G['tid'], $_G['tid'], true, 'ASC', 0, 100, null, 0);
} else {
	$posts = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid'], 0);
	$posts = array($posts);
}
$userinfo = $uids = $skipaids = array();
foreach($posts as $post) {

	if(strpos($post['message'], '[/password]') !== FALSE) {
		$post['message'] = '';
	}

	$post['dateline'] = dgmdate($post['dateline'], 'u');
	if(preg_match("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", $post['message'], $hide)) {
		if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $hide[1], $matchaids)) {
			$skipaids = array_merge($skipaids, $matchaids[1]);
		}
		$post['message'] = preg_replace("/\[hide\]\s*(.+?)\s*\[\/hide\]/is", '', $post['message']);
	}
	if(strpos(strtolower($post['message']), '[hide=') !== FALSE) {
		$post['message'] = preg_replace("/\[hide=(\d+)\]\s*(.*?)\s*\[\/hide\]/is", "", $post['message']);
	}
	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0));

	if(strpos($post['message'], '[page]') !== FALSE) {
		$post['message'] = preg_replace("/\s?\[page\]\s?/is", '', $post['message']);
	}
	if(strpos($post['message'], '[/index]') !== FALSE) {
		$post['message'] = preg_replace("/\s?\[index\](.+?)\[\/index\]\s?/is", '', $post['message']);
	}

	if($post['attachment']) {
		$attachment = 1;
	}
	$post['attachments'] = array();
	if($post['attachment'] && ($_G['group']['allowgetattach'] || $_G['group']['allowgetimage'])) {
		$_G['forum_attachpids'][] = $post['pid'];
		$post['attachment'] = 0;
		if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
			$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
		}
	}
	$uids[] = $post['authorid'];
	$postlist[$post['pid']] = $post;
}
unset($posts);
if($uids) {
	$uids = array_unique($uids);
	$userinfo = C::t('common_member')->fetch_all($uids);
}

if($_G['forum_attachpids'] && !defined('IN_ARCHIVER')) {
	require_once libfile('function/attachment');
	if(is_array($threadsortshow) && !empty($threadsortshow['sortaids'])) {
		$skipaids = array_merge($skipaids, $threadsortshow['sortaids']);
	}
	parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}

include template('forum/viewthread_printable');

?>