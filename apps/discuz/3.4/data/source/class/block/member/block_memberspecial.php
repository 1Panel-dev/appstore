<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_memberspecial.php 25525 2011-11-14 04:39:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_memberspecial extends block_member {
	function block_memberspecial() {
		$this->setting = array(
			'special' => array(
				'title' => 'memberlist_special',
				'type' => 'mradio',
				'value' => array(
					array('', 'memberlist_special_nolimit'),
					array('0', 'memberlist_special_hot'),
					array('1', 'memberlist_special_default'),
				),
				'default' => ''
			),
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_member_script_memberspecial');
	}

	function cookparameter($parameter) {
		if($parameter['special'] === '') {
			$parameter['special'] = -1;
		} else {
			$parameter['special'] = intval($parameter['special']);
		}
		$parameter['orderby'] = 'special';
		return parent::cookparameter($parameter);
	}
}

?>