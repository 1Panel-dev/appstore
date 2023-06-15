<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sublist.php 34398 2014-04-14 07:11:22Z nemohou $
 */

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'forumdisplay';
include_once 'forum.php';

class mobile_api {

	function common() {

	}

	function output() {
		global $_G;

		foreach ($GLOBALS['sublist'] as $k => $sublist) {
			if ($sublist['icon']) {
				$icon = preg_match('/src="(.+?)"/', $sublist['icon'], $r) ? $r[1] : '';
				if (!preg_match('/^https?:\//', $icon)) {
					$icon = $_G['siteurl'] . $icon;
				}
				$GLOBALS['sublist'][$k]['icon'] = $icon;
			}
		}

		$variable = array(
		    'sublist' => mobile_core::getvalues($GLOBALS['sublist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'todayposts', 'posts', 'icon')),
		);
		$variable['forum']['password'] = $variable['forum']['password'] ? '1' : '0';
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>