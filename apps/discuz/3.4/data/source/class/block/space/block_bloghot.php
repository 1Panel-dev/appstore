<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_bloghot.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_blog', 'class/block/space');

class block_bloghot extends block_blog {
	var $setting = array();

	function block_bloghot() {
		$this->setting = array(
			'hours' => array(
				'title' => 'bloglist_hours',
				'type' => 'mradio',
				'value' => array(
					array('', 'bloglist_hours_nolimit'),
					array('1', 'bloglist_hours_hour'),
					array('24', 'bloglist_hours_day'),
					array('168', 'bloglist_hours_week'),
					array('720', 'bloglist_hours_month'),
					array('8760', 'bloglist_hours_year'),
				),
				'default' => '720'
			),
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
		return lang('blockclass', 'blockclass_blog_script_bloghot');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'hot';
		return parent::cookparameter($parameter);
	}
}

?>