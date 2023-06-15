<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_friend.php 32006 2012-10-30 09:51:28Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$perpage = 24;
$perpage = mob_perpage($perpage);

$list = $ols = $fuids = array();
$count = 0;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

if(empty($_GET['view']) || $_GET['view'] == 'all') $_GET['view'] = 'me';

ckstart($start, $perpage);

if($_GET['view'] == 'online') {
	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online";
	$actives = array('me'=>' class="a"');

	space_merge($space, 'field_home');
	$onlinedata = array();
	$wheresql = '';
	if($_GET['type']=='near') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=near";
		if(($count = C::app()->session->count_by_ip($_G['clientip']))) {
			$onlinedata = C::app()->session->fetch_all_by_ip($_G['clientip'], $start, $perpage);
		}
	} elseif($_GET['type']=='friend') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=friend";
		if(!empty($space['feedfriend'])) {
			$onlinedata = C::app()->session->fetch_all_by_uid(explode(',', $space['feedfriend']), $start, $perpage);
		}
		$count = count($onlinedata);
	} elseif($_GET['type']=='member') {
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=member";
		$wheresql = " WHERE uid>0";
		if(($count = C::app()->session->count(1))) {
			$onlinedata = C::app()->session->fetch_member(1, 2, $start, $perpage);
		}
	} else {
		$_GET['type']='all';
		$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=online&type=all";
		if(($count = C::app()->session->count_invisible(0))) {
			$onlinedata = C::app()->session->fetch_member(0, 2, $start, $perpage);
		}
	}

	if($count) {

		foreach($onlinedata as $value) {
			if($_GET['type']=='near') {
				if($value['uid'] == $space['uid']) {
					$count = $count-1;
					continue;
				}
			}

			if(!$value['invisible']) $ols[$value['uid']] = $value['lastactivity'];
			$list[$value['uid']] = $value;
			$fuids[$value['uid']] = $value['uid'];
		}

		if($fuids) {
			require_once libfile('function/friend');
			friend_check($space['uid'], $fuids);

			$fieldhome = C::t('common_member_field_home')->fetch_all($fuids);
			foreach(C::t('common_member')->fetch_all($fuids) as $uid => $value) {
				$value = array_merge($value, $fieldhome[$uid]);
				$value['isfriend'] = $uid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$uid] ? 1 : 0;
				$list[$uid] = array_merge($list[$uid], $value);
			}
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'visitor' || $_GET['view'] == 'trace') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	if($_GET['view'] == 'visitor') {
		$count = C::t('home_visitor')->count_by_uid($space['uid']);
	} else {
		$count = C::t('home_visitor')->count_by_vuid($space['uid']);
	}
	if($count) {
		if($_GET['view'] == 'visitor') {
			$visitors = C::t('home_visitor')->fetch_all_by_uid($space['uid'], $start, $perpage);
		} else {
			$visitors = C::t('home_visitor')->fetch_all_by_vuid($space['uid'], $start, $perpage);
		}
		foreach($visitors as $value) {
			if($_GET['view'] == 'visitor') {
				$value['uid'] = $value['vuid'];
				$value['username'] = $value['vusername'];
			}
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} elseif($_GET['view'] == 'blacklist') {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array('me'=>' class="a"');

	$count = C::t('home_blacklist')->count_by_uid_buid($space['uid']);
	if($count) {
		$backlist = C::t('home_blacklist')->fetch_all_by_uid($space['uid'], $start,$perpage);
		$members = C::t('common_member')->fetch_all(array_keys($backlist));
		foreach($backlist as $buid => $value) {
			$value = array_merge($value, $members[$buid]);
			$value['isfriend'] = 0;
			$fuids[] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	}
	$multi = multi($count, $perpage, $page, $theurl);

} else {

	$theurl = "home.php?mod=space&uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="a"');

	$_GET['view'] = 'me';

	$querydata = array();
	if($space['self']) {
		require_once libfile('function/friend');
		$groups = friend_group_list();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		if($group > -1) {
			$querydata['gid'] = $group;
			$theurl .= "&group=$group";
		}
	}
	if($_GET['searchkey']) {
		require_once libfile('function/search');
		$querydata['searchkey'] = $_GET['searchkey'];
		$theurl .= "&searchkey=$_GET[searchkey]";
	}

	$count = C::t('home_friend')->fetch_all_search($space['uid'], $querydata['gid'], $querydata['searchkey'], true);
	$membercount = C::t('common_member_count')->fetch($_G['uid']);
	$friendnum = $membercount['friends'];
	unset($membercount);
	if($count) {

		$query = C::t('home_friend')->fetch_all_search($space['uid'], $querydata['gid'], $querydata['searchkey'], false, $start, $perpage, $_GET['order'] ? true : false);
		foreach($query as $value) {
			$value['uid'] = $value['fuid'];
			$_G["home_friend_".$space['uid'].'_'.$value['uid']] = $value['isfriend'] = 1;
			$fuids[$value['uid']] = $value['uid'];
			$list[$value['uid']] = $value;
		}
	} elseif(!$friendnum) {
		if(($specialuser_count = C::t('home_specialuser')->count_by_status(1))) {
			foreach(C::t('home_specialuser')->fetch_all_by_status(1, 7) as $value) {
				if($_G['uid'] !== $value['uid']) {
					$fuids[$value['uid']] = $value['uid'];
					$specialuser_list[$value['uid']] = $value;
				}
				if(count($fuids) >= 6) {
					break;
				}
			}
			$specialuser_list = getfollowflag($specialuser_list);

		}
		if(($online_count = C::app()->session->count(1)) > 1) {
			$oluids = $online_list = array();
			foreach(C::app()->session->fetch_member(1, 2, 7) as $value) {
				if($value['uid'] != $_G['uid'] && count($oluids) <= 6) {
					$fuids[$value['uid']] = $value['uid'];
					$oluids[$value['uid']] = $value['uid'];
					$online_list[$value['uid']] = $value;
				}
			}
			$online_list = getfollowflag($online_list);

			$fieldhome = C::t('common_member_field_home')->fetch_all($oluids, false, 0);
			foreach(C::t('common_member')->fetch_all($oluids, false, 0) as $uid => $value) {
				$value = array_merge($value, $fieldhome[$uid]);
				$online_list[$uid] = array_merge($online_list[$uid], $value);
			}

		}
	}

	$diymode = 1;
	if($space['self'] && ($_GET['from'] != 'space' || !$_G['setting']['homepagestyle'])) $diymode = 0;
	if($diymode) {
		$theurl .= "&from=space";
	}

	$multi = multi($count, $perpage, $page, $theurl);

	if($space['self']) {
		$groupselect = array($group => ' class="a"');

		$maxfriendnum = checkperm('maxfriendnum');
		if($maxfriendnum) {
			$maxfriendnum = checkperm('maxfriendnum') + $space['addfriend'];
		}
	}
}

if($fuids) {
	foreach(C::app()->session->fetch_all_by_uid($fuids) as $value) {
		if(!$value['invisible']) {
			$ols[$value['uid']] = $value['lastactivity'];
		} elseif($list[$value['uid']] && !in_array($_GET['view'], array('me', 'trace', 'blacklist'))) {
			unset($list[$value['uid']]);
			$count = $count - 1;
		}
	}
	if($_GET['view'] != 'me') {
		require_once libfile('function/friend');
		friend_check($fuids);
	}
	if($list) {
		$fieldhome = C::t('common_member_field_home')->fetch_all($fuids);
		foreach(C::t('common_member')->fetch_all($fuids) as $uid => $value) {
			$value = array_merge($value, $fieldhome[$uid]);
			$value['isfriend'] = $uid==$space['uid'] || $_G["home_friend_".$space['uid'].'_'.$uid] ? 1 : 0;
			if(empty($list[$uid])) $list[$uid] = array();
			$list[$uid] = array_merge($list[$uid], $value);
		}
	}
}
if($list) {
	$list = getfollowflag($list);
}
$navtitle = lang('core', 'title_friend_list');

$navtitle = lang('space', 'sb_friend', array('who' => $space['username']));
$metakeywords = lang('space', 'sb_friend', array('who' => $space['username']));
$metadescription = lang('space', 'sb_share', array('who' => $space['username']));

$a_actives = array($_GET['view'].$_GET['type'] => ' class="a"');
include_once template("diy:home/space_friend");

function getfollowflag($data) {
	global $_G;
	if($data) {
		$follows = C::t('home_follow')->fetch_all_by_uid_followuid($_G['uid'], array_keys($data));
		foreach($data as $uid => $value) {
			$data[$uid]['follow'] = isset($follows[$uid]) ? 1 : 0;
		}
	}
	return $data;
}
?>