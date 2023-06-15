<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blank.php 27543 2012-02-03 08:56:21Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_blank extends commonblock_html {

	function block_blank() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_blank');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'content' => array(
				'title' => 'blank_content',
				'type' => 'mtextarea'
			)
		);
		return $settings;
	}

	function getdata($style, $parameter) {
		require_once libfile('function/home');
		$return = getstr($parameter['content'], '', 1, 0, 0, 1);
		return array('html' => $return, 'data' => null);
	}
}

?>