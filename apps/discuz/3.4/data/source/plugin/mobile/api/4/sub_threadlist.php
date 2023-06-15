<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sub_threadlist.php 35068 2014-11-04 02:37:45Z nemohou $
 */

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

$tids = array();
foreach ($_G['forum_threadlist'] as $k => $thread) {
	$tids[] = $_G['forum_threadlist'][$k]['tid'] = $thread['icontid'];
	$_G['forum_threadlist'][$k]['cover'] = array();
	if ($thread['cover']) {
		$_G['forum_threadlist'][$k]['cover'] = array('w' => 200, 'h' => 200);
	}

	$_G['forum_threadlist'][$k]['reply'] = array();
	if(!isset($_G['wechat']['setting']['wechat_forumdisplay_reply']) || $_G['wechat']['setting']['wechat_forumdisplay_reply']) {
		$key = C::t('#mobile#mobile_wsq_threadlist')->fetch($thread['tid']);
		if ($key['svalue']) {
			$_G['forum_threadlist'][$k]['reply'] = dunserialize($key['svalue']);
		}
	}
	$_G['forum_threadlist'][$k]['dateline'] = strip_tags($thread['dateline']);
	$_G['forum_threadlist'][$k]['lastpost'] = strip_tags($thread['lastpost']);
	if(!$thread['authorid'] || !$thread['author']) {
		$_G['forum_threadlist'][$k]['author'] = $_G['setting']['anonymoustext'];
		$_G['forum_threadlist'][$k]['authorid'] = 0;
	}
	$userids[] = $thread['authorid'];
}

foreach(C::t('common_member')->fetch_all($userids) as $user) {
	$groupiconIds[$user['uid']] = mobile_core::usergroupIconId($user['groupid']);
}

if($_G['uid']) {
	$memberrecommends = array();
	$query = DB::query('SELECT * FROM %t WHERE recommenduid=%d AND tid IN (%n)', array('forum_memberrecommend', $_G['uid'], $tids));
	while ($memberrecommend = DB::fetch($query)) {
		$memberrecommends[$memberrecommend['tid']] = 1;
	}
	foreach ($_G['forum_threadlist'] as $k => $thread) {
		$_G['forum_threadlist'][$k]['recommend'] = isset($memberrecommends[$thread['icontid']]) ? 1 : 0;
	}
}

foreach ($GLOBALS['sublist'] as $k => $sublist) {
	if ($sublist['icon']) {
		$icon = preg_match('/src="(.+?)"/', $sublist['icon'], $r) ? $r[1] : '';
		if (!preg_match('/^https?:\//', $icon)) {
			$icon = $_G['siteurl'] . $icon;
		}
		$GLOBALS['sublist'][$k]['icon'] = $icon;
	}
}

if($_G['forum']['icon']) {
	require_once libfile('function/forumlist');
	if(!preg_match('/^https?:\/\//i', $_G['forum']['icon'])) {
		$_G['forum']['icon'] = get_forumimg($_G['forum']['icon']);
		if(!preg_match('/^https?:\/\//i', $_G['forum']['icon'])) {
			$_G['forum']['icon'] = $_G['siteurl'] . $_G['forum']['icon'];
		}
	}
}

$_G['forum']['threadcount'] = $_G['forum_threadcount'];

$variable = array(
    'forum' => mobile_core::getvalues($_G['forum'], array('fid', 'fup', 'name', 'threads', 'posts', 'rules', 'autoclose', 'password', 'icon', 'threadcount', 'picstyle', 'description')),
    'group' => mobile_core::getvalues($_G['group'], array('groupid', 'grouptitle')),
    'forum_threadlist' => mobile_core::getvalues(array_values($_G['forum_threadlist']), array('/^\d+$/'), array('tid', 'author', 'special', 'authorid', 'subject', 'subject', 'dbdateline', 'dateline', 'dblastpost', 'lastpost', 'lastposter', 'attachment', 'replies', 'readperm', 'views', 'digest', 'cover', 'recommend', 'recommend_add', 'reply', 'avatar', 'displayorder', 'coverpath', 'typeid', 'rushreply', 'replycredit', 'price')),
    'groupiconid' => $groupiconIds,
    'sublist' => mobile_core::getvalues($GLOBALS['sublist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'todayposts', 'posts', 'icon')),
    'tpp' => $_G['tpp'],
    'page' => $GLOBALS['page'],
    'reward_unit' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'],
);
if (!empty($_G['forum']['threadtypes']) || !empty($_GET['debug'])) {
	$variable['threadtypes'] = $_G['forum']['threadtypes'];
}
if (!empty($_G['forum']['threadsorts']) || !empty($_GET['debug'])) {
	$variable['threadsorts'] = $_G['forum']['threadsorts'];
}
$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';

?>