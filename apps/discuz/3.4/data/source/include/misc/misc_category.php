<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_category.php 31560 2012-09-10 03:47:45Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$gquery = C::t('forum_forum')->fetch_all_info_by_fids($gid);
$query = C::t('forum_forum')->fetch_all_info_by_fids(0, 1, 0, $gid, 1, 0, 0, 'forum');
if(!empty($_G['member']['accessmasks'])) {
	$fids = array_keys($query);
	$accesslist = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
	foreach($query as $key => $val) {
		$query[$key]['allowview'] = $accesslist[$key];
	}
}
if(empty($gquery) || empty($query)) {
	showmessage('forum_nonexistence', NULL);
}
$query = array_merge($gquery, $query);
$fids = array();
foreach($query as $forum) {
	$forum['extra'] = dunserialize($forum['extra']);
	if(!is_array($forum['extra'])) {
		$forum['extra'] = array();
	}
	if($forum['type'] != 'group') {
		$threads += $forum['threads'];
		$posts += $forum['posts'];
		$todayposts += $forum['todayposts'];
		if(forum($forum)) {
			$forum['orderid'] = $catlist[$forum['fup']]['forumscount'] ++;
			$forum['subforums'] = '';
			$forumlist[$forum['fid']] = $forum;
			$catlist[$forum['fup']]['forums'][] = $forum['fid'];
			$fids[] = $forum['fid'];
		}
	} else {
		$forum['collapseimg'] = 'collapsed_no.gif';
		$collapse['category_'.$forum['fid']] = '';

		if($forum['moderators']) {
			$forum['moderators'] = moddisplay($forum['moderators'], 'flat');
		}
		$catlist[$forum['fid']] = $forum;

		$navigation = '<em>&rsaquo;</em> '.$forum['name'];
		$navtitle_g = strip_tags($forum['name']);
	}
}
if($catlist) {
	foreach($catlist as $key => $var) {
		$catlist[$key]['forumcolumns'] = $var['catforumcolumns'];
		if($var['forumscount'] && $var['catforumcolumns']) {
			$catlist[$key]['forumcolwidth'] = (floor(100 / $var['catforumcolumns']) - 0.1).'%';
			$catlist[$key]['endrows'] = '';
			if($colspan = $var['forumscount'] % $var['catforumcolumns']) {
				while(($var['catforumcolumns'] - $colspan) > 0) {
					$catlist[$key]['endrows'] .= '<td>&nbsp;</td>';
					$colspan ++;
				}
				$catlist[$key]['endrows'] .= '</tr>';
			}
		}
	}
}
$query = C::t('forum_forum')->fetch_all_subforum_by_fup($fids);
foreach($query as $forum) {
	if($_G['setting']['subforumsindex'] && $forumlist[$forum['fup']]['permission'] == 2) {
		$forumurl = !empty($forum['domain']) && !empty($_G['setting']['domain']['root']['forum']) ? $_G['scheme'].'://'.$forum['domain'].'.'.$_G['setting']['domain']['root']['forum'] : 'forum.php?mod=forumdisplay&fid='.$forum['fid'];
		$forumlist[$forum['fup']]['subforums'] .= '<a href="'.$forumurl.'"><u>'.$forum['name'].'</u></a>&nbsp;&nbsp;';
	}
	$forumlist[$forum['fup']]['threads'] 	+= $forum['threads'];
	$forumlist[$forum['fup']]['posts'] 	+= $forum['posts'];
	$forumlist[$forum['fup']]['todayposts'] += $forum['todayposts'];

}

?>