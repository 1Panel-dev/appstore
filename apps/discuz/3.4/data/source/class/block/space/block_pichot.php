<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_pichot.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_pic', 'class/block/space');

class block_pichot extends block_pic {
	function block_pichot() {
		$this->setting = array(
			'hours' => array(
				'title' => 'piclist_hours',
				'type' => 'mradio',
				'value' => array(
					array('', 'piclist_hours_nolimit'),
					array('1', 'piclist_hours_hour'),
					array('24', 'piclist_hours_day'),
					array('168', 'piclist_hours_week'),
					array('720', 'piclist_hours_month'),
					array('8760', 'piclist_hours_year'),
				),
				'default' => '720'
			),
			'titlelength' => array(
				'title' => 'piclist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'piclist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_pic_script_pichot');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'hot';
		return parent::cookparameter($parameter);
	}
}

?>