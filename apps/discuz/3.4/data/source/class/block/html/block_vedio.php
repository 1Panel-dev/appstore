<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_vedio.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_vedio extends commonblock_html {

	function block_vedio() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_vedio');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'url' => array(
				'title' => 'vedio_url',
				'type' => 'text',
				'default' => 'http://'
			),
			'width' => array(
				'title' => 'vedio_width',
				'type' => 'text',
				'default' => ''
			),
			'height' => array(
				'title' => 'vedio_height',
				'type' => 'text',
				'default' => ''
			),
		);

		return $settings;
	}

	function getdata($style, $parameter) {
		require_once libfile('function/discuzcode');
		$parameter['width'] = !empty($parameter['width']) ? intval($parameter['width']) : 'auto';
		$parameter['height'] = !empty($parameter['height']) ? intval($parameter['height']) : 'auto';
		$parameter['url'] = addslashes($parameter['url']);
		$return = parseflv($parameter['url'], $parameter['width'], $parameter['height']);
		if($return == false) {
			$return = $parameter['url'];
		}
		return array('html' => $return, 'data' => null);
	}
}

?>