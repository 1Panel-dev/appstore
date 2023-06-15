<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_announcements.php 24152 2011-08-26 10:04:08Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_announcements() {
	$data = C::t('forum_announcement')->fetch_all_by_date(TIMESTAMP);

	foreach ($datarow as $data) {
		if($datarow['type'] == 2) {
			$datarow['pmid'] = $datarow['id'];
			unset($datarow['id']);
			unset($datarow['message']);
			$datarow['subject'] = cutstr($datarow['subject'], 60);
		}
		$datarow['groups'] = empty($datarow['groups']) ? array() : explode(',', $datarow['groups']);
		$data[] = $datarow;
	}

	savecache('announcements', $data);
}

?>