<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_daily.php 33675 2013-08-01 02:09:09Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/cache');
updatecache('forumrecommend');

if(C::t('common_advertisement')->close_endtime()) {
	updatecache(array('setting', 'advs'));
}
C::t('forum_threaddisablepos')->truncate();
C::t('common_searchindex')->truncate();
C::t('forum_threadmod')->delete_by_dateline($_G['timestamp']-31536000);
C::t('forum_forumrecommend')->delete_old();
C::t('home_visitor')->delete_by_dateline($_G['timestamp']-7776000);
C::t('forum_postcache')->delete_by_dateline(TIMESTAMP-86400);
C::t('forum_newthread')->delete_by_dateline(TIMESTAMP-1296000);
C::t('common_seccheck')->truncate();

if($_G['setting']['heatthread']['type'] == 2 && $_G['setting']['heatthread']['period']) {
	$partakeperoid = 86400 * $_G['setting']['heatthread']['period'];
	C::t('forum_threadpartake')->delete($_G[timestamp]-$partakeperoid);
}

C::t('common_member_count')->clear_today_data();

C::t('forum_trade')->update_closed($_G['timestamp']);
C::t('forum_tradelog')->clear_failure(7);
C::t('forum_tradelog')->expiration_payed(7);
C::t('forum_tradelog')->expiration_finished(7);

if($_G['setting']['cachethreadon']) {
	removedir($_G['setting']['cachethreaddir'], TRUE);
	touch($_G['setting']['cachethreaddir'].'/index.htm');
}
removedir($_G['setting']['attachdir'].'image', TRUE);
@touch($_G['setting']['attachdir'].'image/index.htm');

C::t('forum_attachment_unused')->clear();

C::t('forum_polloption_image')->clear();

$uids = $members = array();
$members = C::t('common_member')->fetch_all_ban_by_groupexpiry(TIMESTAMP);
if(($uids = array_keys($members))) {
	$setarr = array();
	foreach(C::t('common_member_field_forum')->fetch_all($uids) as $uid => $member) {
		$member['groupterms'] = dunserialize($member['groupterms']);
		$member['groupid'] = $members[$uid]['groupid'];
		$member['credits'] = $members[$uid]['credits'];

		if(!empty($member['groupterms']['main']['groupid'])) {
			$groupidnew = $member['groupterms']['main']['groupid'];
			$adminidnew = $member['groupterms']['main']['adminid'];
			unset($member['groupterms']['main']);
			unset($member['groupterms']['ext'][$member['groupid']]);
			$setarr['groupexpiry'] = groupexpiry($member['groupterms']);
		} else {
			$query = C::t('common_usergroup')->fetch_by_credits($member['credits'], 'member');
			$groupidnew = $query['groupid'];
			$adminidnew = 0;
		}
		$setarr['adminid'] = $adminidnew;
		$setarr['groupid'] = $groupidnew;
		C::t('common_member')->update($uid, $setarr);
		C::t('common_member_field_forum')->update($uid, array('groupterms' => ($member['groupterms'] ? serialize($member['groupterms']) : '')));
	}
}

if(!empty($_G['setting']['advexpiration']['allow'])) {
	$endtimenotice = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP)) + $_G['setting']['advexpiration']['day'] * 86400;
	$advs = array();
	foreach(C::t('common_advertisement')->fetch_all_endtime($endtimenotice) as $adv) {
		$advs[] = '<a href="admin.php?action=adv&operation=edit&advid='.$adv['advid'].'" target="_blank">'.$adv['title'].'</a>';
	}
	if($advs) {
		$users = explode("\n", $_G['setting']['advexpiration']['users']);
		$users = array_map('trim', $users);
		if($users) {
			foreach(C::t('common_member')->fetch_all_by_username($users) as $member) {
				$noticelang = array('day' => $_G['setting']['advexpiration']['day'], 'advs' => implode("<br />", $advs), 'from_id' => 0, 'from_idtype' => 'advexpire');
				if(in_array('notice', $_G['setting']['advexpiration']['method'])) {
					notification_add($member['uid'], 'system', 'system_adv_expiration', $noticelang, 1);
				}
				if(in_array('mail', $_G['setting']['advexpiration']['method'])) {
					if(!sendmail("$member[username] <$member[email]>", lang('email', 'adv_expiration_subject', $noticelang), lang('email', 'adv_expiration_message', $noticelang))) {
						runlog('sendmail', "$member[email] sendmail failed.");
					}
				}
			}
		}
	}
}


$count = C::t('common_card')->count_by_where("status = '1' AND cleardateline <= '{$_G['timestamp']}'");
if($count) {
	C::t('common_card')->update_to_overdue($_G['timestamp']);
	$card_info = serialize(array('num' => $count));
	$cardlog = array(
		'info' => $card_info,
		'dateline' => $_G['timestamp'],
		'operation' => 9
	);
	C::t('common_card_log')->insert($cardlog);
}

C::t('common_member_action_log')->delete_by_dateline($_G['timestamp'] - 86400);

C::t('forum_collectioninvite')->delete_by_dateline($_G['timestamp'] - 86400*7);

loadcache('seccodedata', true);
$_G['cache']['seccodedata']['register']['show'] = 0;
savecache('seccodedata', $_G['cache']['seccodedata']);

function removedir($dirname, $keepdir = FALSE) {
	$dirname = str_replace(array( "\n", "\r", '..'), array('', '', ''), $dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			is_dir($dir) ? removedir($dir) : unlink($dir);
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

?>