<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blognew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_blog', 'class/block/space');

class block_blognew extends block_blog {
	var $setting = array();

	function block_blognew() {
		$this->setting = array(
			'catid' => array(
				'title' => 'bloglist_catid',
				'type'=>'mselect',
			),
			'picrequired' => array(
				'title' => 'bloglist_picrequired',
				'type' => 'radio',
				'default' => '0'
			),
			'titlelength' => array(
				'title' => 'bloglist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'bloglist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'bloglist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_blog_script_blognew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return parent::cookparameter($parameter);
	}
}

?>