<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_connect_bind.php 22196 2011-04-26 02:02:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_connect_bind {

	var $version = '1.0';
	var $name = 'connect_bind_name';
	var $description = 'connect_bind_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function csc($task = array()) {
		global $_G;

		if($_G['member']['conisbind']) {
			return true;
		}
		return array('csc' => 0, 'remaintime' => 0);
	}

	function view() {
		return lang('task/connect_bind', 'connect_bind_view');
	}

}

?>