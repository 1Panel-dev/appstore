<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: resourcepush.inc.php 34814 2014-08-07 01:46:48Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['adminid'] != 1) {
	showmessage('undefined_action');
}

require_once libfile('function/forum');
require_once libfile('function/post');

$thread = get_thread_by_tid($_GET['tid']);

if(!$thread) {
	showmessage('undefined_action');
}

$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_GET['tid']);
if($thread['cover']) {
	$picurl = getthreadcover($thread['tid'], $thread['cover']);
} else {
	$attach = C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_GET['tid'], 'pid', array($post['pid']), '', true);
	$picurl = '';
	if($attach) {
		$attach = array_shift($attach);
		$picurl = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
	}
}

$data = array(
	'name' => lang('plugin/wechat', 'resource_thread_push').': '.$thread['subject'],
	'data' => array(
	    'title' => $thread['subject'],
	    'pic' => $picurl ? (preg_match('/^https?:/', $picurl) ? '' : $_G['siteurl']).$picurl : '',
	    'desc' => messagecutstr($post['message'], 0, 120),
	    'content' => nl2br(messagecutstr($post['message'])),
	    'url' => $_G['siteurl'].'forum.php?mod=viewthread&tid='.$_GET['tid'],
	),
);
C::t('#wechat#mobile_wechat_resource')->insert($data);

showmessage('wechat:resource_msg_pushed', '', array(), array('alert' => 'right'));