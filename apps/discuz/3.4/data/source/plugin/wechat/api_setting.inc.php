<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: api_setting.inc.php 34754 2014-07-29 03:16:20Z nemohou $
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

if(submitcheck('orderssubmit')) {
	$apihook = WeChatHook::getAPIHook();
	foreach($apihook as $page => $hooks) {
		foreach($hooks as $hook => $rows) {
			foreach($rows as $plugin => $row) {
				if(isset($_GET['order'][$page][$hook][$plugin])) {
					$apihook[$page][$hook][$plugin]['order'] = $_GET['order'][$page][$hook][$plugin];
				}
				$apihook[$page][$hook][$plugin]['allow'] = !empty($_GET['allow'][$page][$hook][$plugin]) ? 1 : 0;
			}
			uasort($apihook[$page][$hook], 'pluginapicmp');
		}
	}
	$settings = array('mobileapihook' => serialize($apihook));
	C::t('common_setting')->update_batch($settings);
	updatecache('setting');
}

showtips(lang('plugin/wechat', 'wechatapi_tips'));

showformheader('plugins&operation=config&do='.$pluginid.'&identifier=wechat&pmod=api_setting');

$mobilewechatsetting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$mobilewechatsetting = (array)unserialize($mobilewechatsetting['mobilewechat']);

if($mobilewechatsetting['wsq_sitetoken']) {

	$apihook = WeChatHook::getAPIHook();

	$plugins = DB::fetch_all('SELECT identifier, name FROM %t', array('common_plugin'), 'identifier');

	showtableheader(lang('plugin/wechat', 'api_wsq'));
	echo '<tr class="header"><th>'.lang('plugin/wechat', 'api_hook').'</th><th>'.cplang('plugins_name').'</th><th>'.cplang('enable').'/'.cplang('display_order').'</th><th>'.lang('plugin/wechat', 'api_method').'</th></tr>';

	foreach($apihook as $page => $hooks) {
		foreach($hooks as $hook => $rows) {
			$i = 0;
			foreach($rows as $plugin => $row) {
				if(!$plugins[$plugin]) {
					$deleteplugins[] = $plugin;
				}
				$row['plugin'] = $plugin;
				echo '<tr class="hover"><td>'.(!$i ? $page.'_'.$hook : '').'</td>'.
					'<td>'.$plugins[$plugin]['name'].'</td>'.
					'<td><input class="checkbox" type="checkbox" name="allow['.$page.']['.$hook.']['.$plugin.']" value="1"'.($row['allow'] ? ' checked' : '').'>'.
					($hook != 'variables' ?
					'<input class="txt num" type="text" name="order['.$page.']['.$hook.']['.$plugin.']" value="'.$row['order'].'"></td>' :
					'</td>').
					'<td>'.formathook($row).'</td></tr>';
				$i++;
			}
		}
	}

	if($deleteplugins) {
		WeChatHook::delAPIHook($deleteplugins);
	}

	showsubmit('orderssubmit');
	showtablefooter();

	showformfooter();
}

$redirect = WeChatHook::getRedirect();
$response = WeChatHook::getResponse();

$plugins = DB::fetch_all('SELECT identifier, name FROM %t', array('common_plugin'), 'identifier');

showtableheader(lang('plugin/wechat', 'api_wechat'));
echo '<tr class="header"><th>'.lang('plugin/wechat', 'api_hook').'</th><th>'.cplang('plugins_name').'</th><th>'.lang('plugin/wechat', 'api_method').'</th></tr>';
if($redirect) {
	if(!$plugins[$redirect['plugin']]) {
		WeChatHook::updateRedirect(array());
	}
	echo '<tr class="hover"><td>'.lang('plugin/wechat', 'wechatapi_redirect').'</td><td>'.$plugins[$redirect['plugin']]['name'].'</td><td>'.formathook($redirect).'</td></tr>';
}
foreach($response as $k => $row) {
	if(!$plugins[$row['plugin']]) {
		$deleteresponses[$k] = array();
	}
	echo '<tr class="hover"><td>'.lang('plugin/wechat', 'api_'.$k).'('.$k.')</td><td>'.$plugins[$row['plugin']]['name'].'</td><td>'.formathook($row).'</td></tr>';
}
showtablefooter();

$wechatresponseExts = unserialize($_G['setting']['wechatresponseExts']);
if($wechatresponseExts) {
	showtableheader();
	foreach($wechatresponseExts as $extk => $response) {
		echo '<tr class="header"><th>'.lang('plugin/wechat', 'wechat_responseexts').' '.$extk.'</th><th>'.cplang('plugins_name').'</th><th>'.lang('plugin/wechat', 'api_method').'</th></tr>';
		foreach($response as $k => $row) {
			if(!$plugins[$row['plugin']]) {
				$deleteresponseExts[$extk][$k] = array();
			}
			echo '<tr class="hover"><td>'.lang('plugin/wechat', 'api_'.$k).'('.$k.')</td><td>'.$plugins[$row['plugin']]['name'].'</td><td>'.formathook($row).'</td></tr>';
		}
	}
	showtablefooter();
}

if($deleteresponses) {
	WeChatHook::updateResponse($deleteresponses);
}

if($deleteresponseExts) {
	foreach($deleteresponseExts as $extk => $deleteresponses) {
		WeChatHook::updateResponse($deleteresponses, $extk);
	}
}

$wechatappInfos = unserialize($_G['setting']['wechatappInfos']);
if($wechatappInfos) {
	showtableheader();
	echo '<tr class="header"><th width="200">'.lang('plugin/wechat', 'wechat_devids').'</th><th>'.lang('plugin/wechat', 'wechat_appId').'</th><th>'.lang('plugin/wechat', 'wechat_appsecret').'</th></tr>';
	foreach(unserialize($_G['setting']['wechatappInfos']) as $k => $info) {
		echo '<tr class="hover"><td>'.$k.'</td><td>'.$info['appId'].'</td><td>'.$info['appSecret'].'</td></tr>';
	}
	showtablefooter();
}

function formathook($hook) {
	return '<b>File:</b> '.$hook['plugin'].'/'.$hook['include'].' <b>Method:</b> '.$hook['class'].'->'.$hook['method'];
}

function pluginapicmp($a, $b) {
	return $a['order'] > $b['order'] ? 1 : -1;
}

?>