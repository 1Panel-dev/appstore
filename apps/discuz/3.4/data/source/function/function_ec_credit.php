<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_ec_credit.php 26205 2011-12-05 10:09:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updatecreditcache($uid, $type, $return = 0) {
	$all = countcredit($uid, $type);
	$halfyear = countcredit($uid, $type, 180);
	$thismonth = countcredit($uid, $type, 30);
	$thisweek = countcredit($uid, $type, 7);
	$before = array(
		'good' => $all['good'] - $halfyear['good'],
		'soso' => $all['soso'] - $halfyear['soso'],
		'bad' => $all['bad'] - $halfyear['bad'],
		'total' => $all['total'] - $halfyear['total']
	);

	$data = array('all' => $all, 'before' => $before, 'halfyear' => $halfyear, 'thismonth' => $thismonth, 'thisweek' => $thisweek);

	C::t('forum_spacecache')->insert(array(
		'uid' => $uid,
		'variable' => $type,
		'value' => serialize($data),
		'expiration' => getexpiration(),
	), false, true);
	if($return) {
		return $data;
	}
}

function countcredit($uid, $type, $days = 0) {
	$type = $type == 'sellercredit' ? 1 : 0;
	$good = $soso = $bad = 0;
	foreach(C::t('forum_tradecomment')->fetch_all_by_rateeid($uid, $type, $days ? TIMESTAMP - $days * 86400 : 0) as $credit) {
		if($credit['score'] == 1) {
			$good++;
		} elseif($credit['score'] == 0) {
			$soso++;
		} else {
			$bad++;
		}
	}
	return array('good' => $good, 'soso' => $soso, 'bad' => $bad, 'total' => $good + $soso + $bad);
}

function updateusercredit($uid, $type, $level) {
	$uid = intval($uid);
	if(!$uid || !in_array($type, array('buyercredit', 'sellercredit')) || !in_array($level, array('good', 'soso', 'bad'))) {
		return;
	}

	if($cache = C::t('forum_spacecache')->fetch($uid, $type)) {
		$expiration = $cache['expiration'];
		$cache = dunserialize($cache['value']);
	} else {
		$init = array('good' => 0, 'soso' => 0, 'bad' => 0, 'total' => 0);
		$cache = array('all' => $init, 'before' => $init, 'halfyear' => $init, 'thismonth' => $init, 'thisweek' => $init);
		$expiration = getexpiration();
	}

	foreach(array('all', 'halfyear', 'thismonth', 'thisweek') as $key) {
		$cache[$key][$level]++;
		$cache[$key]['total']++;
	}

	C::t('forum_spacecache')->insert(array(
		'uid' => $uid,
		'variable' => $type,
		'value' => serialize($cache),
		'expiration' => $expiration,
	), false, true);

	$score = $level == 'good' ? 1 : ($level == 'soso' ? 0 : -1);
	C::t('common_member_status')->increase($uid, array($type=>$score));
}

?>