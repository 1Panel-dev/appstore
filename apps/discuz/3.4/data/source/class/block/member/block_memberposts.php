<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_memberposts.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_memberposts extends block_member {
	function block_memberposts() {
		$this->setting = array(
			'orderby' => array(
				'title' => 'memberlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('threads', 'memberlist_orderby_threads'),
					array('posts', 'memberlist_orderby_posts'),
					array('digestposts', 'memberlist_orderby_digestposts'),
				),
				'default' => 'threads'
			),
			'lastpost' => array(
				'title' => 'memberlist_lastpost',
				'type' => 'mradio',
				'value' => array(
					array('', 'memberlist_lastpost_nolimit'),
					array('3600', 'memberlist_lastpost_hour'),
					array('86400', 'memberlist_lastpost_day'),
					array('604800', 'memberlist_lastpost_week'),
					array('2592000', 'memberlist_lastpost_month'),
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
		return lang('blockclass', 'blockclass_member_script_memberposts');
	}
}

?>