<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 753 2008-11-14 06:48:25Z cnteacher $
*/

!defined('IN_UC') && exit('Access Denied');

class versioncontrol extends base {

	function __construct() {
		$this->versioncontrol();
	}

	function versioncontrol() {
		parent::__construct();
		$this->load('version');
	}

	function oncheck() {
		$db_version = $_ENV['version']->check();
		$return = array('file' => UC_SERVER_VERSION, 'db' => $db_version);
		return $return;
	}

}

?>