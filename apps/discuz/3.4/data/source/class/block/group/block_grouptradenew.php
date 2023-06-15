<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_grouptradenew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_grouptrade', 'class/block/group');

class block_grouptradenew extends block_grouptrade {
	function block_grouptradenew() {
		$this->setting = array(
			'gtids' => array(
				'title' => 'grouptrade_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'gviewperm' => array(
				'title' => 'grouptrade_gviewperm',
				'type' => 'mradio',
				'value' => array(
					array('0', 'grouptrade_gviewperm_only_member'),
					array('1', 'grouptrade_gviewperm_all_member')
				),
				'default' => '1'
			),
			'titlelength' => array(
				'title' => 'grouptrade_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'grouptrade_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'grouptrade_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_grouptrade_script_grouptradenew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return parent::cookparameter($parameter);
	}
}

?>