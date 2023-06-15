<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_privacy.php 24946 2011-10-18 02:54:40Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

space_merge($space, 'field_home');
$operation = in_array($_GET['op'], array('base', 'feed', 'filter', 'getgroup')) ? trim($_GET['op']) : 'base';

if(submitcheck('privacysubmit')) {

	if($operation == 'base') {
		$space['privacy']['view'] = array();
		$viewtype = array('index', 'friend', 'wall', 'doing', 'blog', 'album', 'share', 'home', 'videoviewphoto');
		foreach ($_POST['privacy']['view'] as $key => $value) {
			if(in_array($key, $viewtype)) {
				$space['privacy']['view'][$key] = intval($value);
			}
		}
	}

	if($operation == 'feed') {
		$space['privacy']['feed'] = array();
		if(isset($_POST['privacy']['feed'])) {
			foreach ($_POST['privacy']['feed'] as $key => $value) {
				$space['privacy']['feed'][$key] = 1;
			}
		}
	}
	privacy_update();

	manyoulog('user', $_G['uid'], 'update');
	showmessage('do_success', 'home.php?mod=spacecp&ac=privacy&op='.$operation);

} elseif(submitcheck('privacy2submit')) {

	$space['privacy']['filter_icon'] = array();
	if(isset($_POST['privacy']['filter_icon'])) {
		foreach($_POST['privacy']['filter_icon'] as $key => $value) {
			$space['privacy']['filter_icon'][$key] = 1;
		}
	}
	$space['privacy']['filter_gid'] = array();
	if(isset($_POST['privacy']['filter_gid'])) {
		foreach ($_POST['privacy']['filter_gid'] as $key => $value) {
			$space['privacy']['filter_gid'][$key] = intval($value);
		}
	}
	$space['privacy']['filter_note'] = array();
	if(isset($_POST['privacy']['filter_note'])) {
		foreach ($_POST['privacy']['filter_note'] as $key => $value) {
			$space['privacy']['filter_note'][$key] = 1;
		}
	}
	privacy_update();

	require_once libfile('function/friend');
	friend_cache($_G['uid']);

	showmessage('do_success', 'home.php?mod=spacecp&ac=privacy&op='.$operation);
}

if($operation == 'filter') {
	require_once libfile('function/friend');
	$groups = friend_group_list();

	$filter_icons = empty($space['privacy']['filter_icon'])?array():$space['privacy']['filter_icon'];
	$filter_note = empty($space['privacy']['filter_note'])?array():$space['privacy']['filter_note'];
	$iconnames = $appids = $icons = $uids = $users = array();
	foreach ($filter_icons as $key => $value) {
		list($icon, $uid) = explode('|', $key);
		$icons[$key] = $icon;
		$uids[$key] = $uid;
	}
	foreach ($filter_note as $key => $value) {
		list($type, $uid) = explode('|', $key);
		$types[$key] = $type;
		$uids[$key] = $uid;
	}
	if($uids) {
		foreach(C::t('common_member')->fetch_all($uids) as $uid => $value) {
			$users[$uid] = $value['username'];
		}
	}

} elseif ($operation == 'getgroup') {

	$gid = empty($_GET['gid'])?0:intval($_GET['gid']);
	$users = array();
	$query = C::t('home_friend')->fetch_all_by_uid_gid($_G['uid'], $gid, 0, 0, false);
	foreach($query as $value) {
		$users[] = $value['fusername'];
	}
	$ustr = empty($users)?'': dhtmlspecialchars(implode(' ', $users));
	showmessage($ustr, '', array(), array('msgtype' => 3, 'handle'=>false));

} else {

	$sels = array();
	if($space['privacy']['view']) {
		foreach ($space['privacy']['view'] as $key => $value) {
			$sels['view'][$key] = array($value => ' selected');
		}
	}
	if($space['privacy']['feed']) {
		foreach ($space['privacy']['feed'] as $key => $value) {
			$sels['feed'][$key] = ' checked';
		}
	}
}

$actives = array('privacy' =>' class="a"');
$opactives = array($operation =>' class="a"');

include template('home/spacecp_privacy');

?>