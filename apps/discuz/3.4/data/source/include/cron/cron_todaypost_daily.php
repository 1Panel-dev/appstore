<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 31920 2012-10-24 09:18:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$yesterdayposts = intval(C::t('forum_forum')->fetch_sum_todaypost());

C::t('forum_forum')->update_oldrank_and_yesterdayposts();

$historypost = C::t('common_setting')->fetch('historyposts');
$hpostarray = explode("\t", $historypost);
$_G['setting']['historyposts'] = $hpostarray[1] < $yesterdayposts ? "$yesterdayposts\t$yesterdayposts" : "$yesterdayposts\t$hpostarray[1]";

C::t('common_setting')->update('historyposts', $_G['setting']['historyposts']);
$date = date('Y-m-d', TIMESTAMP - 86400);

C::t('forum_statlog')->insert_stat_log($date);
C::t('forum_forum')->clear_todayposts();
$rank = 1;
foreach(C::t('forum_statlog')->fetch_all_rank_by_logdate($date) as $value) {
	C::t('forum_forum')->update($value['fid'], array('rank' => $rank));
	$rank++;
}
savecache('historyposts', $_G['setting']['historyposts']);

?>