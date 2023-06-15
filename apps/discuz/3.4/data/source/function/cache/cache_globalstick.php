<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_globalstick.php 24152 2011-08-26 10:04:08Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_globalstick() {
	$data = array();
	$query = C::t('forum_forum')->fetch_all_valid_forum();
	$fuparray = $threadarray = array();
	foreach($query as $forum) {
		switch($forum['type']) {
			case 'forum':
				$fuparray[$forum['fid']] = $forum['fup'];
				break;
			case 'sub':
				$fuparray[$forum['fid']] = $fuparray[$forum['fup']];
				break;
		}
	}
	foreach(C::t('forum_thread')->fetch_all_by_displayorder(array(2, 3)) as $thread) {
		switch($thread['displayorder']) {
			case 2:
				$threadarray[$fuparray[$thread['fid']]][] = $thread['tid'];
				break;
			case 3:
				$threadarray['global'][] = $thread['tid'];
				break;
		}
	}
	foreach(array_unique($fuparray) as $gid) {
		if(!empty($threadarray[$gid])) {
			$data['categories'][$gid] = array(
				'tids'	=> dimplode($threadarray[$gid]),
				'count'	=> intval(@count($threadarray[$gid]))
			);
		}
	}
	$data['global'] = array(
		'tids'	=> empty($threadarray['global']) ? '' : dimplode($threadarray['global']),
		'count'	=> intval(@count($threadarray['global']))
	);

	savecache('globalstick', $data);
}

?>