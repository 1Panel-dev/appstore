<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forums.php 31989 2012-10-30 05:31:52Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forums() {
	$data = array();
	$forums = C::t('forum_forum')->fetch_all_forum();
	$pluginvalue = $forumlist = array();
	$pluginvalue = pluginsettingvalue('forums');

	$forumnoperms = array();
	foreach($forums as $val) {
		$forum = array('fid' => $val['fid'], 'type' => $val['type'], 'name' => $val['name'], 'fup' => $val['fup'], 'simple' => $val['simple'], 'status' => $val['status'], 'allowpostspecial' => $val['allowpostspecial'], 'viewperm' => $val['viewperm'], 'formulaperm' => $val['formulaperm'], 'havepassword' => $val['password'], 'postperm' => $val['postperm'], 'replyperm' => $val['replyperm'], 'getattachperm' => $val['getattachperm'], 'postattachperm' => $val['postattachperm'], 'extra' => $val['extra'], 'commentitem' => $val['commentitem'], 'uid' => $val['uid'], 'archive' => $val['archive'], 'domain' => $val['domain']);
		$forum['orderby'] = bindec((($forum['simple'] & 128) ? 1 : 0).(($forum['simple'] & 64) ? 1 : 0));
		$forum['ascdesc'] = ($forum['simple'] & 32) ? 'ASC' : 'DESC';
		$forum['extra'] = unserialize($forum['extra']);
		if(!is_array($forum['extra'])) {
			$forum['extra'] = array();
		}

		if(!isset($forumlist[$forum['fid']])) {
			if($forum['uid']) {
				$forum['users'] = "\t$forum[uid]\t";
			}
			unset($forum['uid']);
			if($forum['fup']) {
				$forumlist[$forum['fup']]['count']++;
			}
			$forumlist[$forum['fid']] = $forum;
		} elseif($forum['uid']) {
			if(!$forumlist[$forum['fid']]['users']) {
				$forumlist[$forum['fid']]['users'] = "\t";
			}
			$forumlist[$forum['fid']]['users'] .= "$forum[uid]\t";
		}
	}

	$data = array();
	if(!empty($forumlist)) {
		foreach($forumlist as $fid1 => $forum1) {
			if(($forum1['type'] == 'group' && $forum1['count'])) {
				$data[$fid1] = formatforumdata($forum1, $pluginvalue);
				unset($data[$fid1]['users'], $data[$fid1]['allowpostspecial'], $data[$fid1]['commentitem']);
				foreach($forumlist as $fid2 => $forum2) {
					if($forum2['fup'] == $fid1 && $forum2['type'] == 'forum') {
						$data[$fid2] = formatforumdata($forum2, $pluginvalue);
						foreach($forumlist as $fid3 => $forum3) {
							if($forum3['fup'] == $fid2 && $forum3['type'] == 'sub') {
								$data[$fid3] = formatforumdata($forum3, $pluginvalue);
							}
						}
					}
				}
			}
		}
	}
	savecache('forums', $data);
}

function formatforumdata($forum, &$pluginvalue) {
	static $keys = array('fid', 'type', 'name', 'fup', 'viewperm', 'postperm', 'orderby', 'ascdesc', 'users', 'status',
		'extra', 'plugin', 'allowpostspecial', 'commentitem', 'archive', 'domain', 'havepassword');
	static $orders = array('lastpost', 'dateline', 'replies', 'views');

	$data = array();
	foreach ($keys as $key) {
		switch ($key) {
			case 'orderby': $data[$key] = $orders[$forum['orderby']]; break;
			case 'plugin': $data[$key] = $pluginvalue[$forum['fid']]; break;
			case 'havepassword': $data[$key] = $forum[$key] ? 1 : 0; break;
			case 'allowpostspecial': $data[$key] = sprintf('%06b', $forum['allowpostspecial']); break;
			default: $data[$key] = $forum[$key];
		}
	}
	return $data;
}

?>