<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumindex.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'index';
include_once 'forum.php';

class mobile_api {

	function common() {
	}

	function output() {
		global $_G;
		if($_GET['checknotice']) {
			$variable = array();
		} else {
			$variable = array(
				'member_email' => $_G['member']['email'],
				'member_credits' => $_G['member']['credits'],
				'setting_bbclosed' => $_G['setting']['bbclosed'],
				'group' => mobile_core::getvalues($_G['group'], array('groupid', 'grouptitle', '/^allow.+?$/')),
				'catlist' => array_values(mobile_core::getvalues($GLOBALS['catlist'], array('/^\d+$/'), array('fid', 'name', 'forums'))),
				'forumlist' => array_values(mobile_core::getvalues($GLOBALS['forumlist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'posts', 'redirect', 'todayposts', 'description'))),
			);
		}
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>