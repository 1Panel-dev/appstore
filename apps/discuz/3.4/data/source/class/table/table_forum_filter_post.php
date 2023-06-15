<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_filter_post.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_filter_post extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_filter_post';
		$this->_pk = '';

		parent::__construct();
	}

	public function fetch_all_by_tid_pids($tid, $pid) {
		return DB::fetch_all('SELECT * FROM %t WHERE tid=%d AND pid IN(%n)', array($this->_table, $tid, $pid), 'pid');
	}

	public function fetch_all_by_tid_postlength_limit($tid, $limit = 10) {
		if($limit <= 0) {
			return array();
		}
		return DB::fetch_all('SELECT * FROM %t WHERE tid=%d ORDER BY postlength DESC LIMIT %d', array($this->_table, $tid, $limit), 'pid');
	}

	public function delete_by_tid_pid($tid, $pid) {
		if(empty($tid) || empty($pid)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE tid=%d AND pid=%d', array($this->_table, $tid, $pid));
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