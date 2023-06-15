<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ajax.php 26657 2011-12-19 04:13:43Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];

if($op == 'comment') {

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);

	if($cid) {
		$ajax_edit = 1;
	} else {
		$ajax_edit = 0;
	}

	$list = array();
	$value = C::t('home_comment')->fetch_latest_by_authorid($_G['uid'], $cid);
	$list[] = $value;



} elseif($op == 'getfriendgroup') {

	$uid = intval($_GET['uid']);
	if($_G['uid'] && $uid) {
		$space = getuserbyuid($_G['uid']);
		$query = C::t('home_friend')->fetch_all_by_uid_fuid($_G['uid'], $uid);
		$value = $query[0];
	}

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if(empty($value['gid'])) $value['gid'] = 0;
	$group =$groups[$value['gid']];

} elseif($op == 'getfriendname') {

	$groupname = '';
	$group = intval($_GET['group']);

	if($_G['uid'] && $group) {
		require_once libfile('function/friend');
		$groups = friend_group_list();
		$groupname = $groups[$group];
	}

} elseif($op == 'share') {

	require_once libfile('function/share');

	$list = array();
	foreach(C::t('home_share')->fetch_all_by_uid($_G['uid'], 0, 1) as $value) {
		$value = mkshare($value);
		$ajax_edit = 1;
		$list[] = $value;
	}

} elseif($op == 'album') {

	$id = empty($_GET['id'])?0:intval($_GET['id']);

	$perpage = 10;
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	if(empty($_G['uid'])) {
		showmessage('to_login', null, array(), array('showmsg' => true, 'login' => 1));
	}

	$count = C::t('home_pic')->check_albumpic($id, NULL, $_G['uid']);
	$piclist = array();
	$multi = '';
	if($count) {
		$query = C::t('home_pic')->fetch_all_by_albumid($id, $start, $perpage, 0, 0, 1, $_G['uid']);
		foreach($query as $value) {
			$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$piclist[] = $value;
		}
		$multi = multi($count, $perpage, $page, "home.php?mod=misc&ac=ajax&op=album&id=$id");
	}

} elseif($op == 'docomment') {

	$doid = intval($_GET['doid']);
	$clist = $do = array();
	$icon = $_GET['icon'] == 'plus' ? 'minus' : 'plus';
	if($doid) {
		if($value = C::t('home_doing')->fetch($doid)) {
			$value['icon'] = 'plus';
			if($value['replynum'] > 0 && ($value['replynum'] < 20 || $doid == $value['doid'])) {
				$doids[] = $value['doid'];
				$value['icon'] = 'minus';
			} elseif($value['replynum']<1) {
				$value['icon'] = 'minus';
			}
			$value['id'] = 0;
			$value['layer'] = 0;
			$clist[] = $value;
		}
	}

	if($_GET['icon'] == 'plus' && $value['replynum']) {

		$tree = new lib_tree();

		foreach(C::t('home_docomment')->fetch_all_by_doid($doid) as $value) {

			if(empty($value['upid'])) {
				$value['upid'] = "do";
			}
			$tree->setNode($value['id'], $value['upid'], $value);
		}

		$values = $tree->getChilds("do");
		foreach ($values as $key => $id) {
			$one = $tree->getValue($id);
			$one['layer'] = $tree->getLayer($id) * 2;
			$clist[] = $one;
		}
	}



} elseif($op == 'deluserapp') {

} elseif($op == 'delnotice') {

	if(empty($_G['uid'])) {
		showmessage('no_privilege_guest');
	}
	$id = intval($_GET['id']);
	if($id) {
		C::t('home_notification')->delete_by_id_uid($id, $_G['uid']);
	}
	showmessage('do_success');

} elseif($op == 'getreward') {
	$reward = '';
	if($_G['cookie']['reward_log']) {
		$log = explode(',', $_G['cookie']['reward_log']);
		if(count($log) == 2 && $log[1]) {

			loadcache('creditrule');
			$creditlog = C::t('common_credit_rule_log')->fetch($log[1]);
			$rule = $_G['cache']['creditrule'][$log[0]];
			$rule['cyclenum'] = $rule['rewardnum']? $rule['rewardnum'] - $creditlog['cyclenum'] : 0;
		}
		dsetcookie('reward_log', '');
	}

} elseif($op == 'district') {
	$container = $_GET['container'];
	$showlevel = intval($_GET['level']);
	$showlevel = $showlevel >= 1 && $showlevel <= 4 ? $showlevel : 4;
	$values = array(intval($_GET['pid']), intval($_GET['cid']), intval($_GET['did']), intval($_GET['coid']));
	$containertype = in_array($_GET['containertype'], array('birth', 'reside'), true) ? $_GET['containertype'] : 'birth';
	$level = 1;
	if($values[0]) {
		$level++;
	} else if($_G['uid'] && !empty($_GET['showdefault'])) {

		space_merge($_G['member'], 'profile');
		$district = array();
		if($containertype == 'birth') {
			if(!empty($_G['member']['birthprovince'])) {
				$district[] = $_G['member']['birthprovince'];
				if(!empty($_G['member']['birthcity'])) {
					$district[] = $_G['member']['birthcity'];
				}
				if(!empty($_G['member']['birthdist'])) {
					$district[] = $_G['member']['birthdist'];
				}
				if(!empty($_G['member']['birthcommunity'])) {
					$district[] = $_G['member']['birthcommunity'];
				}
			}
		} else {
			if(!empty($_G['member']['resideprovince'])) {
				$district[] = $_G['member']['resideprovince'];
				if(!empty($_G['member']['residecity'])) {
					$district[] = $_G['member']['residecity'];
				}
				if(!empty($_G['member']['residedist'])) {
					$district[] = $_G['member']['residedist'];
				}
				if(!empty($_G['member']['residecommunity'])) {
					$district[] = $_G['member']['residecommunity'];
				}
			}
		}
		if(!empty($district)) {
			foreach(C::t('common_district')->fetch_all_by_name($district) as $value) {
				$key = $value['level'] - 1;
				$values[$key] = $value['id'];
			}
			$level++;
		}
	}
	if($values[1]) {
		$level++;
	}
	if($values[2]) {
		$level++;
	}
	if($values[3]) {
		$level++;
	}
	$showlevel = $level;
	$elems = array();
	if($_GET['province']) {
		$elems = array($_GET['province'], $_GET['city'], $_GET['district'], $_GET['community']);
	}

	include_once libfile('function/profile');
	$html = showdistrict($values, $elems, $container, $showlevel, $containertype);
} elseif($_GET['op'] == 'createalbum') {
	$albumname = 'new:'.$_GET['name'];
	require_once libfile('function/spacecp');
	$albumid = album_creat_by_id($albumname, intval($_GET['catid']));
}

include template('home/misc_ajax');

?>