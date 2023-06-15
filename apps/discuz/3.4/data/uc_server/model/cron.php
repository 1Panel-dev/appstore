<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: cron.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class cronmodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->cronmodel($base);
	}

	function cronmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function note_delete_user() {
	}

	function note_delete_pm() {
		$data = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."badwords");
		return $data;
	}

}

?>