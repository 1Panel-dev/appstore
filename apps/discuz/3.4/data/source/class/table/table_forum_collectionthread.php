<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collectionthread.php 34219 2013-11-14 08:09:32Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collectionthread extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collectionthread';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_ctid($ctid, $start = 0, $limit = 0, $distinct = 0) {
		if(!$ctid) {
			return null;
		}
		if($distinct == 1) {
			$sql = " GROUP BY tid";
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('ctid', $ctid).$sql.' ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table), 'tid');
	}

	public function fetch_by_ctid_dateline($ctid) {
		$data = $this->fetch_all_by_ctid($ctid, 0, 1);
		return $data ? current($data) : null;
	}

	public function fetch_all_by_tids($tids) {
		if(!$tids) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('tid', $tids), array($this->_table), 'ctid');
	}

	public function fetch_by_ctid_tid($ctid, $tid) {
		return DB::fetch_first('SELECT * FROM %t WHERE ctid=%d AND tid=%d', array($this->_table, $ctid, $tid));
	}

	public function fetch_all_by_ctid_tid($ctid, $tids) {
		if(!$ctid || !$tids) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE ctid=%d AND tid IN(%n)', array($this->_table, $ctid, $tids), 'tid');
	}

	public function delete_by_ctid($ctid) {
		if(!$ctid) {
			return false;
		}
		return DB::delete($this->_table, DB::field('ctid', $ctid));
	}

	public function delete_by_ctid_tid($ctid, $tid) {
		if(!$ctid && !$tid) {
			return false;
		}

		$condition = array();

		if($ctid) {
			$condition[] = DB::field('ctid', $ctid);
		}

		if($tid) {
			$condition[] = DB::field('tid', $tid);
		}

		return DB::delete($this->_table, implode(' AND ', $condition), 0, false);
	}
}

?>