<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_grouphot.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_group', 'class/block/group');

class block_grouphot extends block_group {

	function block_grouphot() {
		$this->setting = array(
			'gtids' => array(
				'title' => 'grouplist_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'titlelength' => array(
				'title' => 'grouplist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'grouplist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'orderby' => array(
				'title' => 'grouplist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('threads', 'grouplist_orderby_threads'),
					array('posts', 'grouplist_orderby_posts'),
					array('todayposts', 'grouplist_orderby_todayposts'),
					array('membernum', 'grouplist_orderby_membernum'),
				),
				'default' => 'posts'
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_group_script_grouphot');
	}

}

?>