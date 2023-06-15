<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_doinghot.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_doing', 'class/block/space');

class block_doinghot extends block_doing {
	var $setting = array();

	function block_doinghot() {
		$this->setting = array(
			'titlelength' => array(
				'title' => 'doinglist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'doinglist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_doing_script_doinghot');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'replynum';
		return parent::cookparameter($parameter);
	}
}

?>