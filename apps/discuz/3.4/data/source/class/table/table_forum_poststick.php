<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: table_forum_poststick.php 27806 2012-02-15 03:20:46Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_poststick extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_poststick';
		$this->_pk	  = '';

		parent::__construct();
	}

	public function fetch_all_by_tid($tid) {
		return DB::fetch_all('SELECT * FROM %t WHERE tid=%d ORDER BY dateline DESC', array($this->_table, $tid), 'pid');
	}


	public function count_by_pid($pid) {
		return DB::result_first('SELECT count(*) FROM %t WHERE pid=%d ', array($this->_table, $pid));
	}

	public function delete_by_pid($pids) {
		if(empty($pids)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('pid', $pids), array($this->_table));
	}

	public function delete_by_tid($tids) {
		if(empty($tids)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('tid', $tids), array($this->_table));
	}

	public function delete($tid, $pid) {
		return DB::query('DELETE FROM %t WHERE tid=%d AND pid=%d', array($this->_table, $tid, $pid));
	}

	public function count_by_tid($tid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE tid=%d', array($this->_table, $tid));
	}
}

?>