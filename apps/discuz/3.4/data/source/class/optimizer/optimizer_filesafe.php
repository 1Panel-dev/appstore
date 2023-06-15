<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_filesafe {

	public function __construct() {

	}

	public function check() {
		$nosecurity = false;
		if(file_exists(DISCUZ_ROOT.'/old/') || file_exists(DISCUZ_ROOT.'/utility/') || file_exists(DISCUZ_ROOT.'/install/index.php') || file_exists(DISCUZ_ROOT.'/uc_server/install/index.php') || file_exists(DISCUZ_ROOT.'/ucenter/install/index.php') || file_exists(DISCUZ_ROOT.'/data/restore.php')) {
			$nosecurity = true;
		}
		if(strcmp(ADMINSCRIPT, 'admin.php') !== 0 && file_exists(DISCUZ_ROOT.'/admin.php')) {
			$nosecurity = true;
		}
		if($nosecurity) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_filesafe_need'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_filesafe_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		@unlink(DISCUZ_ROOT.'/install/index.php');
		@unlink(DISCUZ_ROOT.'/uc_server/install/index.php');
		@unlink(DISCUZ_ROOT.'/ucenter/install/index.php');
		@unlink(DISCUZ_ROOT.'/data/restore.php');
		if(strcmp(ADMINSCRIPT, 'admin.php') !== 0 && file_exists(DISCUZ_ROOT.'/admin.php')) {
			@unlink(DISCUZ_ROOT.'/admin.php');
		}
		cpmsg('optimizer_filesafe_optimizer', '', 'error');
	}
}