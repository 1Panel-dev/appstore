<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: collection_index.php 33200 2013-05-06 12:27:49Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = lang('core', 'title_collection');
$searchtitle = '';
$oplist = array('all', 'my', 'search');
if(!in_array($op, $oplist)) {
	$op = '';
}

$cpp = 20;
$start = ($page-1)*$cpp;

if($op == 'all' || $op == 'search') {
	if($op == 'search' && $_GET['kw']) {
		dheader('Location: search.php?mod=collection&searchsubmit=yes&srchtxt='.urlencode($_GET['kw']));
		exit;
	} else {
		$orderbyarr = array('follownum', 'threadnum', 'commentnum', 'dateline');
		$count = C::t('forum_collection')->count();
	}

	$orderby = (in_array($_GET['order'], $orderbyarr)) ? $_GET['order'] : 'dateline';
	$collectiondata = processCollectionData(C::t('forum_collection')->fetch_all('', $orderby, 'DESC', $start, $cpp, $searchtitle), array(), $orderby);
	$htmlsearchtitle = dhtmlspecialchars($searchtitle);
	$multipage = multi($count, $cpp, $page, 'forum.php?mod=collection&order='.$orderby.'&op='.$op.(($htmlsearchtitle) ? '&kw='.$htmlsearchtitle : ''));

	include template('forum/collection_all');
} elseif ($op == 'my') {
	$mycollection = C::t('forum_collection')->fetch_all_by_uid($_G['uid']);
	$myctid = array_keys($mycollection);
	$teamworker = C::t('forum_collectionteamworker')->fetch_all_by_uid($_G['uid']);
	$twctid = array_keys($teamworker);
	$follow = C::t('forum_collectionfollow')->fetch_all_by_uid($_G['uid']);
	if(empty($follow)) {
		$follow = array();
	}
	$followctid = array_keys($follow);

	if(!$myctid) {
		$myctid = array();
	}
	if(!$twctid) {
		$twctid = array();
	}
	if(!$followctid) {
		$followctid = array();
	}

	$ctidlist = array_merge($myctid, $twctid, $followctid);

	if(count($ctidlist) > 0) {
		$tfcollection = $mycollection + $teamworker + $follow;
		$collectiondata = C::t('forum_collection')->fetch_all($ctidlist, 'dateline', 'DESC');
		$collectiondata = processCollectionData($collectiondata, $tfcollection);
	}

	include template('forum/collection_mycollection');
} else {
	if(!$tid) {
		$collectiondata = array();
		loadcache('collection');

		if(TIMESTAMP - $_G['cache']['collection']['dateline'] > 300) {
			$collection = getHotCollection(500, false);
			$collectioncache = array('dateline' => TIMESTAMP, 'data' => $collection);
			savecache('collection', $collectioncache);
		} else {
			$collection = &$_G['cache']['collection']['data'];
		}
		$count = count($collection);
		for($i = $start; $i < $start+$cpp; $i++) {
			if(!$collection[$i]) {
				continue;
			}
			$collectiondata[] = $collection[$i];
		}
		unset($collection);
		$collectiondata = processCollectionData($collectiondata);
	} else {
		$tidrelate = C::t('forum_collectionrelated')->fetch($tid);
		$ctids = explode("\t", $tidrelate['collection'], -1);
		$count = count($ctids);
		$collectiondata = C::t('forum_collection')->fetch_all($ctids, 'follownum', 'DESC', $start, $cpp);
		$collectiondata = processCollectionData($collectiondata);
	}

	$multipage = multi($count, $cpp, $page, 'forum.php?mod=collection'.($tid ? '&tid='.$tid : ''));

	include template('forum/collection_index');
}


?>