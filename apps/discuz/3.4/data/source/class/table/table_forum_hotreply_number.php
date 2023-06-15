<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_hotreply_number.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_hotreply_number extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_hotreply_number';
		$this->_pk = 'pid';

		parent::__construct();
	}

	public function fetch_all_by_pids($pids) {
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('pid', $pids), array($this->_table), 'pid');
	}

	public function fetch_all_by_tid_total($tid, $limit = 5) {
		return DB::fetch_all('SELECT * FROM %t WHERE tid=%d ORDER BY total DESC LIMIT %d', array($this->_table, $tid, $limit), 'pid');
	}

	public function fetch_by_pid($pid) {
		return DB::fetch_first('SELECT * FROM %t WHERE pid=%d', array($this->_table, $pid));
	}

	public function update_num($pid, $typeid) {
		$typename = $typeid == 1 ? 'support' : 'against';
		return DB::query('UPDATE %t SET '.$typename.'='.$typename.'+1, total=total+1 WHERE pid=%d', array($this->_table, $pid));
	}

	public function delete_by_tid($tid) {
		if(empty($tid)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE tid IN (%n)', array($this->_table, $tid));
	}

	public function delete_by_pid($pids) {
		if(empty($pids)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('pid', $pids), array($this->_table));
	}
}
?>