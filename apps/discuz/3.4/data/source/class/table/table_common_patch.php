<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_patch.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_patch extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_patch';
		$this->_pk    = 'serial';

		parent::__construct();
	}

	public function fetch_all() {
		return DB::fetch_all("SELECT * FROM ".DB::table($this->_table));
	}

	public function fetch_max_serial() {
		return DB::result_first("SELECT serial FROM ".DB::table($this->_table)." ORDER BY serial DESC LIMIT 1");
	}

	public function update_status_by_serial($status, $serial, $condition = '') {
		return DB::query("UPDATE ".DB::table($this->_table)." SET ".DB::field('status', $status)." WHERE ".DB::field('serial', $serial, $condition));
	}

	public function fetch_needfix_patch($serials) {
		return DB::fetch_all("SELECT * FROM ".DB::table($this->_table)." WHERE ".DB::field('serial', $serials)." AND status<=0");
	}

	public function fetch_patch_by_status($status) {
		return DB::fetch_all("SELECT * FROM ".DB::table($this->_table)." WHERE ".DB::field('status', $status));
	}
}

?>