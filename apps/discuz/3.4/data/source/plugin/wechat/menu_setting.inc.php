<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: menu_setting.inc.php 35194 2015-02-02 02:37:34Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$setting = C::t('common_setting')->fetch_all(array('wechatmenu'));
$setting = (array)unserialize($setting['wechatmenu']);

require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

if(!$_G['wechat']['setting']['wechat_appId'] || !$_G['wechat']['setting']['wechat_appsecret']) {
	cpmsg(lang('plugin/wechat', 'wsq_menu_at_error'), '', 'error');
}

if(!submitcheck('menusubmit') && !submitcheck('pubsubmit')) {

	$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);

	if(in_array('plugin', $_G['setting']['rewritestatus'])) {
		$url = $_G['siteurl'].rewriteoutput('plugin', 1, 'wechat', 'access', '', 'op=access');
	} else {
		$url = $_G['siteurl'].'plugin.php?id=wechat:access';
	}

	showtips(lang('plugin/wechat', 'menu_tips', array('url' => $url)));

	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=menu_setting');
	showtableheader();
	echo '<tr class="header"><th class="td25"></th><th>'.$lang['display_order'].'</th><th style="width:350px">'.lang('plugin/wechat', 'wsq_menu_name').'</th><th>'.lang('plugin/wechat', 'wsq_menu_keyurl').'</th></tr>';

	foreach($setting['button'] as $k => $button) {
		$disabled = !empty($button['sub_button']) ? 'disabled' : '';
		showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"<input class=\"checkbox\" type=\"checkbox\" name=\"button[$k][delete]\" value=\"yes\" $disabled>",
			"<input type=\"text\" class=\"txt\" size=\"3\" name=\"button[$k][displayorder]\" value=\"$button[displayorder]\">",
			"<div class=\"parentnode\"><input type=\"text\" class=\"txt\" size=\"30\" name=\"button[$k][name]\" value=\"".dhtmlspecialchars($button['name'])."\"></div>",
			"<input type=\"text\" class=\"txt\" size=\"30\" name=\"button[$k][keyurl]\" value=\"".dhtmlspecialchars($button['keyurl'])."\">",
		));
		if(!empty($button['sub_button'])) {
			foreach($button['sub_button'] as $sk => $sub_button) {
				showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"button[$k][sub_button][$sk][delete]\" value=\"yes\">",
					"<input type=\"text\" class=\"txt\" size=\"3\" name=\"button[$k][sub_button][$sk][displayorder]\" value=\"$sub_button[displayorder]\">",
					"<div class=\"node\"><input type=\"text\" class=\"txt\" size=\"30\" name=\"button[$k][sub_button][$sk][name]\" value=\"".dhtmlspecialchars($sub_button['name'])."\"></div>",
					"<input type=\"text\" class=\"txt\" size=\"30\" name=\"button[$k][sub_button][$sk][keyurl]\" value=\"".dhtmlspecialchars($sub_button['keyurl'])."\">",
				));
			}
		}
		echo '<tr><td></td><td></td><td colspan="2"><div class="lastnode"><a href="###" onclick="addrow(this, 1, '.$k.')" class="addtr">'.lang('plugin/wechat', 'wsq_menu_sub_button').'</a></div></td></tr>';
	}
	echo '<tr><td></td><td class="td23 td28"></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.lang('plugin/wechat', 'wsq_menu_button').'</a></div></td></tr>';

	echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
