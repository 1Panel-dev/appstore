<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_topicnew.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_topic', 'class/block/portal');

class block_topicnew extends block_topic {
	function block_topicnew() {
		$this->setting = array(
			'picrequired' => array(
				'title' => 'topiclist_picrequired',
				'type' => 'radio',
				'default' => '0'
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
			),
			'startrow' => array(
				'title' => 'topiclist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_topic_script_topicnew');
	}

	function cookeparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return $parameter;
	}
}

?>