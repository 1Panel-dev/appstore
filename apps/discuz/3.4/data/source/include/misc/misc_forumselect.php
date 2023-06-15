<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_forumselect.php 34303 2014-01-15 04:32:19Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!isset($_G['cache']['forums'])) {
	loadcache('forums');
}


$grouplist = $commonlist = '';
$special = isset($_GET['special']) ? intval($_GET['special']) : null;
$forumlist = $subforumlist = array();
$i = array();

if(!$special) {
	$commonfids = explode('D', $_G['cookie']['visitedfid']);

	foreach($commonfids as $k => $fid) {
		if($_G['cache']['forums'][$fid]['type'] == 'sub') {
			$commonfids[] = $_G['cache']['forums'][$fid]['fup'];
			unset($commonfids[$k]);
		}
	}

	$commonfids = array_unique($commonfids);

	foreach($commonfids as $fid) {
		$fid = intval($fid);
		$commonlist .= '<li fid="'.$fid.'">'.$_G['cache']['forums'][$fid]['name'].'</li>';
	}
}

foreach($_G['cache']['forums'] as $forum) {
	if(!$forum['status'] || $forum['status'] == 2) {
		continue;
	}
	if($forum['type'] != 'group' && $special !== null) {
		$allow = false;
		if(!$forum['postperm'] || $forum['postperm'] && forumperm($forum['postperm'])) {
			if($special == 1) {
				$allow = $_G['group']['allowpostpoll'] && substr($forum['allowpostspecial'], -1, 1);
			} elseif($special == 2) {
				$allow = $_G['group']['allowposttrade'] && substr($forum['allowpostspecial'], -2, 1);
			} elseif($special == 3) {
				$allow = $_G['group']['allowpostreward'] && isset($_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]) && substr($forum['allowpostspecial'], -3, 1);
			} elseif($special == 4) {
				$allow = $_G['group']['allowpostactivity'] && substr($forum['allowpostspecial'], -4, 1);
			} elseif($special == 5) {
				$allow = $_G['group']['allowpostdebate'] && substr($forum['allowpostspecial'], -5, 1);
			} else {
				$allow = true;
				$special = 0;
			}
		}
		if(!$allow) {
			continue;
		}
	}
	if($forum['type'] == 'group') {
		$grouplist .= '<li fid="'.$forum['fid'].'">'.$forum['name'].'</li>';
		$visible[$forum['fid']] = true;
	} elseif($forum['type'] == 'forum' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t"))) {
		$forumlist[$forum['fup']] .= '<li fid="'.$forum['fid'].'">'.$forum['name'].'</li>';
		$visible[$forum['fid']] = true;
	} elseif($forum['type'] == 'sub' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t"))) {
		$subforumlist[$forum['fup']] .= '<li fid="'.$forum['fid'].'">'.$forum['name'].'</li>';
	}
}

include template('forum/post_forumselect');
exit;

?>