[[1,''], [1,'<input name="newbutton[displayorder][]" value="" size="3" type="text" class="txt">', 'td23 td28'], [1, '<input name="newbutton[name][]" value="" size="30" type="text" class="txt">'], [1, '<input name="newbutton[keyurl][]" value="" size="30" type="text" class="txt">', 'td29']],
[[1,''], [1,'<input name="newsub_button[{1}][displayorder][]" value="" size="3" type="text" class="txt">', 'td23 td28'], [1, '<div class=\"node\"><input name="newsub_button[{1}][name][]" value="" size="30" type="text" class="txt"></div>'], [1, '<input name="newsub_button[{1}][keyurl][]" value="" size="30" type="text" class="txt">', 'td29']],
];
</script>
EOT;

	showsubmit('menusubmit', lang('plugin/wechat', 'wsq_menu_save'), 'del', '<input type="submit" class="btn" name="pubsubmit" value="'.lang('plugin/wechat', 'wsq_menu_pub').'" />');
	showtablefooter();
	showformfooter();

} else {

	if(!empty($_GET['newbutton'])) {
		foreach($_GET['newbutton']['name'] as $k => $name) {
			$button = array(
				'displayorder' => $_GET['newbutton']['displayorder'][$k],
				'name' => $name,
				'keyurl' => $_GET['newbutton']['keyurl'][$k],
			);
			$setting['button'][] = $button;
		}
	}

	foreach($_GET['button'] as $k => $value) {
		if($value['sub_button']) {
			foreach($value['sub_button'] as $sk => $v) {
				if($v['delete']) {
					unset($value['sub_button'][$sk]);
				}
			}
		}
		if($value['delete']) {
			unset($setting['button'][$k]);
			continue;
		}
		$setting['button'][$k] = $value;
		if(!empty($_GET['newsub_button'][$k])) {
			foreach($_GET['newsub_button'][$k]['name'] as $sk => $name) {
				$sub_button = array(
					'displayorder' => $_GET['newsub_button'][$k]['displayorder'][$sk],
					'name' => $name,
					'keyurl' => $_GET['newsub_button'][$k]['keyurl'][$sk],
				);
				$setting['button'][$k]['sub_button'][] = $sub_button;
			}
		}
		if(count($setting['button'][$k]['sub_button']) > 7) {
			cpmsg(lang('plugin/wechat', 'wsq_menu_sub_button_max'), '', 'error');
		}
		usort($setting['button'][$k]['sub_button'], 'buttoncmp');
	}

	if(count($setting['button']) > 3) {
		cpmsg(lang('plugin/wechat', 'wsq_menu_button_max'), '', 'error');
	}

	usort($setting['button'], 'buttoncmp');

	$settings = array('wechatmenu' => serialize($setting));
	C::t('common_setting')->update_batch($settings);
	updatecache('setting');

	if(submitcheck('pubsubmit')) {
		if(!$setting['button']) {
			cpmsg(lang('plugin/wechat', 'wsq_menu_button_pub_error'), '', 'error');
		}
		$pubmenu = array('button' => array());
		foreach($setting['button'] as $button) {
			if(!$button['sub_button']) {
				if(!$button['name']) {
					cpmsg(lang('plugin/wechat', 'wsq_menu_name_empty'), '', 'error');
				}
				if(!$button['keyurl']) {
					cpmsg(lang('plugin/wechat', 'wsq_menu_keyurl_empty'), '', 'error');
				}
				$parse = parse_url($button['keyurl']);
				$item = array(
					'type' => $parse['host'] ? 'view' : 'click',
					'name' => convertname($button['name']),
					$parse['host'] ? 'url' : 'key' => $button['keyurl']
				);
				$pubmenu['button'][] = $item;
			} else {
				if(!$button['name']) {
					cpmsg(lang('plugin/wechat', 'wsq_menu_name_empty'), '', 'error');
				}
				$sub_buttons = array();
				foreach($button['sub_button'] as $sub_button) {
					if(!$sub_button['name']) {
						cpmsg(lang('plugin/wechat', 'wsq_menu_name_empty'), '', 'error');
					}
					if(!$sub_button['keyurl']) {
						cpmsg(lang('plugin/wechat', 'wsq_menu_keyurl_empty'), '', 'error');
					}
					$parse = parse_url($sub_button['keyurl']);
					$item = array(
						'type' => $parse['host'] ? 'view' : 'click',
						'name' => convertname($sub_button['name']),
						$parse['host'] ? 'url' : 'key' => $sub_button['keyurl']
					);
					$sub_buttons[] = $item;
				}
				$item = array(
					'name' => convertname($button['name']),
					'sub_button' => $sub_buttons
				);
				$pubmenu['button'][] = $item;
			}
		}

		require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';

		$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

		$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);

		if($wechat_client->setMenu($pubmenu)) {
			cpmsg(lang('plugin/wechat', 'wsq_menu_pub_succeed'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=menu_setting', 'succeed');
		} else {
			cpmsg(lang('plugin/wechat', 'wsq_menu_pub_error', array('errno' => $wechat_client->error())), '', 'error');
		}
	} else {
		cpmsg('setting_update_succeed', 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=menu_setting', 'succeed');
	}

}

function convertname($str) {
	return urlencode(diconv($str, CHARSET, 'UTF-8'));
}

function buttoncmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

?>