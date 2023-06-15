<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_gift.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_gift {

	var $version = '1.0';
	var $name = 'gift_name';
	var $description = 'gift_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function preprocess($task) {
		dheader("Location: home.php?mod=task&do=draw&id=$task[taskid]");
	}

	function csc($task = array()) {
		return true;
	}

}


?>