<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_profile.php 29279 2012-03-31 09:06:19Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['uid'] && $_G['setting']['privacy']['view']['profile']) {
	showmessage('home_no_privilege', '', array(), array('login' => true));
}

require_once libfile('function/spacecp');

$inarchive = isset($space['_inarchive']) && $space['_inarchive'];
space_merge($space, 'count', $inarchive);
space_merge($space, 'field_home', $inarchive);
space_merge($space, 'field_forum', $inarchive);
space_merge($space, 'profile', $inarchive);
space_merge($space, 'status', $inarchive);
getonlinemember(array($space['uid']));

if($space['videophoto'] && ckvideophoto($space, 1)) {
	$space['videophoto'] = getvideophoto($space['videophoto']);
} else {
	$space['videophoto'] = '';
}

$space['admingroup'] = $_G['cache']['usergroups'][$space['adminid']];
$space['admingroup']['icon'] = g_icon($space['adminid'], 1);

$space['group'] = $_G['cache']['usergroups'][$space['groupid']];
$space['group']['icon'] = g_icon($space['groupid'], 1);
$encodeusername = rawurlencode($space['username']);

if($space['extgroupids']) {
	$newgroup = array();
	$e_ids = explode("\t", $space['extgroupids']);
	foreach ($e_ids as $e_id) {
		$newgroup[] = $_G['cache']['usergroups'][$e_id]['grouptitle'];
	}
	$space['extgroupids'] = implode(',', $newgroup);
}

$space['regdate'] = dgmdate($space['regdate']);
if($space['lastvisit']) $space['lastvisit'] = dgmdate($space['lastvisit']);
if($space['lastactivity']) {
	$space['lastactivitydb'] = $space['lastactivity'];
	$space['lastactivity'] = dgmdate($space['lastactivity']);
}
if($space['lastpost']) $space['lastpost'] = dgmdate($space['lastpost']);
if($space['lastsendmail']) $space['lastsendmail'] = dgmdate($space['lastsendmail']);


if($_G['uid'] == $space['uid'] || $_G['group']['allowviewip']) {
	require_once libfile('function/misc');
	$space['regip_loc'] = convertip($space['regip']);
	$space['lastip_loc'] = convertip($space['lastip']);
}

$space['buyerrank'] = 0;
if($space['buyercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['buyercredit'] <= $credit) {
			$space['buyerrank'] = $level;
			break;
		}
	}
}

$space['sellerrank'] = 0;
if($space['sellercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['sellercredit'] <= $credit) {
			$space['sellerrank'] = $level;
			break;
		}
	}
}

$space['attachsize'] = formatsize($space['attachsize']);

$space['timeoffset'] = empty($space['timeoffset']) ? '9999' : $space['timeoffset'];
if(strtotime($space['regdate']) + $space['oltime'] * 3600 > TIMESTAMP) {
	$space['oltime'] = 0;
}
require_once libfile('function/friend');
$isfriend = friend_check($space['uid'], 1);
if(!$_G['adminid']){
	if($_G['setting']['privacy']['view']['profile'] == 1 && !$isfriend && !$space['self']) {
		showmessage('specified_user_is_not_your_friend', '', array(), array());
	}
	if($_G['setting']['privacy']['view']['profile'] == 2 && !$space['self']) {
		showmessage('is_blacklist', '', array(), array());
	}
}
loadcache('profilesetting');
include_once libfile('function/profile');
$profiles = array();
$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

if($_G['setting']['verify']['enabled']) {
	space_merge($space, 'verify');
}
foreach($_G['cache']['profilesetting'] as $fieldid => $field) {
	if($_G['setting']['nsprofiles']) {
		break;
	}
	if(!$field['available'] || in_array($fieldid, array('birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
			continue;
	}
	if(
		$field['available'] && (strlen($space[$fieldid]) > 0 || ($fieldid == 'birthcity' && strlen($space['birthprovince']) || $fieldid == 'residecity' && strlen($space['resideprovince']))) &&
		($space['self'] || empty($privacy[$fieldid]) || ($isfriend && $privacy[$fieldid] == 1)) &&
		(!$_G['inajax'] && !$field['invisible'] || $_G['inajax'] && $field['showincard'])
	) {
		$val = profile_show($fieldid, $space);
		if($val !== false) {
			if($fieldid == 'realname' && $_G['uid'] != $space['uid'] && !ckrealname(1)) {
				continue;
			}
			if($field['formtype'] == 'file' && $val) {
				$imgurl = getglobal('setting/attachurl').'./profile/'.$val;
				$val = '<span><a href="'.$imgurl.'" target="_blank"><img src="'.$imgurl.'"  style="max-width: 500px;" /></a></span>';
			}
			if($val == '')  $val = '-';
			$profiles[$fieldid] = array('title'=>$field['title'], 'value'=>$val);
		}
	}
}

$count = C::t('forum_moderator')->count_by_uid($space['uid']);
if($count) {
	foreach(C::t('forum_moderator')->fetch_all_by_uid($space['uid']) as $result) {
		$moderatefids[] = $result['fid'];
	}
	$query = C::t('forum_forum')->fetch_all_info_by_fids($moderatefids);
	foreach($query as $result) {
		$manage_forum[$result['fid']] = $result['name'];
	}
}

if(!$_G['inajax'] && $_G['setting']['groupstatus']) {
	$groupcount = C::t('forum_groupuser')->fetch_all_group_for_user($space['uid'], 1);
	if($groupcount > 0) {
		$fids = C::t('forum_groupuser')->fetch_all_fid_by_uids($space['uid']);
		$usergrouplist = C::t('forum_forum')->fetch_all_info_by_fids($fids);
	}
}

if($space['medals']) {
        loadcache('medals');
        foreach($space['medals'] = explode("\t", $space['medals']) as $key => $medalid) {
                list($medalid, $medalexpiration) = explode("|", $medalid);
                if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
                        $space['medals'][$key] = $_G['cache']['medals'][$medalid];
                        $space['medals'][$key]['medalid'] = $medalid;
                } else {
                        unset($space['medals'][$key]);
                }
        }
}
$upgradecredit = $space['uid'] && $space['group']['type'] == 'member' && $space['group']['creditslower'] != 9999999 ? $space['group']['creditslower'] - $space['credits'] : false;
$allowupdatedoing = $space['uid'] == $_G['uid'] && checkperm('allowdoing');

dsetcookie('home_diymode', 1);

$navtitle = lang('space', 'sb_profile', array('who' => $space['username']));
$metakeywords = lang('space', 'sb_profile', array('who' => $space['username']));
$metadescription = lang('space', 'sb_profile', array('who' => $space['username']));

$showvideophoto = true;
if($space['videophotostatus'] > 0 && $_G['uid'] != $space['uid'] && !ckvideophoto($space, 1)) {
	$showvideophoto = false;
}

$clist = array();
if(in_array($_G['adminid'], array(1, 2, 3))) {
	include_once libfile('function/member');
	$clist = crime('getactionlist', $space['uid']);
}

show_view();

if(!$_G['privacy']) {
	if(!$_G['inajax']) {
		include_once template("home/space_profile");
	} else {
		$_GET['do'] = 'card';
		if(helper_access::check_module('follow')) {
			$follow = C::t('home_follow')->fetch_by_uid_followuid($_G['uid'], $space['uid']);
		}
		include_once template("home/space_card");
	}
}
?>