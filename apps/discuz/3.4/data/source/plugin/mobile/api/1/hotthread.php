<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: hotthread.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'guide';
$_GET['view'] = 'hot';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;

		foreach($GLOBALS['data']['hot']['threadlist'] as $tid=>$thread) {
			$GLOBALS['data']['hot']['threadlist'][$tid]['avatar'] = avatar($thread['authorid'], 'big', true);
		}
		$variable = array(
			'data' => array_values($GLOBALS['data']['hot']['threadlist']),
			'perpage' => $GLOBALS['perpage'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>