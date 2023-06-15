<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_activity.php 30378 2012-05-24 09:52:46Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$minhot = $_G['setting']['feedhotmin']<1?3:$_G['setting']['feedhotmin'];
$page = empty($_GET['page'])?1:intval($_GET['page']);
if($page<1) $page=1;
$id = empty($_GET['id'])?0:intval($_GET['id']);
$opactives['activity'] = 'class="a"';

if(empty($_GET['view'])) $_GET['view'] = 'we';
$_GET['order'] = empty($_GET['order']) ? 'dateline' : $_GET['order'];

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page-1)*$perpage;
ckstart($start, $perpage);

$list = array();
$userlist = array();
$hiddennum = $count = $pricount = 0;

$gets = array(
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'activity',
	'view' => $_GET['view'],
	'order' => $_GET['order'],
	'type' => $_GET['type'],
	'fuid' => $_GET['fuid'],
	'searchkey' => $_GET['searchkey']
);
$theurl = 'home.php?'.url_implode($gets);
$multi = '';

$wheresql = '1';
$threadsql = $apply_sql = '';

$f_index = '';
$need_count = true;
require_once libfile('function/misc');
if($_GET['view'] == 'me') {
	$viewtype = in_array($_GET['type'], array('orig', 'apply')) ? $_GET['type'] : 'orig';
	$orderactives = array($viewtype => ' class="a"');
} else {

	space_merge($space, 'field_home');

	if($space['feedfriend']) {
		$fuid_actives = array();
		require_once libfile('function/friend');
		$fuid = intval($_GET['fuid']);
		if($fuid && friend_check($fuid, $space['uid'])) {
			$fuid_actives = array($fuid=>' selected');
			$frienduid = $fuid;
		} else {
			$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we";
			$frienduid = explode(',', $space['feedfriend']);
		}

		$query = C::t('home_friend')->fetch_all_by_uid($space['uid'], 0, 100, true);
		foreach($query as $value) {
			$userlist[] = $value;
		}
	} else {
		$need_count = false;
	}
}

$actives = array($_GET['view'] =>' class="a"');

if($need_count) {

	if(!empty($_GET['searchkey'])) {
		$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
	}
	$count = C::t('forum_activity')->fetch_all_for_search($_GET['view'], $_GET['order'], $_GET['searchkey'], $_GET['type'], $frienduid, $space['uid'], $minhot, 1);
	if($count) {
		$query = C::t('forum_activity')->fetch_all_for_search($_GET['view'], $_GET['order'], $_GET['searchkey'], $_GET['type'], $frienduid, $space['uid'], $minhot, 0, $start, $perpage);

		loadcache('forums');
		$daytids = $tids = array();
		foreach($query as $value) {
			if(empty($value['author']) && $value['authorid'] != $_G['uid']) {
				$hiddennum++;
				continue;
			}
			$date = dgmdate($value['starttimefrom'], 'Ymd');
			$posttableid = $value['posttableid'] ? $value['posttableid'] : 0;
			$tids[$posttableid][$value['tid']] = $value['tid'];
			$value['week'] = dgmdate($value['starttimefrom'], 'w');
			$value['month'] = dgmdate($value['starttimefrom'], 'n'.lang('space', 'month'));
			$value['day'] = dgmdate($value['starttimefrom'], 'j');
			$value['time'] = dgmdate($value['starttimefrom'], 'Y'.lang('space', 'year').'m'.lang('space', 'month').'d'.lang('space', 'day'));
			$value['starttimefrom'] = dgmdate($value['starttimefrom']);

			$daytids[$value['tid']] = $date;
			$list[$date][$value['tid']] = procthread($value);
		}
		if($tids) {
			require_once libfile('function/post');
			foreach($tids as $ptid=>$ids) {
				foreach(C::t('forum_post')->fetch_all_by_tid($ptid, $ids, true, '', 0, 0, 1) as $value) {
					$date = $daytids[$value['tid']];
					$value['message'] = messagecutstr($value['message'], 150);
					$list[$date][$value['tid']]['message'] = $value['message'];
				}
			}
		}

		$multi = multi($count, $perpage, $page, $theurl);
	}

}

if($_G['uid']) {
	if($_GET['view'] == 'all') {
		$navtitle = lang('core', 'title_view_all').lang('core', 'title_activity');
	} elseif($_GET['view'] == 'me') {
		$navtitle = lang('core', 'title_my_activity');
	} else {
		$navtitle = lang('core', 'title_friend_activity');
	}
} else {
	if($_GET['order'] == 'hot') {
		$navtitle = lang('core', 'title_top_activity');
	} else {
		$navtitle = lang('core', 'title_newest_activity');
	}
}

include_once template("diy:home/space_activity");

?>