<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_groupactivitynew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_groupactivity', 'class/block/group');

class block_groupactivitynew extends block_groupactivity {
	function block_groupactivitynew() {
		$this->setting = array(
			'gtids' => array(
				'title' => 'groupactivity_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'class' => array(
				'title' => 'groupactivity_class',
				'type' => 'select',
				'value' => array()
			),
			'gviewperm' => array(
				'title' => 'groupactivity_gviewperm',
				'type' => 'mradio',
				'value' => array(
					array('0', 'groupactivity_gviewperm_only_member'),
					array('1', 'groupactivity_gviewperm_all_member')
				),
				'default' => '1'
			),
			'titlelength' => array(
				'title' => 'groupactivity_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'groupactivity_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'groupactivity_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_groupactivity_script_groupactivitynew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return parent::cookparameter($parameter);
	}
}

?>