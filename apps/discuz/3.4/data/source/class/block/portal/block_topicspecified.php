<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_topicspecified.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_topic', 'class/block/portal');

class block_topicspecified extends block_topic {
	function block_topicspecified() {
		$this->setting = array(
			'topicids'	=> array(
				'title' => 'topiclist_topicids',
				'type' => 'text',
				'value' => ''
			),
			'uids'	=> array(
				'title' => 'topiclist_uids',
				'type' => 'text',
				'value' => ''
			),
			'titlelength' => array(
				'title' => 'topiclist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'summary' => 'topiclist_summarylength',
				'type' => 'text',
				'default' => 80
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_topic_script_topicspecified');
	}
}

?>