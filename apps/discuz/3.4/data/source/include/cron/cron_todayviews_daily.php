<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todayviews_daily.php 26812 2011-12-23 08:21:29Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$updateviews = array();
$deltids = array();
foreach(C::t('forum_threadaddviews')->fetch_all_order_by_tid(500) as $tid => $addview) {
	$deltids[$tid] = $updateviews[$addview['addviews']][] = $tid;
}
if($deltids) {
	C::t('forum_threadaddviews')->delete($deltids);
}
foreach($updateviews as $views => $tids) {
	C::t('forum_thread')->increase($tids, array('views' => $views), true);
}

?>