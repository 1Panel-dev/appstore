<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_memberspecified.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_memberspecified extends block_member {
	function block_memberspecified() {
		$this->setting = array(
			'uids' => array(
				'title' => 'memberlist_uids',
				'type' => 'text'
			),
			'groupid' => array(
				'title' => 'memberlist_groupid',
				'type' => 'mselect',
				'value' => array()
			),
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
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_member_script_memberspecified');
	}
}

?>