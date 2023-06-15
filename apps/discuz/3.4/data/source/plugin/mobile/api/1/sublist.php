<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sublist.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		$variable = array(
			'sublist' => mobile_core::getvalues($GLOBALS['sublist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'todayposts', 'posts')),
		);
		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>