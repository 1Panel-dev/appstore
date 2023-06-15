<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadlog.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadlog extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_threadlog';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_all_order_by_dateline($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t ORDER BY dateline'.DB::limit($start, $limit), array($this->_table));
	}
	public function delete_by_tid_fid_uid($tids = null, $fids = array(), $uids = array()) {
		$condition = array();
		if($tids != null) {
			$condition[] = DB::field('tid', $tids);
		}
		if(!empty($fids)) {
			$condition[] = DB::field('fid', $fids);
		}
		if(!empty($uids)) {
			$condition[] = DB::field('uid', $uids);
		}
		return DB::delete($this->_table, DB::field('tid', $tids));
	}

}

?>