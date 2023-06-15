<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_mytask.php 27777 2012-02-14 07:07:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_mytask extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_mytask';
		$this->_pk    = '';

		parent::__construct();
	}

	public function delete($uid, $taskid) {
		$condition = array();
		if($uid) {
			$condition[] = DB::field('uid', $uid);
		}
		if($taskid) {
			$condition[] = DB::field('taskid', $taskid);
		}
		DB::delete($this->_table, implode(' AND ', $condition));
	}

	public function update($uid, $taskid, $data) {
		if(!$data || !is_array($data)) {
			return;
		}
		$condition = array();
		if($uid) {
			$condition[] = DB::field('uid', $uid);
		}
		if($taskid) {
			$condition[] = DB::field('taskid', $taskid);
		}
		DB::update($this->_table, $data, implode(' AND ', $condition));
	}

	public function count($uid, $taskid = false, $status = false) {
		$taskid = $taskid !== false ? 'AND taskid='.intval($taskid) : '';
		$status = $status !== false ? 'AND status='.intval($status) : '';
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE uid=%d %i %i", array($this->_table, $uid, $taskid, $status));
	}

	public function delete_exceed($exceedtime) {
		DB::query("DELETE FROM %t WHERE status='-1' AND dateline<%d", array($this->_table, TIMESTAMP - intval($exceedtime)), false, true);
	}

	public function fetch_all_by_taskid($taskid, $limit) {
		return DB::fetch_all("SELECT * FROM %t WHERE taskid=%d ORDER BY dateline DESC LIMIT 0, %d", array($this->_table, $taskid, $limit));
	}

	public function fetch($uid, $taskid) {
		return DB::fetch_first("SELECT * FROM %t WHERE uid=%d AND taskid=%d", array($this->_table, $uid, $taskid));
	}

	public function update_to_success($uid, $taskid, $timestamp) {
		return DB::query('UPDATE '.DB::table($this->_table)." SET status = '1', csc = '100', dateline = '".intval($timestamp)."' WHERE taskid = '".intval($taskid)."' AND uid = '".intval($uid)."' AND status != '1'");
	}

}

?>