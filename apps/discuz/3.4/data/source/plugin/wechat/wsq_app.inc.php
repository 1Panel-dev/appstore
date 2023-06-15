<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wsq_app.inc.php 35205 2015-02-12 01:39:25Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$setting = C::t('common_setting')->fetch_all(array('mobilewechat'));
$setting = (array)unserialize($setting['mobilewechat']);

if(!$setting['wsq_sitetoken']) {
	cpmsg(lang('plugin/wechat', 'wsq_api_register_close'), '', 'error');
}

require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/wsq.class.php';
require_once DISCUZ_ROOT.'./source/plugin/wechat/setting.class.php';
WeChatSetting::menu();

showtableheader(lang('plugin/wechat', 'wsq_viewapp_pubevent'));
echo '<tr><td style="line-height:30px" id="pubevent">'.lang('plugin/wechat', 'wsq_viewapp_na').'</td></tr>';
showtablefooter();

showtableheader(lang('plugin/wechat', 'wsq_viewapp_local'));
echo '<tr><td style="line-height:30px">'.lang('plugin/wechat', 'wsq_viewapp_local_comment').'</td></tr>';
showtablefooter();

showtableheader(lang('plugin/wechat', 'wsq_viewapp_online'));
echo '<tr><td style="line-height:30px">'.lang('plugin/wechat', 'wsq_viewapp_online_comment').'</td></tr>';
showtablefooter();

if($setting['wsq_siteid']) {
	$time = TIMESTAMP;

echo <<<EOF
<script>
function pubEventCallback(re) {
	if(re.errCode) {
		return;
	}
	if(typeof re.data.event.peId != 'undefined') {
		$('pubevent').innerHTML = '<h1><a href="http://mp.wsq.qq.com" target="_blank">' + re.data.event.peTitle + '</a></h1>' + re.data.event.peContent;
	}
}
</script>
<script src="http://api.wsq.qq.com/publicEvent?sId={$setting[wsq_siteid]}&resType=jsonp&isAjax=1&_=$time&isDiscuz=1&callback=pubEventCallback">
</script>
EOF;

}