<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: threadrecommend.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'misc';
$_GET['action'] = 'recommend';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		$variable = array();
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>