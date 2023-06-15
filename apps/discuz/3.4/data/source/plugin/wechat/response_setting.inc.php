<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: response_setting.inc.php 34817 2014-08-11 02:59:38Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

loadcache('wechat_response');
$response = & $_G['cache']['wechat_response'];

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

if(!submitcheck('menusubmit')) {

	showtips(lang('plugin/wechat', 'response_tips', array('url' => $url)));

	$responsehook = WeChatHook::getResponse();

	if($_GET['subscribe'] == 'custom') {
		$response['subscribeback'] = $responsehook['receiveEvent::subscribe'];
		$updatedata = array('receiveEvent::subscribe' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'custom'));
		$responsehook = WeChatHook::updateResponse($updatedata);
		savecache('wechat_response', $response);
		cpmsg(lang('plugin/wechat', 'response_subscribe_custom'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting', 'succeed');
	} elseif($_GET['subscribe'] == 'restore') {
		$response['subscribeback'] = $response['subscribeback'] ? $response['subscribeback'] : array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'subscribe');
		$updatedata = array('receiveEvent::subscribe' => $response['subscribeback']);
		$responsehook = WeChatHook::updateResponse($updatedata);
		savecache('wechat_response', $response);
		cpmsg(lang('plugin/wechat', 'response_subscribe_plugin'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting', 'succeed');
	}

	if($_GET['text'] == 'custom') {
		$response['textback'] = $responsehook['receiveMsg::text'];
		$updatedata = array('receiveMsg::text' => array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'text'));
		$responsehook = WeChatHook::updateResponse($updatedata);
		savecache('wechat_response', $response);
		cpmsg(lang('plugin/wechat', 'response_message_custom'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting', 'succeed');
	} elseif($_GET['text'] == 'restore') {
		$response['textback'] = $response['textback'] ? $response['textback'] : array('plugin' => 'wechat', 'include' => 'response.class.php', 'class' => 'WSQResponse', 'method' => 'text');
		$updatedata = array('receiveMsg::text' => $response['textback']);
		$responsehook = WeChatHook::updateResponse($updatedata);
		savecache('wechat_response', $response);
		cpmsg(lang('plugin/wechat', 'response_message_plugin'), 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting', 'succeed');
	}

	WeChatSetting::showResource();

	showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting');
	showtableheader();
	echo '<tr><th class="td25"></th><th style="width:350px"><strong>'.lang('plugin/wechat', 'response_keyword').'</strong></th><th><strong>'.lang('plugin/wechat', 'response_content').'</strong></th></tr>';

	if($responsehook['receiveEvent::subscribe']['plugin'] == 'wechat' &&
		$responsehook['receiveEvent::subscribe']['class'] == 'WSQResponse' &&
		$responsehook['receiveEvent::subscribe']['method'] == 'custom') {
		showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"",
			lang('plugin/wechat', 'response_subscribe'),
			"<p class=\"mbn\">".lang('plugin/wechat', 'response_custom')." ".($response['subscribeback'] ? "<a class=\"normal\" href=\"".ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=wechat&pmod=response_setting&subscribe=restore\">[".lang('plugin/wechat', 'response_switch_plugin_mode', array('plugin' => $response['subscribeback']['plugin']))."]</a></p>" : '')
		));
		showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"",
			"<i>".lang('plugin/wechat', 'subscribe')."</i>",
			"<textarea class=\"tarea\" name=\"response[subscribe]\" id=\"res_subscribe\" rows=\"5\" cols=\"40\">".dhtmlspecialchars($response['subscribe'])."</textarea>"
			."<br /><a href=\"javascript:;\" id=\"rsel\" onclick=\"showResource('res_subscribe')\">".lang('plugin/wechat', 'resource_select')."</a>"
		));
	} else {
		showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"",
			lang('plugin/wechat', 'response_subscribe'),
			"<p>".lang('plugin/wechat', 'response_plugin_mode', array('plugin' => $responsehook['receiveEvent::subscribe']['plugin']))." <a class=\"normal\" href=\"".ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=wechat&pmod=response_setting&subscribe=custom\">[".lang('plugin/wechat', 'response_switch_custom_mode')."]</a></p>"
		));
	}

	showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
		"",
		lang('plugin/wechat', 'response_access'),
		"<p class=\"mbn normal\">".lang('plugin/wechat', 'response_access_comment')
	));
	showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
		"",
		"<i>".lang('plugin/wechat', 'access')."</i>",
		"<textarea class=\"tarea\" name=\"response[access]\" id=\"res_access\" rows=\"5\" cols=\"40\">".dhtmlspecialchars($response['access'])."</textarea>"
		."<br /><a href=\"javascript:;\" id=\"rsel\" onclick=\"showResource('res_access')\">".lang('plugin/wechat', 'resource_select')."</a>"
	));

	showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
		"",
		lang('plugin/wechat', 'response_scan'),
		"<p class=\"mbn normal\">".lang('plugin/wechat', 'response_scan_comment')
	));
	showtablerow('', array('', 'class="td23 td28"', '', 'class="td29"'), array(
		"",
		"<i>".lang('plugin/wechat', 'scan')."</i>",
		"<textarea class=\"tarea\" name=\"response[scan]\" id=\"res_scan\" rows=\"5\" cols=\"40\">".dhtmlspecialchars($response['scan'])."</textarea>"
		."<br /><a href=\"javascript:;\" id=\"rsel\" onclick=\"showResource('res_scan')\">".lang('plugin/wechat', 'resource_select')."</a>"
	));

	if($responsehook['receiveMsg::text']['plugin'] == 'wechat' &&
		$responsehook['receiveMsg::text']['class'] == 'WSQResponse' &&
		$responsehook['receiveMsg::text']['method'] == 'text') {
		showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"",
			lang('plugin/wechat', 'response_message'),
			"<p class=\"mbn\">".lang('plugin/wechat', 'response_custom')." ".($response['textback'] ? "<a class=\"normal\" href=\"".ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=wechat&pmod=response_setting&text=restore\">[".lang('plugin/wechat', 'response_switch_plugin_mode', array('plugin' => $response['textback']['plugin']))."]</a></p>" : '')
		));

		foreach($response['text'] as $k => $text) {
			showtablerow('', array('', 'class="td23 td28"', 'class="td29"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"response[text][$k][delete]\" value=\"yes\">",
				"<div class=\"parentnode\"><input type=\"text\" class=\"txt\" size=\"30\" name=\"response[text][$k][keyword]\" value=\"".dhtmlspecialchars($text['keyword'])."\"></div>",
				"<textarea class=\"tarea\" name=\"response[text][$k][response]\" rows=\"5\" id=\"res_text_$k\" cols=\"40\">".dhtmlspecialchars($text['response'])."</textarea>"
				."<br /><a href=\"javascript:;\" id=\"rsel\" onclick=\"showResource('res_text_$k')\">".lang('plugin/wechat', 'resource_select')."</a>"
			));
		}
		echo '<tr><td></td><td class="td23 td28"></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.lang('plugin/wechat', 'response_add_message').'</a></div></td></tr>';

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
[[1,''], [1, '<input name="newresponse[keyword][]" value="" size="30" type="text" class="txt">'], [1, '<textarea class="tarea" name="newresponse[response][]" rows="5" cols="40"></textarea>', 'td29']],
];
</script>
EOT;

	} else {
		showtablerow('class="header"', array('', 'class="td23 td28"', '', 'class="td29"'), array(
			"",
			lang('plugin/wechat', 'response_message'),
			"<p>".lang('plugin/wechat', 'response_plugin_mode', array('plugin' => $responsehook['receiveMsg::text']['plugin']))." <a class=\"normal\" href=\"".ADMINSCRIPT."?action=plugins&operation=config&do=$pluginid&identifier=wechat&pmod=response_setting&text=custom\">[".lang('plugin/wechat', 'response_switch_custom_mode')."]</a></p>"
		));
	}

	showsubmit('menusubmit', 'submit', 'del');
	showtablefooter();
	showformfooter();

} else {

	if(!empty($_GET['newresponse'])) {
		foreach($_GET['newresponse']['keyword'] as $k => $keyword) {
			$item = array(
				'keyword' => $keyword,
				'response' => $_GET['newresponse']['response'][$k],
			);
			$response['text'][] = $item;
		}
	}

	foreach($_GET['response']['text'] as $k => $value) {
		if($value['delete']) {
			unset($response['text'][$k]);
			continue;
		}
		$response['text'][$k] = $value;
	}

	$response['subscribe'] = $_GET['response']['subscribe'];
	$response['access'] = $_GET['response']['access'];
	$response['scan'] = $_GET['response']['scan'];

	$query = array(
	    'subscribe' => $response['subscribe'],
	    'text' => array(),
	);

	foreach($response['text'] as $value) {
		$query['text'][$value['keyword']] = $value['response'];
	}

	$response['query'] = $query;

	savecache('wechat_response', $response);

	cpmsg('setting_update_succeed', 'action=plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=response_setting', 'succeed');

}

?>