<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_google.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_google extends commonblock_html {

	function block_google() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_google');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'lang' => array(
				'title' => 'google_lang',
				'type' => 'mradio',
				'value' => array(
					array('', 'google_lang_any'),
					array('en', 'google_lang_en'),
					array('zh-CN', 'google_lang_zh-CN'),
					array('zh-TW', 'google_lang_zh-TW')
				)
			),
			'default' => array(
				'title' => 'google_default',
				'type' => 'mradio',
				'value' => array(
					array(0, 'google_default_0'),
					array(1, 'google_default_1'),
				)
			),
			'client' => array(
				'title' => 'google_client',
				'type' => 'text',
			),
		);

		return $settings;
	}

	function getdata($style, $parameter) {
		$parameter = dhtmlspecialchars($this->cookparameter($parameter));
		$return = '<script type="text/javascript">var google_host="'.$_SERVER['HTTP_HOST'].'",google_charset="'.CHARSET.'",google_client="'.$parameter['client'].'",google_hl="'.$parameter['lang'].'",google_lr="'.($parameter['lang'] ? 'lang_'.$parameter['lang'] : '').'";google_default_0="'.($parameter['default'] == 0 ? ' selected' : '').'";google_default_1="'.($parameter['default'] == 1 ? ' selected' : '').'";</script><script type="text/javascript" src="static/js/google.js"></script>';
		return array('html' => $return, 'data' => null);
	}

}

?>