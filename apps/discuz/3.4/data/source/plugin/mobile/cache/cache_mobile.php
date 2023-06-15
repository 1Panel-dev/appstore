<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_mobile.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_plugin_mobile() {
	if(!defined('DISCUZ_VERSION')) {
	    require './source/discuz_version.php';
	}

	global $_G;

	$setting = array();
	$settings = array('closeforumorderby');
	foreach($settings as $v) {
		$setting[$v] = $_G['setting'][$v];
	}

	if(in_array('mobile', $_G['setting']['plugins']['available'])) {
		$extendsetting = C::t('#mobile#mobile_setting')->fetch_all(array(
			'extend_used',
			'extend_lastupdate'
		));
		$array = array(
			'discuzversion' => DISCUZ_VERSION,
			'charset' => CHARSET,
			'version' => MOBILE_PLUGIN_VERSION,
			'pluginversion' => $_G['setting']['plugins']['version']['mobile'],
			'oemversion' => in_array('mobileoem', $_G['setting']['plugins']['available']) ? $_G['setting']['plugins']['version']['mobileoem'] : '0',
			'regname' => $_G['setting']['regname'],
			'qqconnect' => in_array('qqconnect', $_G['setting']['plugins']['available']) ? '1' : '0',
			'sitename' => $_G['setting']['bbname'],
			'mysiteid' => $_G['setting']['my_siteid'],
			'ucenterurl' => $_G['setting']['ucenterurl'],
			'setting' => $setting,
			'extends' => array('used' => $extendsetting['extend_used'], 'lastupdate' => $extendsetting['extend_lastupdate']),
		);
	} else {
		$array = array();
	}

	require_once './source/plugin/mobile/mobile.class.php';

	define('IN_MOBILE_API', 1);

	$data = array('mobilecheck' => mobile_core::json($array));
	writetocache('mobile', getcachevars($data));
}