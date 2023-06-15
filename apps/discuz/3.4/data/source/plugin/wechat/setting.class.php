<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: setting.class.php 35205 2015-02-12 01:39:25Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class WeChatSetting {

	function showResource() {
		echo '<div id="rsel_menu" class="custom cmain" style="display:none;width:1000px;height:540px">'
			. '<div class="cnote" style="width:100%"><span class="right"><a href="###" class="flbc" onclick="hideMenu();return false;"></a></span><h3>'.lang('plugin/wechat', 'resource_select').'</h3></div>'
			. '<div id="rsel_content" style="overflow:hidden;height:95%"></div></div>';

		$adminscript = ADMINSCRIPT;

echo <<<EOF
<script>
var showResourceId = null;
function showResource(id) {
	showMenu({'ctrlid':'rsel','evt':'click','duration':3,'pos':'00'});
	showResourceId = id;
	ajaxget('$adminscript?action=plugins&operation=config&identifier=wechat&pmod=resource_setting&ac=select', 'rsel_content');
}
function selResource(id, text) {
	$(showResourceId).value = '[resource=' + id + '] ' + text;
	hideMenu();
}
</script>
EOF;

	}

	function menu() {
		global $_G;
		$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

echo <<<EOF
<style>
.floattop { display: none; }
.floattopempty { display: none; }
.mymenu { height:35px; }
.mymenu .floattop { display: inline; }
.mymenu .floattopempty { display: inline; }
</style>
EOF;

		echo '<div class="mymenu">';
		if($_G['wechat']['setting']['wsq_sitetoken']) {
			showsubmenu(lang('plugin/wechat', 'menu_root'), array(
				array(array('menu' => lang('plugin/wechat', 'menu_wsq'), 'submenu' => array(
					array(lang('plugin/wechat', 'menu_wsq_base'), 'plugins&operation=config&identifier=wechat&pmod=wsq_setting', $_GET['pmod'] == 'wsq_setting'),
					array(lang('plugin/wechat', 'menu_wsq_show'), 'plugins&operation=config&identifier=wechat&pmod=showactivity_setting', $_GET['pmod'] == 'showactivity_setting'),
					array(lang('plugin/wechat', 'menu_wsq_stat'), 'plugins&operation=config&identifier=wechat&pmod=wsq_stat', $_GET['pmod'] == 'wsq_stat'),
				))),
				array(array('menu' => lang('plugin/wechat', 'menu_wechat'), 'submenu' => array(
					array(lang('plugin/wechat', 'menu_wechat_base'), 'plugins&operation=config&identifier=wechat&pmod=wechat_setting', $_GET['pmod'] == 'wechat_setting'),
					array(lang('plugin/wechat', 'menu_wechat_msg'), 'plugins&operation=config&identifier=wechat&pmod=response_setting', $_GET['pmod'] == 'response_setting'),
					array(lang('plugin/wechat', 'menu_wechat_menu'), 'plugins&operation=config&identifier=wechat&pmod=menu_setting', $_GET['pmod'] == 'menu_setting'),
					array(lang('plugin/wechat', 'menu_wechat_resource'), 'plugins&operation=config&identifier=wechat&pmod=resource_setting', $_GET['pmod'] == 'resource_setting'),
					array(lang('plugin/wechat', 'menu_wechat_masssend'), 'plugins&operation=config&identifier=wechat&pmod=masssend_setting', $_GET['pmod'] == 'masssend_setting'),
				))),
				array(lang('plugin/wechat', 'menu_app'), 'plugins&operation=config&identifier=wechat&pmod=wsq_app', $_GET['pmod'] == 'wsq_app'),
				array(lang('plugin/wechat', 'menu_api'), 'plugins&operation=config&identifier=wechat&pmod=api_setting', $_GET['pmod'] == 'api_setting'),
			));
		} else {
			showsubmenu(lang('plugin/wechat', 'menu_root'), array(
				array(array('menu' => lang('plugin/wechat', 'menu_wechat'), 'submenu' => array(
					array(lang('plugin/wechat', 'menu_wechat_base'), 'plugins&operation=config&identifier=wechat&pmod=wechat_setting', $_GET['pmod'] == 'wechat_setting'),
					array(lang('plugin/wechat', 'menu_wechat_msg'), 'plugins&operation=config&identifier=wechat&pmod=response_setting', $_GET['pmod'] == 'response_setting'),
					array(lang('plugin/wechat', 'menu_wechat_menu'), 'plugins&operation=config&identifier=wechat&pmod=menu_setting', $_GET['pmod'] == 'menu_setting'),
					array(lang('plugin/wechat', 'menu_wechat_resource'), 'plugins&operation=config&identifier=wechat&pmod=resource_setting', $_GET['pmod'] == 'resource_setting'),
					array(lang('plugin/wechat', 'menu_wechat_masssend'), 'plugins&operation=config&identifier=wechat&pmod=masssend_setting', $_GET['pmod'] == 'masssend_setting'),
				))),
				array(lang('plugin/wechat', 'menu_api'), 'plugins&operation=config&identifier=wechat&pmod=api_setting', $_GET['pmod'] == 'api_setting'),
			));
		}
		echo '</div>';

		if($_G['wechat']['setting']['wsq_siteid']) {
			$time = TIMESTAMP;

echo <<<EOF
<script>
function pubEventCallbackCommon(re) {
	if(re.errCode) {
		return;
	}
	if(typeof re.data.event.peId != 'undefined') {
		$('pubEventNum').innerHTML = 'New!';
		$('pubEventNum').style.display = '';
	}
}
</script>
<script src="http://api.wsq.qq.com/publicEvent?sId={$_G[wechat][setting][wsq_siteid]}&resType=jsonp&isAjax=1&_=$time&isDiscuz=1&callback=pubEventCallbackCommon">
</script>
EOF;

		}
	}


}