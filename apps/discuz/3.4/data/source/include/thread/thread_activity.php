<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_activity.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$isverified = $applied = 0;
$ufielddata = $applyinfo = array();
if($_G['uid']) {
	$applyinfo = C::t('forum_activityapply')->fetch_info_for_user($_G['uid'], $_G['tid']);
	if($applyinfo) {
		$isverified = $applyinfo['verified'];
		if($applyinfo['ufielddata']) {
			$ufielddata = dunserialize($applyinfo['ufielddata']);
		}
		$applied = 1;
	}
}
$applylist = array();
$activity = C::t('forum_activity')->fetch($_G['tid']);
$activityclose = $activity['expiration'] ? ($activity['expiration'] > TIMESTAMP ? 0 : 1) : 0;
$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto']) : 0;
$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration']) : 0;
$activity['attachurl'] = $activity['thumb'] = '';
if($activity['ufield']) {
	$activity['ufield'] = dunserialize($activity['ufield']);
	if($activity['ufield']['userfield']) {
		$htmls = $settings = array();
		require_once libfile('function/profile');
		foreach($activity['ufield']['userfield'] as $fieldid) {
			if(empty($ufielddata['userfield'])) {
				$memberprofile = C::t('common_member_profile')->fetch($_G['uid']);
				foreach($activity['ufield']['userfield'] as $val) {
					$ufielddata['userfield'][$val] = $memberprofile[$val];
				}
				unset($memberprofile);
			}
			$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
			if($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}
		}
	}
} else {
	$activity['ufield'] = '';
}

if($activity['aid']) {
	$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $activity['aid']);
	if($attach['isimage']) {
		$activity['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
		$activity['thumb'] = $attach['thumb'] ? getimgthumbname($activity['attachurl']) : $activity['attachurl'];
		$activity['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
	}
	$skipaids[] = $activity['aid'];
}


$applylistverified = array();
$noverifiednum = 0;
$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 0, 0, 1);
foreach($query as $activityapplies) {
	$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
	if($activityapplies['verified'] == 1) {
		$activityapplies['ufielddata'] = dunserialize($activityapplies['ufielddata']);
		if(count($applylist) < $_G['setting']['activitypp']) {
			$activityapplies['message'] = preg_replace("/(".lang('forum/misc', 'contact').".*)/", '', $activityapplies['message']);
			$applylist[] = $activityapplies;
		}
	} else {
		if(count($applylistverified) < 8) {
			$applylistverified[] = $activityapplies;
		}
		$noverifiednum++;
	}

}

$applynumbers = $activity['applynumber'];
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;
$allapplynum = $applynumbers + $noverifiednum;
if($_G['forum']['status'] == 3) {
	$isgroupuser = groupperm($_G['forum'], $_G['uid']);
}
?>