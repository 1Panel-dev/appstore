<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_albumnew.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_album', 'class/block/space');

class block_albumnew extends block_album {
	function block_albumnew() {
		$this->setting = array(
			'catid' => array(
				'title' => 'albumlist_catid',
				'type' => 'mselect',
			),
			'titlelength' => array(
				'title' => 'albumlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'albumlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_album_script_albumnew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'updatetime';
		return parent::cookparameter($parameter);
	}
}

?>