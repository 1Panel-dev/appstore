<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todayheats_daily.php 31913 2012-10-24 06:52:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$yesterday = strtotime(dgmdate(TIMESTAMP, 'Y-m-d'))-86400;
$data = $tids = $fids = $hotnum = array();
$daystr = dgmdate($yesterday, 'Ymd');
foreach(C::t('forum_thread')->fetch_all_for_guide('hot', 0, array(), $_G['setting']['heatthread']['guidelimit'], $yesterday, 0, 0) as $thread) {
	$data[$thread['tid']] = array(
			'cid' => 0,
			'fid' => $thread['fid'],
			'tid' => $thread['tid']
		);
	$fids[$thread['fid']] = array('fid' => $thread['fid'], 'dateline' => $daystr, 'hotnum' => 0);
	$tids[$thread['fid']][$thread['tid']] = $thread['tid'];
}
if($data) {
	$cids = C::t('forum_threadcalendar')->fetch_all_by_fid_dateline(array_keys($fids), $daystr);
	foreach($cids as $fid => $cinfo) {
		$hotnum[$cinfo['cid']] = count($tids[$fid]);
		foreach($tids[$fid] as $tid) {
			$data[$tid]['cid'] = $cinfo['cid'];
		}
		unset($fids[$fid]);
	}
	if($fids) {
		C::t('forum_threadcalendar')->insert_multiterm($fids);
		foreach(C::t('forum_threadcalendar')->fetch_all_by_fid_dateline(array_keys($fids), $daystr) as $fid => $cinfo) {
			$hotnum[$cinfo['cid']] = count($tids[$fid]);
			foreach($tids[$fid] as $tid) {
				$data[$tid]['cid'] = $cinfo['cid'];
			}
		}
	}
	C::t('forum_threadhot')->insert_multiterm($data);
	foreach($hotnum as $cid => $num) {
		C::t('forum_threadcalendar')->update($cid, array('hotnum' => $num));
	}
}

?>