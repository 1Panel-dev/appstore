<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_announcement_daily.php 25786 2011-11-22 06:17:25Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$delnum = C::t('forum_announcement')->delete_all_by_endtime($_G['timestamp']);

if($delnum) {
	require_once libfile('function/cache');
	updatecache(array('announcements', 'announcements_forum'));
}

?>