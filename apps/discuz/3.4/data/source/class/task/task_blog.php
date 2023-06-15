<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_blog.php 24741 2011-10-10 03:41:51Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_blog {

	var $version = '1.0';
	var $name = 'blog_name';
	var $description = 'blog_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function csc($task = array()) {
		global $_G;

		if(C::t('home_blog')->count_by_uid($_G['uid'])) {
			return true;
		}
		return array('csc' => 0, 'remaintime' => 0);
	}

	function view() {
		return lang('task/blog', 'blog_view');
	}

}

?>