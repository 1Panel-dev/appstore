<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cloud_connect.php 33756 2013-08-10 06:32:48Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
$utilService = new Cloud_Service_Util();

require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Connect.php';
$connectService = new Cloud_Service_Connect();

$op = $_GET['op'];

$_GET['anchor'] = in_array($_GET['anchor'], array('setting', 'service')) ? $_GET['anchor'] : 'setting';
$current = array($_GET['anchor'] => 1);

if (!$_G['inajax']) {
	cpheader();
}

if ($_GET['anchor'] == 'setting') {

	$setting = C::t('common_setting')->fetch_all(array('extcredits', 'connect', 'connectsiteid', 'connectsitekey', 'regconnect', 'connectappid', 'connectappkey'));
	$setting['connect'] = (array)dunserialize($setting['connect']);

	if(!submitcheck('connectsubmit')) {

		include_once libfile('function/forumlist');
		$forumselect = array();
			$forumselect['t'] = '<select name="connectnew[t][fids][]" multiple="multiple" size="10">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
			if($setting['connect']['t']['fids']) {
				foreach($setting['connect']['t']['fids'] as $v) {
					$forumselect['t'] = str_replace('<option value="'.$v.'">', '<option value="'.$v.'" selected>', $forumselect['t']);
				}
			}

		$connectrewardcredits = $connectgroup = $connectguestgroup = '';
		$setting['extcredits'] = dunserialize($setting['extcredits']);
		for($i = 0; $i <= 8; $i++) {
			if($setting['extcredits'][$i]['available']) {
				$extcredit = 'extcredits'.$i.' ('.$setting['extcredits'][$i]['title'].')';
				$connectrewardcredits .= '<option value="'.$i.'" '.($i == intval($setting['connect']['register_rewardcredit']) ? 'selected' : '').'>'.($i ? $extcredit : $lang['none']).'</option>';
			}
		}

		$groups = C::t('common_usergroup')->fetch_all_by_type('special');
		foreach($groups as $group) {
			$connectgroup .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['connect']['register_groupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
			$connectguestgroup .= "<option value=\"$group[groupid]\" ".($group['groupid'] == $setting['connect']['guest_groupid'] ? 'selected' : '').">$group[grouptitle]</option>\n";
		}

		showformheader('plugins&operation=config&do='.$pluginid.'&identifier=qqconnect&pmod=admincp', 'connectsubmit');
		showtableheader();
		showsetting('connect_setting_allow', 'connectnew[allow]', $setting['connect']['allow'], 'radio', 0, 1);
		showsetting('App Id', 'connectappidnew', $setting['connectappid'], 'text', '', 0, $scriptlang['qqconnect']['connect_appid_desc']);
		showsetting('App Key', 'connectappkeynew', $setting['connectappkey'], 'text');
		showsetting('setting_access_guest_connect_group', '', '', '<select name="connectnew[guest_groupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$connectguestgroup.'</select>');
		showsetting('setting_access_register_connect_birthday', 'connectnew[register_birthday]', $setting['connect']['register_birthday'], 'radio');
		showsetting('setting_access_register_connect_gender', 'connectnew[register_gender]', $setting['connect']['register_gender'], 'radio');
		showsetting('setting_access_register_connect_uinlimit', 'connectnew[register_uinlimit]', $setting['connect']['register_uinlimit'], 'text');
		showsetting('setting_access_register_connect_credit', '', '', '<select name="connectnew[register_rewardcredit]">'.$connectrewardcredits.'</select>');
		showsetting('setting_access_register_connect_addcredit', 'connectnew[register_addcredit]', $setting['connect']['register_addcredit'], 'text');
		showsetting('setting_access_register_connect_group', '', '', '<select name="connectnew[register_groupid]"><option value="0">'.$lang['usergroups_system_0'].'</option>'.$connectgroup.'</select>');
		showsetting('setting_access_register_connect_regverify', 'connectnew[register_regverify]', $setting['connect']['register_regverify'], 'radio');
		showsetting('setting_access_register_connect_invite', 'connectnew[register_invite]', $setting['connect']['register_invite'], 'radio');
		showsetting('setting_access_register_connect_newbiespan', 'connectnew[newbiespan]', $setting['connect']['newbiespan'], 'text');
		showtagfooter('tbody');
		showsubmit('connectsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$_GET['connectnew'] = array_merge($setting['connect'], $_GET['connectnew']);
		$_GET['connectnew']['like_url'] = '';
		$_GET['connectnew']['turl_code'] = '';
		$connectnew = serialize($_GET['connectnew']);
		$regconnectnew = !$setting['connect']['allow'] && $_GET['connectnew']['allow'] ? 1 : $setting['regconnect'];
		C::t('common_setting')->update_batch(array(
			'regconnect' => $regconnectnew,
			'connect' => $connectnew,
			'connectappid' => $_GET['connectappidnew'],
			'connectappkey' => $_GET['connectappkeynew'],
		));

		updatecache(array('setting', 'fields_register', 'fields_connect_register'));
		cpmsg('connect_update_succeed', 'action=plugins&operation=config&do='.$pluginid.'&identifier=qqconnect&pmod=admincp', 'succeed');

	}
}