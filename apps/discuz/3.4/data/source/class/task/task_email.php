<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_email.php 6752 2010-03-25 08:47:54Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_email {

	var $version = '1.0';
	var $name = 'email_name';
	var $description = 'email_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function csc($task = array()) {
		global $_G;

		if($_G['member']['emailstatus']) {
			return true;
		}
		return array('csc' => 0, 'remaintime' => 0);
	}

	function view() {
		return lang('task/email', 'email_view');
	}

}

?>