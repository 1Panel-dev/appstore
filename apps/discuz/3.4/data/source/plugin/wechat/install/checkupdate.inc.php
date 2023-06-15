<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: checkupdate.inc.php 35224 2015-03-03 09:48:38Z nemohou $
 */

define('PLUGIN_RELEASE', '20150303');

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pluginupdated = false;

$setting = $_G['setting']['mobilewechat'] ? (array)unserialize($_G['setting']['mobilewechat']) : array();

if($setting['RELEASE'] != PLUGIN_RELEASE) {

	$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_common_member_wechat (
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` char(32) NOT NULL default '',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `isregister` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `openid` (`openid`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wechat_authcode (
  `sid` char(6) NOT NULL,
  `code` int(10) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sid`),
  UNIQUE KEY `code` (`code`),
  KEY `createtime` (`createtime`)
) ENGINE=MEMORY;

CREATE TABLE IF NOT EXISTS pre_common_member_wechatmp (
  `uid` mediumint(8) unsigned NOT NULL,
  `openid` char(32) NOT NULL default '',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`),
  KEY `openid` (`openid`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wsq_threadlist (
  `skey` int(10) unsigned NOT NULL,
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wechat_resource (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `dateline` int(10) unsigned NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MYISAM;

CREATE TABLE IF NOT EXISTS pre_mobile_wechat_masssend (
    `id` int(10) unsigned NOT NULL auto_increment,
    `type` char(5) NOT NULL,
    `name` varchar(255) NOT NULL,
    `resource_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    `text` text,
    `media_id`  char(64) DEFAULT '',
    `created_at` int(10) unsigned NOT NULL,
    `sent_at` int(10) unsigned,
    `msg_id` int(10) unsigned,
    `res_status` varchar(50),
    `res_totalcount` int(10),
    `res_filtercount` int(10),
    `res_sentcount` int(10),
    `res_errorcount` int(10),
    `res_finish_at` int(10),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

EOF;

	if(!defined('DISCUZ_VERSION')) {
	    require './source/discuz_version.php';
	}

	$settingdefault = array (
		'wechat_mtype' => '0',
		'wechat_qrtype' => '3',
		'wechat_token' => random(16),
		'wechat_allowregister' => '1',
		'wechat_allowfastregister' => '1',
		'wechat_disableregrule' => '1',
		'wechat_float_qrcode' => '1',
		'wechat_confirmtype' => '0',
		'wechat_newusergroupid' => $_G['setting']['newusergroupid'],
		'wsq_wapdefault' => 1,
		'wsq_global_banner' => 1,
	);

	require_once DISCUZ_ROOT.'./source/plugin/wechat/install/update.func.php';

	runquery($sql);
	updatetable($sql);

	foreach($settingdefault as $_key => $_default) {
		if(!isset($setting[$_key])) {
			$setting[$_key] = $_default;
		}
	}
	$setting['RELEASE'] = PLUGIN_RELEASE;

	$settings = array('mobilewechat' => serialize($setting));
	C::t('common_setting')->update_batch($settings);

	C::t('common_plugin')->delete_by_identifier('mobileoem');

	require_once DISCUZ_ROOT.'./source/plugin/wechat/wechat.lib.class.php';

	$hook = WeChatHook::getAPIHook('wechat');
	if(!$hook) {
		WeChatHook::updateAPIHook(array(
			array('forumdisplay_variables' => array('plugin' => 'wechat', 'include' => 'wsqapi.class.php', 'class' => 'WSQAPI', 'method' => 'forumdisplay_variables')),
			array('viewthread_variables' => array('plugin' => 'wechat', 'include' => 'wsqapi.class.php', 'class' => 'WSQAPI', 'method' => 'viewthread_variables')),
		));
	} elseif($hook['wsqindex']) {
		WeChatHook::updateAPIHook(array(
			array('wsqindex_variables' => array('plugin' => 'wechat')),
		));
	}

	DB::query("ALTER TABLE ".DB::table('forum_debatepost')." ADD INDEX `voters` (`tid`,`voters`)", 'SILENT');

	$pluginupdated = true;

}

?>