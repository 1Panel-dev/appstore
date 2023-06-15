<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_medal_daily.php 24698 2011-10-08 08:36:47Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$medalnewarray = $medalsnew = $uids = array();


foreach(C::t('forum_medallog')->fetch_all_by_expiration(TIMESTAMP) as $medalnew) {
	$uids[] = $medalnew['uid'];
	$medalnews[] = $medalnew;
}

$membermedals = array();
foreach(C::t('common_member_field_forum')->fetch_all($uids) as $member) {
	$membermedals[$member['uid']] = $member['medals'];
}

foreach($medalnews as $medalnew) {
	$medalnew['medals'] = empty($medalnewarray[$medalnew['uid']]) ? explode("\t", $membermedals[$medalnew['uid']]) : explode("\t", $medalnewarray[$medalnew['uid']]);

	foreach($medalnew['medals'] as $key => $medalnewid) {
		list($medalid, $medalexpiration) = explode("|", $medalnewid);
		if($medalnew['medalid'] == $medalid) {
			unset($medalnew['medals'][$key]);
		}
	}

	$medalnewarray[$medalnew['uid']] = implode("\t", $medalnew['medals']);
	C::t('forum_medallog')->update($medalnew['id'], array('status' => 0));
	C::t('common_member_field_forum')->update($medalnew['uid'], array('medals' => $medalnewarray[$medalnew['uid']]), 'UNBUFFERED');
	C::t('common_member_medal')->delete_by_uid_medalid($medalnew['uid'], $medalnew['medalid']);
}
?>