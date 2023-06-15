<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_getatuser.php 25782 2011-11-22 05:29:19Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$result = '';
$search_str = $_GET['search_str'];
if($_G['uid']) {
	$atlist = $atlist_cookie = array();
	$limit = 200;
	if($_G['cookie']['atlist']) {
		$cookies = explode(',', $_G['cookie']['atlist']);
		foreach(C::t('common_member')->fetch_all($cookies, false) as $row) {
			if ($row['uid'] != $_G['uid'] && in_array($row['uid'], $cookies)) {
				$atlist_cookie[$row['uid']] = $row['username'];
			}
		}
	}
	foreach(C::t('home_follow')->fetch_all_following_by_uid($_G['uid'], 0, 0, $limit) as $row) {
		if($atlist_cookie[$row[followuid]]) {
			continue;
		}
		$atlist[$row[followuid]] = $row['fusername'];
	}
	$num = count($atlist);
	if($num < $limit) {
		$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, $limit * 2);
		foreach($query as $row) {
			if(count($atlist) == $limit) {
				break;
			}
			if($atlist_cookie[$row[fuid]]) {
				continue;
			}
			$atlist[$row[fuid]] = $row['fusername'];
		}
	}
	$result = implode(',', $atlist_cookie).($atlist_cookie && $atlist ? ',' : '').implode(',', $atlist);
}
include template('common/getatuser');
?>