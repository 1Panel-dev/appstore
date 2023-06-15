<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: log.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class control extends adminbase {

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		$this->check_priv();
		if(!$this->user['isfounder'] && !$this->user['allowadminlog']) {
			$this->message('no_permission_for_this_module');
		}
		$this->check_priv();
	}

	function onls() {
		$logdir = UC_ROOT.'data/logs/';
		$dir = opendir($logdir);
		$logs = $loglist = array();
		while($entry = readdir($dir)) {
			if(is_file($logdir.$entry) && strpos($entry, '.php') !== FALSE) {
				$logs = array_merge($logs, file($logdir.$entry));
			}
		}
		closedir($dir);

		$logs = array_reverse($logs);
		foreach($logs AS $k => $v) {
			if(count($v = explode("\t", $v)) > 1) {
				$v[3] = $this->date($v[3]);
				$v[4] = $this->lang[$v[4]];
				$loglist[$k] = $v;
			}
		}

		$page = max(1, intval($_GET['page']));
		$start = ($page - 1) * UC_PPP;

		$num = count($loglist);
		$multipage = $this->page($num, UC_PPP, $page, 'admin.php?m=log&a=ls');
		$loglist = array_slice($loglist, $start, UC_PPP);

		$this->view->assign('loglist', $loglist);
		$this->view->assign('multipage', $multipage);

		$this->view->display('admin_log');

	}

}

?>