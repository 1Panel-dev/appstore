<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blogspecified.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_blog', 'class/block/space');

class block_blogspecified extends block_blog {
	function block_blogspecified() {
		$this->setting = array(
			'blogids'	=> array(
				'title' => 'bloglist_blogids',
				'type' => 'text'
			),
			'uids'	=> array(
				'title' => 'bloglist_uids',
				'type' => 'text',
			),
			'catid' => array(
				'title' => 'bloglist_catid',
				'type'=>'mselect',
			),
			'titlelength' => array(
				'title' => 'bloglist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'bloglist_summarylength',
				'type' => 'text',
				'default' => 80
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_blog_script_blogspecified');
	}

}

?>