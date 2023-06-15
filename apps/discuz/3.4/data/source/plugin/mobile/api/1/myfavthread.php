<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myfavthread.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'space';
$_GET['do'] = 'favorite';
$_GET['type'] = 'thread';
include_once 'home.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$list = array_values($GLOBALS['list']);
		$tids = array();
		foreach($list as $key=>$value) {
			$tids[] = $value['id'];
		}
		if($tids) {
			$threadinfo = C::t('forum_thread')->fetch_all($tids);
		}
		foreach($list as $key=>$value) {
			$list[$key]['replies'] = $threadinfo[$value['id']]['replies'];
			$list[$key]['author'] = $threadinfo[$value['id']]['author'];
		}
		$variable = array(
			'list' => $list,
			'perpage' => $GLOBALS['perpage'],
			'count' => $GLOBALS['count'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>