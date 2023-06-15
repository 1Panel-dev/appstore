<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_doing.php 29155 2012-03-27 10:39:12Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 20;
$perpage = mob_perpage($perpage);

$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

ckstart($start, $perpage);

$dolist = array();
$count = 0;

if(empty($_GET['view'])) {
	$_GET['view'] = 'we';
}

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'doing',
	'view' => $_GET['view'],
	'searchkey' => $_GET['searchkey'],
	'from' => $_GET['from']
);
$theurl = 'home.php?'.url_implode($gets);

$f_index = '';
$diymode = 0;
if($_GET['view'] == 'all') {

	$f_index = 'dateline';

} elseif($_GET['view'] == 'we') {

	space_merge($space, 'field_home');
	if($space['feedfriend']) {
		$uids = array_merge(explode(',', $space['feedfriend']), array($space['uid']));
		$f_index = 'dateline';
	} else {
		$uids = array($space['uid']);
	}

} else {

	if($_GET['from'] == 'space') $diymode = 1;

	$uids = $_GET['highlight'] ? array() : array($space['uid']);
}
$actives = array($_GET['view'] =>' class="a"');

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$doids = $clist = $newdoids = array();
$pricount = 0;

if($doid) {
	$count = 1;
	$f_index = '';
	$theurl .= "&doid=$doid";
}

if($searchkey = stripsearchkey($_GET['searchkey'])) {
	$searchkey = dhtmlspecialchars($searchkey);
}

if(empty($count)) {
	$count = C::t('home_doing')->fetch_all_search($start, $perpage, 3, $uids, '', $searchkey, '', '' ,'', 1, $doid, $f_index);
}
if($count) {
	$query = C::t('home_doing')->fetch_all_search($start, $perpage, 1, $uids, '', $searchkey, '', '' ,'', 1, $doid, $f_index);
	foreach($query as $value) {
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$doids[] = $value['doid'];
			$dolist[] = $value;
		} else {
			$pricount ++;
		}
	}
}

if($doid) {
	$dovalue = empty($dolist)?array():$dolist[0];
	if($dovalue) {
		if($dovalue['uid'] == $_G['uid']) {
			$actives = array('me'=>' class="a"');
		} else {
			$actives = array('all'=>' class="a"');
		}
	}
}


if($doids) {

	$tree = new lib_tree();

	$values = array();
	foreach(C::t('home_docomment')->fetch_all_by_doid($doids) as $value) {
		$newdoids[$value['doid']] = $value['doid'];
		if(empty($value['upid'])) {
			$value['upid'] = "do$value[doid]";
		}
		$tree->setNode($value['id'], $value['upid'], $value);
	}
}

$showdoinglist = array();
foreach ($newdoids as $cdoid) {
	$values = $tree->getChilds("do$cdoid");
	$show = false;
	foreach ($values as $key => $id) {
		$one = $tree->getValue($id);
		$one['layer'] = $tree->getLayer($id) * 2 - 2;
		$one['style'] = "padding-left:{$one['layer']}em;";
		if($_GET['highlight'] && $one['id'] == $_GET['highlight']) {
			$one['style'] .= 'color:#F60;';
		}
		if($one['layer'] > 0){
			if($one['layer']%3 == 2) {
				$one['class'] = ' dtls';
			} else {
				$one['class'] = ' dtll';
			}
		}
		if(!$show && $one['uid']) {
			$show = true;
		}
		$clist[$cdoid][] = $one;
	}
	$showdoinglist[$cdoid] = $show;
}

$multi = multi($count, $perpage, $page, $theurl);

dsetcookie('home_diymode', $diymode);
if($_G['uid']) {
	if($_GET['view'] == 'all') {
		$navtitle = lang('core', 'title_view_all').lang('core', 'title_doing');
	} elseif($_GET['view'] == 'me') {
		$navtitle = lang('core', 'title_doing_view_me');
	} else {
		$navtitle = lang('core', 'title_me_friend_doing');
	}
	$defaultstr = getdefaultdoing();
} else {
	$navtitle = lang('core', 'title_newest_doing');
}

if($space['username']) {
	$navtitle = lang('space', 'sb_doing', array('who' => $space['username']));
}
$metakeywords = $navtitle;
$metadescription = $navtitle;
include_once template('diy:home/space_doing');

?>