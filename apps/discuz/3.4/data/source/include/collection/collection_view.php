<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: collection_view.php 33065 2013-04-16 10:06:07Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$oplist = array('comment', 'followers', 'related');
if(!in_array($op, $oplist)) {
	$op = '';
}
$fromoplist = array('my', 'all');
$fromop = (!in_array($_GET['fromop'], $fromoplist)) ? '' : $_GET['fromop'];
$fromtid = dintval($_GET['fromtid']);
$tids = $fids = array();

if(!$_G['collection']['ctid']) {
	showmessage('collection_permission_deny');
}

$navtitle = $_G['collection']['name'].' - '.lang('core', 'title_collection');

$permission = checkcollectionperm($_G['collection'], $_G['uid']);
$avgrate = number_format($_G['collection']['rate'], 1);

$start = ($page-1)*$tpp;

$collectionfollowdata = C::t('forum_collectionfollow')->fetch_by_ctid_uid($ctid, $_G['uid']);
$collectionteamworker = C::t('forum_collectionteamworker')->fetch_all_by_ctid($_G['collection']['ctid']);

$_G['collection']['arraykeyword'] = parse_keyword($_G['collection']['keyword'], false, false);
if($_G['collection']['arraykeyword']) {
	foreach ($_G['collection']['arraykeyword'] as $kid=>$s_keyword) {
		$metakeywords .= ($metakeywords ? ',' : '').$s_keyword;
		$_G['collection']['urlkeyword'][$kid] = rawurlencode($s_keyword);
	}
}
$metadescription = $_G['collection']['name'];

if($_G['collection']['ratenum']) {
	$star = imgdisplayrate($avgrate);
}

if(!$op || $op == 'related') {
	$isteamworkers = in_array($_G['uid'], array_keys($collectionteamworker));

	$search_status = FALSE;
	require_once libfile('function/friend');
	$friendgrouplist = friend_group_list();

	if(!$op && $op != 'related') {
		if($_G['collection']['uid'] == $_G['uid']) {
			$lastvisit = $_G['collection']['lastvisit'];
			if($_G['collection']['lastupdate'] >= $lastvisit) {
				C::t('forum_collection')->update($ctid, array('lastvisit' => TIMESTAMP), true, true);
			}
		} elseif($isteamworkers) {
			$lastvisit = $collectionteamworker[$_G['uid']]['lastvisit'];
			if($_G['collection']['lastupdate'] >= $lastvisit) {
				C::t('forum_collectionteamworker')->update($ctid, $_G['uid'], array('lastvisit' => TIMESTAMP), true, true);
			}
		} elseif($collectionfollowdata['ctid']) {
			$lastvisit = $collectionfollowdata['lastvisit'];
			if($_G['collection']['lastupdate'] >= $lastvisit) {
				C::t('forum_collectionfollow')->update($ctid, $_G['uid'], array('lastvisit' => TIMESTAMP), true, true);
			}
		} else {
			$lastvisit = null;
		}

		$collectiontids = C::t('forum_collectionthread')->fetch_all_by_ctid($_G['collection']['ctid'], $start, $tpp);
		$tids = array_keys($collectiontids);
		$threadlist = C::t('forum_thread')->fetch_all_by_tid($tids);
		collectionThread($threadlist, false, $lastvisit, $collectiontids);

		$multipage = multi($_G['collection']['threadnum'], $tpp, $page, "forum.php?mod=collection&action=view&ctid={$_G['collection']['ctid']}");

		$userCollections = C::t('forum_collection')->fetch_all_by_uid($_G['collection']['uid'], 0, 5, $_G['collection']['ctid']);
	}

	if($_G['collection']['commentnum'] > 0) {
		$commentlist = C::t('forum_collectioncomment')->fetch_all_by_ctid($_G['collection']['ctid'], 0, 5);
		foreach($commentlist as &$curvalue) {
			$curvalue['dateline'] = dgmdate($curvalue['dateline'], 'u', '9999', getglobal('setting/dateformat'));
			$curvalue['message'] = cutstr($curvalue['message'], 50);
			$curvalue['rateimg'] = imgdisplayrate($curvalue['rate']);
		}

		$memberrate = C::t('forum_collectioncomment')->fetch_rate_by_ctid_uid($_G['collection']['ctid'], $_G['uid']);
	}
	$followers = C::t('forum_collectionfollow')->fetch_all($ctid, true, 0, 6);

	include template('forum/collection_view');
} elseif($op == 'comment') {
	$navtitle = lang('core', 'title_collection_comment_list').' - '.$navtitle;
	if($_G['collection']['commentnum'] > 0) {
		$start = ($page-1)*$_G['setting']['postperpage'];

		$commentlist = C::t('forum_collectioncomment')->fetch_all_by_ctid($_G['collection']['ctid'], $start, $_G['setting']['postperpage']);
		foreach($commentlist as &$curvalue) {
			$curvalue['dateline'] = dgmdate($curvalue['dateline'], 'u', '9999', getglobal('setting/dateformat'));
			$curvalue['rateimg'] = imgdisplayrate($curvalue['rate']);
		}

		$multipage = multi($_G['collection']['commentnum'], $_G['setting']['postperpage'], $page, "forum.php?mod=collection&action=view&op=comment&ctid={$_G['collection']['ctid']}");

		$memberrate = C::t('forum_collectioncomment')->fetch_rate_by_ctid_uid($_G['collection']['ctid'], $_G['uid']);
	}
	include template('forum/collection_comment');
} elseif($op == 'followers') {
	$navtitle = lang('core', 'title_collection_followers_list').' - '.$navtitle;
	$cmemberperpage = 28;
	$start = ($page-1)*$cmemberperpage;
	$followers = C::t('forum_collectionfollow')->fetch_all($ctid, true, $start, $cmemberperpage);
	$multipage = multi($_G['collection']['follownum'], $cmemberperpage, $page, "forum.php?mod=collection&action=view&op=followers&ctid={$_G['collection']['ctid']}");

	include template('forum/collection_followers');
}
?>