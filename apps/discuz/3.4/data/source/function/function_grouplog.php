<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_grouplog.php 30465 2012-05-30 04:10:03Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function updategroupcreditlog($fid, $uid) {
	global $_G;
	if(empty($fid) || empty($uid)) {
		return false;
	}
	$today = date('Ymd', TIMESTAMP);
	$updategroupcredit = getcookie('groupcredit_'.$fid);
	if($updategroupcredit < $today) {
		$status = C::t('forum_groupcreditslog')->check_logdate($fid, $uid, $today);
		if(empty($status)) {
			C::t('forum_forum')->update_commoncredits($fid);
			C::t('forum_groupcreditslog')->insert(array('fid' => $fid, 'uid' => $uid, 'logdate' => $today), false, true);
			if(empty($_G['forum']) || empty($_G['forum']['level'])) {
				$forum = C::t('forum_forum')->fetch($fid);
				$forum = array('name' => $forum['name'], 'level' => $forum['level'], 'commoncredits' => $forum['commoncredits']);
			} else {
				$_G['forum']['commoncredits'] ++;
				$forum = &$_G['forum'];
			}
			if(empty($_G['grouplevels'])) {
				loadcache('grouplevels');
			}
			$grouplevel = $_G['grouplevels'][$forum['level']];

			if($grouplevel['type'] == 'default' && !($forum['commoncredits'] >= $grouplevel['creditshigher'] && $forum['commoncredits'] < $grouplevel['creditslower'])) {
				$levelinfo = C::t('forum_grouplevel')->fetch_by_credits($forum['commoncredits']);
				$levelid = $levelinfo['levelid'];
				if(!empty($levelid)) {
					C::t('forum_forum')->update_group_level($levelid, $fid);
					$query = C::t('forum_forumfield')->fetch($fid);
					$groupfounderuid = $query['founderuid'];
					notification_add($groupfounderuid, 'system', 'grouplevel_update', array(
						'groupname' => '<a href="forum.php?mod=group&fid='.$fid.'">'.$forum['name'].'</a>',
						'newlevel' => $_G['grouplevels'][$levelid]['leveltitle'],
						'from_id' => 0,
						'from_idtype' => 'changeusergroup'
					));
				}
			}
		}
		dsetcookie('groupcredit_'.$fid, $today, 86400);
	}
}