<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_domain.php 32699 2013-03-01 02:54:05Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = in_array($operation, array('global', 'app', 'root')) ? $operation : 'global';
$current = array($operation => 1);

shownav('global', 'setting_domain');
showsubmenu('setting_domain', array(
	array('setting_domain_base', 'domain', $current['global']),
	array('setting_domain_app', 'domain&operation=app', $current['app']),
	array('setting_domain_root', 'domain&operation=root', $current['root']),
));
$navs = $_G['setting']['navs'];
if($operation == 'app') {

	if(!submitcheck('submit')) {
		$appkeyarr = array(
			'portal' => $navs[1]['navname'],
			'forum' => $navs[2]['navname'],
			'group' => $navs[3]['navname'],
			'home' => $lang['nav_home'],
			'mobile' => $lang['mobile'],
			'default' => $lang['default']
		);
		showtips('setting_domain_app_tips');

		showformheader('domain&operation=app');
		showtableheader();
		showsubtitle(array('name', 'setting_domain_app_domain'));
		$app = array();
		$hiddenarr = array();
		foreach($appkeyarr as $key => $desc) {
			if(in_array($key, array('portal', 'group')) && !helper_access::check_module($key) || ($key == 'home' && !helper_access::check_module('feed'))) {
				$hiddenarr["appnew[$key]"] = '';
			} else {
				showtablerow('', array('class="td25"', ''), array(
						$desc,
						"<input type=\"text\" class=\"txt\" style=\"width:50%;\" name=\"appnew[$key]\" value=\"".$_G['setting']['domain']['app'][$key]."\">".($key == 'mobile' ? cplang('setting_domain_app_mobile_tips') : '')
					));
			}
		}
		showsubmit('submit');
		showtablefooter();
		showhiddenfields($hiddenarr);
		showformfooter();
	} else {
		$olddomain = $_G['setting']['domain']['app'];
		$_G['setting']['domain']['app'] = array();
		$appset = false;
		foreach($_GET['appnew'] as $appkey => $domain) {
			$domain = strtolower($domain);
			if(!empty($domain) && !preg_match('/^((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}$/', $domain)) {
				cpmsg('setting_domain_http_error', '', 'error');
			}
			if(!empty($domain) && in_array($domain, $_G['setting']['domain']['app'])) {
				cpmsg('setting_domain_repeat_error', '', 'error');
			}
			if($appkey != 'default' && $domain) {
				$appset = true;
			}
			$_G['setting']['domain']['app'][$appkey] = $domain;
		}
		if($appset && !$_G['setting']['domain']['app']['default']) {
			cpmsg('setting_domain_need_default_error', '', 'error');
		}

		if($_GET['appnew']['mobile'] != $olddomain['mobile']) {
			C::t('common_nav')->update_by_identifier('mobile', array('url' => (!$_GET['appnew']['mobile'] ? 'forum.php?mobile=yes' : $_G['scheme'].'://'.$_GET['appnew']['mobile'])));
		}

		C::t('common_setting')->update('domain',$_G['setting']['domain']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=domain&operation=app', 'succeed');
	}

} elseif($operation == 'root') {

	$roottype = array(
		'home' => $lang['domain_home'],
		'group' => $navs[3]['navname'],
		'forum' => $lang['domain_forum'],
		'topic' => $lang['domain_topic'],
		'channel' => $lang['channel'],
	);
	if(!submitcheck('submit')) {
		showtips('setting_domain_root_tips');
		showformheader('domain&operation=root');
		showtableheader();
		showsubtitle(array('name', 'setting_domain_app_domain'));
		$hiddenarr = array();
		foreach($roottype as $type => $desc) {
			if(in_array($type, array('topic', 'channel')) && !helper_access::check_module('portal') || ($type == 'home' && !$_G['setting']['homepagestyle']) || ($type == 'group' && !helper_access::check_module('group'))) {
				$hiddenarr["domainnew[$type]"] = '';
			} else {
				$domainroot = $_G['setting']['domain']['root'][$type];
				showtablerow('', array('class="td25"', ''), array(
						$desc,
						"<input type=\"text\" class=\"txt\" style=\"width:50%;\" name=\"domainnew[$type]\" value=\"$domainroot\">"
					));
			}
		}
		showsubmit('submit');
		showtablefooter();
		showhiddenfields($hiddenarr);
		showformfooter();
	} else {
		$oldroot = $_G['setting']['domain']['root'];
		$_G['setting']['domain']['root'] = array();
		foreach($_GET['domainnew'] as $idtype => $domain) {
			$domain = strtolower($domain);
			if(!empty($domain) && !preg_match('/^((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}$/', $domain)) {
				cpmsg('setting_domain_http_error', '', 'error');
			}
			if($_G['setting']['domain']['root'][$idtype] != $domain) {
				$updatetype = $idtype == 'forum' ? array('forum', 'channel') : $idtype;
				C::t('common_domain')->update_by_idtype($updatetype, array('domainroot' => $domain));
			}
			$_G['setting']['domain']['root'][$idtype] = $domain;

		}
		C::t('common_setting')->update('domain', $_G['setting']['domain']);
		updatecache('setting');
		cpmsg('setting_update_succeed', 'action=domain&operation=root', 'succeed');
	}
} else {
	if(!submitcheck('domainsubmit')) {

		showtips('setting_domain_base_tips');
		showformheader("domain");
		showtableheader();
		if($_G['setting']['homepagestyle']) {
			showsetting('setting_domain_allow_space', 'settingnew[allowspacedomain]', $_G['setting']['allowspacedomain'], 'radio');
		} else {
			showhiddenfields(array('settingnew[allowspacedomain]' => 0));
		}
		if(helper_access::check_module('group')) {
			showsetting('setting_domain_allow_group', 'settingnew[allowgroupdomain]', $_G['setting']['allowgroupdomain'], 'radio');
		} else {
			showhiddenfields(array('settingnew[allowgroupdomain]' => 0));
		}
		showsetting('setting_domain_hold_domain', 'settingnew[holddomain]', $_G['setting']['holddomain'], 'text');
		showsubmit('domainsubmit');
		showtablefooter();
		showformfooter();
	} else {

		$settings = $_GET['settingnew'];
		$settings['allowspacedomain'] = (float)$settings['allowspacedomain'];
		$settings['allowgroupdomain'] = (float)$settings['allowgroupdomain'];
		if($settings) {
			C::t('common_setting')->update_batch($settings);
			updatecache('setting');

		}
		cpmsg('setting_update_succeed', 'action=domain', 'succeed');
	}
}
?>