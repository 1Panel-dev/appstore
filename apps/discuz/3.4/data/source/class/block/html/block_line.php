<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_line.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_line extends commonblock_html {

	function block_line() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_line');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'style' => array(
				'title' => 'line_style',
				'type' => 'mradio',
				'value' => array(
					array('dash', 'line_style_dash'),
					array('line', 'line_style_line'),
				),
				'default' => 'dash'
			)
		);

		return $settings;
	}

	function getdata($style, $parameter) {
		$class = $parameter['style'] == 'line' ? 'l' : 'da';
		$return = "<hr class='$class' />";
		return array('html' => $return, 'data' => null);
	}
}

?>