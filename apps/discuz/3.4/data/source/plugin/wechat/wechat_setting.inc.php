<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wechat_setting.inc.php 34891 2014-08-20 07:24:39Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$setting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$setting = (array)unserialize($setting['mobilewechat']);
$apiurl = $_G['siteurl'].'api/mobile/?module=wechat';

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

if(isset($_GET['viewapi'])) {

	dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=api_setting');

}

if(!submitcheck('settingsubmit')) {

	if(!$setting['wechat_token']) {
		$setting['wechat_token'] = random(16);
		$settings = array('mobilewechat' => serialize($setting));
		C::t('common_setting')->update_batch($settings);
		updatecache('setting');
	}

	$groupselect = array();
	foreach(C::t('common_usergroup')->range_orderby_credit() as $group) {
		if($group['type'] != 'member' || $_G['setting']['newusergroupid'] == $group['groupid']) {
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.($setting['wechat_newusergroupid'] == $group['groupid'] ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
		}
	}
	$usergroups = '<select name="setting[wechat_newusergroupid]"><option value="">'.cplang('plugins_empty').'</option>'.
		'<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
		($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
		($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
		'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';

	showtips(lang('plugin/wechat', 'wechat_tips', array('url' => $apiurl)));
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wechat_setting', 'enctype');

	showtableheader();
	showsetting(lang('plugin/wechat', 'wechat_mptype'), array('setting[wechat_mtype]', array(
		array(0, lang('plugin/wechat', 'wechat_mptype_0'), array('qrcode' => 'none')),
		array(2, lang('plugin/wechat', 'wechat_mptype_2'), array('qrcode' => 'none')),
	)), $setting['wechat_mtype'], 'mradio', 0, 0, lang('plugin/wechat', 'wechat_mptype_comment'));
	showtagheader('tbody', 'qrcode', $setting['wechat_mtype'] == 1);
	showsetting(lang('plugin/wechat', 'wechat_qrcode'), 'wechat_qrcode', '', 'file', 0, 0, lang('plugin/wechat', 'wechat_qrcode_comment', array('qrcode' => $qrcode)));
	showtagfooter('tbody');
	showtablefooter();

	showtableheader(lang('plugin/wechat', 'wechat_func_setting'));
	showsetting(lang('plugin/wechat', 'wechat_allowregister'), 'setting[wechat_allowregister]', $setting['wechat_allowregister'], 'radio', 0, 1, lang('plugin/wechat', 'wechat_allowregister_comment'));
	showsetting(lang('plugin/wechat', 'wechat_allowfastregister'), 'setting[wechat_allowfastregister]', $setting['wechat_allowfastregister'], 'radio', 0, 0, lang('plugin/wechat', 'wechat_allowfastregister_comment'));
	showsetting(lang('plugin/wechat', 'wechat_disableregrule'), 'setting[wechat_disableregrule]', $setting['wechat_disableregrule'], 'radio', 0, 0, lang('plugin/wechat', 'wechat_disableregrule_comment'));
	showsetting(lang('plugin/wechat', 'wechat_confirmtype'), 'setting[wechat_confirmtype]', $setting['wechat_confirmtype'], 'radio', 0, 0, lang('plugin/wechat', 'wechat_confirmtype_comment'));
	showsetting(lang('plugin/wechat', 'wechat_newusergroupid'), '', '', $usergroups, 0, 0, lang('plugin/wechat', 'wechat_newusergroupid_comment'));
	showtagfooter('tbody');
	showsetting(lang('plugin/wechat', 'wechat_followurl'), 'setting[wechat_followurl]', $setting['wechat_followurl'], 'text', 0, 0, lang('plugin/wechat', 'wechat_followurl_comment'));
	showtagfooter('tbody');

	showtableheader(lang('plugin/wechat', 'wechat_service_setting'));
	showsetting(lang('plugin/wechat', 'wechat_url'), '', '', '<span style="white-space:nowrap">'.$apiurl.'</span>');
	showsetting(lang('plugin/wechat', 'wechat_token'), 'setting[wechat_token]', $setting['wechat_token'], 'text');
	showtablefooter();

	showtableheader(lang('plugin/wechat', 'wechat_devid_setting'));
	showsetting(lang('plugin/wechat', 'wechat_appId'), 'setting[wechat_appId]', $setting['wechat_appId'], 'text');
	showsetting(lang('plugin/wechat', 'wechat_appsecret'), 'setting[wechat_appsecret]', $setting['wechat_appsecret'], 'text');
	showtablefooter();

	showtableheader();
	showsubmit('settingsubmit');
	showtablefooter();

	showformfooter();

} else {

	if($_GET['setting']['wechat_mtype'] == 2 && !$_GET['setting']['wechat_appId']) {
		cpmsg(lang('plugin/wechat', 'wechat_at_need'), '', 'error');
	}

	if($_GET['setting']['wechat_appId'] && $_GET['setting']['wechat_appsecret']) {
		require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
		$wechat_client = new WeChatClient($_GET['setting']['wechat_appId'], $_GET['setting']['wechat_appsecret']);
		if(!$wechat_client->getAccessToken(1, 1)) {
			cpmsg(lang('plugin/wechat', 'wechat_at_geterror'), '', 'error');
		}
		$option = array(
			'scene_id' => 100000,
			'expire' => 30,
			'ticketOnly' => 1
		);
		$ticket = $wechat_client->getQrcodeTicket($option);
		if(!$wechat_client->getQrcodeImgUrlByTicket($ticket)) {
			cpmsg(lang('plugin/wechat', 'wechat_at_qrgeterror'), '', 'error');
		}
	}

	$_GET['setting']['wechat_qrtype'] = !$_GET['setting']['wechat_mtype'] ? 3 : 0;
	$_GET['setting']['wechat_token'] = $_GET['setting']['wechat_token'] ? $_GET['setting']['wechat_token'] : random(16);

	if($_FILES['wechat_qrcode']['tmp_name']) {
		$upload = new discuz_upload();
		if(!$upload->init($_FILES['wechat_qrcode'], 'common', random(3, 1), random(8)) || !$upload->save()) {
			cpmsg($upload->errormessage(), '', 'error');
		}
		$_GET['setting']['wechat_qrcode'] = $upload->attach['attachment'];
	}

	if($_GET['setting']['wechat_followurl']) {
		$_GET['setting']['wechat_followurl'] = (!preg_match('/^https?:\/\//', $_GET['setting']['wechat_followurl']) ? 'http://' : '').$_GET['setting']['wechat_followurl'];
		$parse = parse_url($_GET['setting']['wechat_followurl']);
		if(!$parse['host'] || $parse['host'] != 'mp.weixin.qq.com') {
			cpmsg(lang('plugin/wechat', 'wsq_followurl_error'), '', 'error');
		}
	}

	if($setting['wsq_siteid']) {
		$siteinfo = wsq::edit($setting['wsq_sitename'],
			$setting['wsq_siteurl'],
			$setting['wsq_sitelogo'],
			$setting['wsq_sitesummary'],
			$_GET['setting']['wechat_mtype'],
			$_GET['setting']['wechat_qrtype'],
			$setting['wsq_siteip'],
			$_GET['setting']['wechat_followurl'],
			$_GET['setting']['wechat_appId'],
			$_GET['setting']['wechat_appsecret'],
			$_GET['setting'] + $setting
		);
		if(!$siteinfo || $siteinfo->code) {
			cpmsg(lang('plugin/wechat', 'wsq_api_edit_error'), '', 'error');
		}
	}

	$settings = array('mobilewechat' => serialize($_GET['setting'] + $setting));
	C::t('common_setting')->update_batch($settings);
	updatecache('setting');

	cpmsg('setting_update_succeed', 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wechat_setting', 'succeed');
}

function formathook($hook) {
	return '<b>File:</b> '.$hook['plugin'].'/'.$hook['include'].' <b>Method:</b> '.$hook['class'].'->'.$hook['method'];
}

?>