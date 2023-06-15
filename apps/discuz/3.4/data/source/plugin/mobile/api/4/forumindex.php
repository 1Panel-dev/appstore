<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forumindex.php 34398 2014-04-14 07:11:22Z nemohou $
 */

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'index';
include_once 'forum.php';

class mobile_api {

	function common() {

	}

	function output() {
		global $_G;
		$forums = $GLOBALS['forums'] ? $GLOBALS['forums'] : C::t('forum_forum')->fetch_all_by_status(1);
		foreach ($forums as $forum) {
			if ($forum['fup'] && $GLOBALS['forumlist'][$forum['fup']]) {
				$GLOBALS['forumlist'][$forum['fup']]['sublist'][] = mobile_core::getvalues($forum, array('fid', 'name', 'threads', 'posts', 'redirect', 'todayposts', 'description'));
			}
			if ($GLOBALS['forumlist'][$forum['fid']]['icon']) {
				$icon = preg_match('/src="(.+?)"/', $GLOBALS['forumlist'][$forum['fid']]['icon'], $r) ? $r[1] : '';
				if (!preg_match('/^https?:\//', $icon)) {
					$icon = $_G['siteurl'] . $icon;
				}
				$GLOBALS['forumlist'][$forum['fid']]['icon'] = $icon;
			}
		}
		if ($_GET['checknotice']) {
			$variable = array();
		} else {
			$variable = array(
			    'member_email' => $_G['member']['email'],
			    'member_credits' => $_G['member']['credits'],
			    'setting_bbclosed' => $_G['setting']['bbclosed'],
			    'group' => mobile_core::getvalues($_G['group'], array('groupid', 'grouptitle', '/^allow.+?$/')),
			    'catlist' => array_values(mobile_core::getvalues($GLOBALS['catlist'], array('/^\d+$/'), array('fid', 'name', 'forums'))),
			    'forumlist' => array_values(mobile_core::getvalues($GLOBALS['forumlist'], array('/^\d+$/'), array('fid', 'name', 'threads', 'posts', 'redirect', 'todayposts', 'description', 'sublist', 'icon'))),
			);
		}
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>