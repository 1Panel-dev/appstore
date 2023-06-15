<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_connect_postfeedlog.php 31305 2012-08-09 06:36:16Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_connect_postfeedlog extends discuz_table {

	public function __construct() {
		$this->_table = 'connect_postfeedlog';
		$this->_pk = 'flid';

		parent::__construct();
	}

	public function fetch_by_pid($pid) {

		return DB::fetch_first('SELECT * FROM %t WHERE pid=%d', array($this->_table, $pid));
	}

	public function update_by_pid($pid, $data) {
		$pid = dintval($pid);
		return DB::update($this->_table, $data, DB::field('pid', $pid));
	}
}