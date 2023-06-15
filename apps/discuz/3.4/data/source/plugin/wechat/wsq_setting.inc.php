<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsq_setting.inc.php 35197 2015-02-03 08:11:53Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($_GET['updated'])) {
	require_once DISCUZ_ROOT.'./source/plugin/wechat/install/checkupdate.inc.php';
	if($pluginupdated) {
		updatecache(array('plugin', 'setting'));
		$url = $_SERVER['REQUEST_URI'].(strexists($_SERVER['REQUEST_URI'], '?') ? '&' : '?').'updated=yes';
		dheader('location: '.$url);
	}
}

$setting = C::t('common_setting')->fetch_all(array('mobilewechat', 'mobile'));
$mobilesetting = (array)unserialize($setting['mobile']);
$setting = (array)unserialize($setting['mobilewechat']);

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

if(!$setting['wsq_sitetoken']) {
	cpmsg(lang('plugin/wechat', 'wsq_api_register_close'), '', 'error');
}

if(!empty($_GET['recheck'])) {
	wsq::recheck();
	$siteinfo = wsq::info();
	$setting['wsq_status'] = $siteinfo->res->status;
	$setting['wsq_lastrequest'] = $siteinfo->res->lasttime;
	$settings = array('mobilewechat' => serialize($setting));
	C::t('common_setting')->update_batch($settings);
}

