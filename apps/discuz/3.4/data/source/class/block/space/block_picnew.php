<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_picnew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_pic', 'class/block/space');

class block_picnew extends block_pic {
	function block_picnew() {
		$this->setting = array(
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
		return lang('blockclass', 'blockclass_pic_script_picnew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return parent::cookparameter($parameter);
	}
}

?>