<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumdisplay.php 35213 2015-02-26 06:15:12Z nemohou $
 */
if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}


$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		if (!empty($_GET['pw'])) {
			$_GET['action'] = 'pwverify';
		}
		$_G['forum']['allowglobalstick'] = true;
		if($_G['forum']['redirect']) {
			mobile_core::result(mobile_core::variable(array('forum' => array('fid' => $_G['fid'], 'redirect' => $_G['forum']['redirect']))));
		}
	}

	function output() {
		global $_G;
		include_once 'source/plugin/mobile/api/4/sub_threadlist.php';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>