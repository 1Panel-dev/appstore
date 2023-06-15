<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mythread.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'guide';
$_GET['view'] = 'my';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$variable = array(
			'data' => array_values($GLOBALS['data']['my']['threadlist']),
			'perpage' => $GLOBALS['perpage'],
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>