if(!submitcheck('settingsubmit')) {

	if($setting['wsq_siteid']) {
		if(in_array('plugin', $_G['setting']['rewritestatus'])) {
			$url = $_G['siteurl'].rewriteoutput('plugin', 1, 'wechat', 'access');
		} else {
			$url = $_G['siteurl'].'plugin.php?id=wechat:access';
		}
	}

	$apilisturl = ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wsq_setting&viewapi=yes';

	$setting['wsq_siteurl'] = $setting['wsq_siteurl'] ? $setting['wsq_siteurl'] : $_G['siteurl'];
	$setting['wsq_sitename'] = $setting['wsq_sitename'] ? $setting['wsq_sitename'] : $_G['setting']['bbname'];

	require_once libfile('function/forumlist');
	$forums = '<select name="setting[wsq_fid]"><option value="">'.cplang('plugins_empty').'</option>'.forumselect(FALSE, 0, $setting['wsq_fid'], TRUE).'</select>';

	$sitelogo = $setting['wsq_sitelogo'] ? '<img src="'.$setting['wsq_sitelogo'].'" width="150" />' : '';
	$qrcode = $setting['wechat_qrcode'] ? '<img src="'.$_G['setting']['attachurl'].'common/'.$setting['wechat_qrcode'].'" width="150" />' : '';

	$apicredits = '<option value="0">'.cplang('none').'</option>';
	foreach($_G['setting']['extcredits'] as $i => $credit) {
		$extcredit = 'extcredits'.$i.' ('.$credit['title'].')';
		$apicredits .= '<option value="'.$i.'" '.($i == intval($setting['wsq_apicredit']) ? 'selected' : '').'>'.$extcredit.'</option>';
	}

	$setting['wechat_forumdisplay_reply'] = isset($setting['wechat_forumdisplay_reply']) ? $setting['wechat_forumdisplay_reply'] : 1;

	showtips(lang('plugin/wechat', 'wsq_tips', array('ADMINSCRIPT' => ADMINSCRIPT.'?action=', 'apiurl' => $apilisturl)));
	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wsq_setting', 'enctype');

	showtableheader(lang('plugin/wechat', 'wechat_wsq_setting').' '.($setting['wsq_status'] ? ' ('.lang('plugin/wechat', 'wsq_status_open').')' : ($setting['wsq_siteid'] ? ' ('.lang('plugin/wechat', 'wsq_status_ing').(TIMESTAMP - $setting['wsq_lastrequest'] > 3600 ? ' <a href="'.ADMINSCRIPT.'?action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wsq_setting&recheck=yes">['.lang('plugin/wechat', 'wsq_status_try').']</a>)' : ')') : ' ('.lang('plugin/wechat', 'wsq_status_close').')')));

	showsetting(lang('plugin/wechat', 'wsq_allow'), 'setting[wsq_allow]', $setting['wsq_allow'], 'radio', 0, 0, lang('plugin/wechat', 'wsq_allow_comment'));
	if($setting['wsq_allow'] && $setting['wsq_siteid']) {
		showtablefooter();
		showtableheader();
		showsetting(lang('plugin/wechat', 'wsq_url'), '', '', '<span style="white-space:nowrap">'.$url.'</span>');
		showtablefooter();
		showtableheader();
		showsetting(lang('plugin/wechat', 'wsq_siteid'), '', '', $setting['wsq_siteid']);
		showsetting(lang('plugin/wechat', 'wsq_sitetoken'), '', '', $setting['wsq_sitetoken']);
	}
	showsetting(lang('plugin/wechat', 'wsq_sitename'), 'setting[wsq_sitename]', $setting['wsq_sitename'], 'text');
	showsetting(lang('plugin/wechat', 'wsq_sitelogo'), 'wsq_sitelogo', $setting['wsq_sitelogo'], 'file', 0, 0, lang('plugin/wechat', 'wsq_sitelogo_comment', array('sitelogo' => $sitelogo)));
	showsetting(lang('plugin/wechat', 'wsq_sitesummary'), 'setting[wsq_sitesummary]', $setting['wsq_sitesummary'], 'textarea');
	showsetting(lang('plugin/wechat', 'wsq_siteurl'), 'setting[wsq_siteurl]', $setting['wsq_siteurl'], 'text', 0, 0, lang('plugin/wechat', 'wsq_siteurl_comment'));
	showsetting(lang('plugin/wechat', 'wsq_siteip'), 'setting[wsq_siteip]', $setting['wsq_siteip'], 'text', 0, 0, lang('plugin/wechat', 'wsq_siteip_comment'));
	showsetting(lang('plugin/wechat', 'wsq_fid'), '', '', $forums, 0, 0, lang('plugin/wechat', 'wsq_fid_comment'));
	if(!empty($_G['setting']['domain']['root']['forum'])) {
		showsetting(lang('plugin/wechat', 'wsq_domain'), '', '', 'http://<input type="text" name="setting[wsq_domain]" class="txt" value="'.$setting['wsq_domain'].'" style="width:100px; margin-right:0px;" >.'.$_G['setting']['domain']['root']['forum'], !function_exists('domain_create'), 0, lang('plugin/wechat', 'wsq_domain_comment'));
	} else {
		showsetting(lang('plugin/wechat', 'wsq_domain'), 'setting[wsq_domain]', '', 'text', 1, 0, lang('plugin/wechat', 'wsq_domain_comment'));
	}
	showsetting(lang('plugin/wechat', 'wechat_forumdisplay_reply'), 'setting[wechat_forumdisplay_reply]', $setting['wechat_forumdisplay_reply'], 'radio');
	showsetting(lang('plugin/wechat', 'wechat_float_qrcode'), 'setting[wechat_float_qrcode]', $setting['wechat_float_qrcode'], 'radio', 0, 1);
	showsetting(lang('plugin/wechat', 'wechat_float_text'), 'setting[wechat_float_text]', $setting['wechat_float_text'], 'text');
	showtagfooter('tbody');
	showsetting(lang('plugin/wechat', 'wsq_wapdefault'), 'setting[wsq_wapdefault]', $setting['wsq_wapdefault'], 'radio');
	showsetting(lang('plugin/wechat', 'wsq_apicredit'), '', '', '<select name="setting[wsq_apicredit]">'.$apicredits.'</select>', 0, 0, lang('plugin/wechat', 'wsq_apicredit_comment'));
	showsubmit('settingsubmit');
	showtablefooter();

	showformfooter();

} else {

	if($_FILES['wsq_sitelogo']['tmp_name']) {
		$upload = new discuz_upload();
		if(!$upload->init($_FILES['wsq_sitelogo'], 'common', random(3, 1), random(8)) || !$upload->save()) {
			cpmsg($upload->errormessage(), '', 'error');
		}
		$parsev = parse_url($_G['setting']['attachurl']);
		$_GET['setting']['wsq_sitelogo'] = ($parsev['host'] ? '' : $_G['siteurl']).$_G['setting']['attachurl'].'common/'.$upload->attach['attachment'];
	} else {
		$_GET['setting']['wsq_sitelogo'] = $setting['wsq_sitelogo'];
	}

	if(!$_GET['setting']['wsq_fid']) {
		cpmsg(lang('plugin/wechat', 'wsq_fid_empty'), '', 'error');
	}

	if(!$setting['wsq_sitetoken']) {
		$siteinfo = wsq::register(
			$_GET['setting']['wsq_sitename'],
			$_GET['setting']['wsq_siteurl'],
			$_GET['setting']['wsq_sitelogo'],
			$_GET['setting']['wsq_sitesummary'],
			$setting['wechat_mtype'],
			$setting['wechat_qrtype'],
			$_GET['setting']['wsq_siteip'],
			$setting['wechat_followurl'],
			$setting['wechat_appId'],
			$setting['wechat_appsecret'],
			$_GET['setting']['wsq_global_banner'],
			$_GET['setting'] + $setting
		);
		if(!$siteinfo || $siteinfo->code) {
            if($siteinfo->code == 14) {
                cpmsg(lang('plugin/wechat', 'wsq_api_servertime_error', '', 'error'));
            }
			cpmsg(lang('plugin/wechat', 'wsq_api_register_error'), '', 'error');
		}
		$_GET['setting']['wsq_siteid'] = $siteinfo->res->siteid;
		$_GET['setting']['wsq_sitetoken'] = $siteinfo->res->token;
	} else {
		$siteinfo = wsq::edit(
			$_GET['setting']['wsq_sitename'],
			$_GET['setting']['wsq_siteurl'],
			$_GET['setting']['wsq_sitelogo'],
			$_GET['setting']['wsq_sitesummary'],
			$setting['wechat_mtype'],
			$setting['wechat_qrtype'],
			$_GET['setting']['wsq_siteip'],
			$setting['wechat_followurl'],
			$setting['wechat_appId'],
			$setting['wechat_appsecret'],
			$_GET['setting']['wsq_global_banner'],
			$_GET['setting'] + $setting
		);
		if(!$siteinfo || $siteinfo->code) {
            if($siteinfo->code == 14) {
                cpmsg(lang('plugin/wechat', 'wsq_api_servertime_error', '', 'error'));
            }
			cpmsg(lang('plugin/wechat', 'wsq_api_edit_error'), '', 'error');
		}
	}

	if(function_exists('domain_create')) {
		if(isset($_GET['setting']['wsq_domain'])) {
			$_GET['setting']['wsq_domain'] = strtolower($_GET['setting']['wsq_domain']);
		}
		if(!empty($_GET['setting']['wsq_domain']) && !preg_match('/^((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}$/', $_GET['setting']['wsq_domain'])) {
			cpmsg('setting_domain_http_error', '', 'error');
		}
	}

	$_GET['setting']['wsq_status'] = $siteinfo->res->status;
	$_GET['setting']['wsq_lastrequest'] = $siteinfo->res->lasttime;
	$settings = array('mobilewechat' => serialize($_GET['setting'] + $setting));
	if(!$mobilesetting['allowmobile']) {
		$mobilesetting['allowmobile'] = 1;
		$settings['mobile'] = serialize($mobilesetting);
	}
	C::t('common_setting')->update_batch($settings);

	updatecache('setting');

	if($_GET['setting']['wsq_allow']) {
		WeChatHook::updateResponse(array(
			'receiveMsg::text' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'text'),
			'receiveEvent::click' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'click'),
			'receiveEvent::subscribe' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'subscribe'),
			'receiveEvent::scan' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'scan'),
		));
		WeChatHook::updateRedirect(array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'redirect'));
		WeChatHook::updateAPIHook(array(
			array('forumdisplay_variables' => array('plugin' => 'wechat', 'include' => 'wsqapi.class.php', 'class' => 'WSQAPI', 'method' => 'forumdisplay_variables')),
			array('viewthread_variables' => array('plugin' => 'wechat', 'include' => 'wsqapi.class.php', 'class' => 'WSQAPI', 'method' => 'viewthread_variables')),
		));
		WeChatHook::updateViewPluginId('wechat');
		if(!in_array('mobile', $_G['setting']['plugins']['available'])) {
			$plugin = C::t('common_plugin')->fetch_by_identifier('mobile');
			if(!$plugin) {
				cpmsg(lang('plugin/wechat', 'wsq_mobile_plugin_error'), '', 'error');
			}
			C::t('common_plugin')->update($plugin['pluginid'], array('available' => 1));
			updatecache(array('plugin', 'setting'));
		}
	} else {
		$wechatredirect = WeChatHook::getRedirect();
		if($wechatredirect['plugin'] == 'wechat') {
			$wechatredirect = array();
		}
		WeChatHook::updateRedirect($wechatredirect);
		WeChatHook::updateViewPluginId('');
	}

	if(function_exists('domain_create') && $_G['setting']['domain']['root']['forum']) {
		if($_GET['setting']['wsq_domain']) {
			domain_create('wechat', $_GET['setting']['wsq_domain'], $_G['setting']['domain']['root']['forum']);
		} else {
			domain_delete('wechat');
		}
	}

	cpmsg('setting_update_succeed', 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=wsq_setting', 'succeed');
}

?>