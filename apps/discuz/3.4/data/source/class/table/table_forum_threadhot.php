<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadhot.php 31913 2012-10-24 06:52:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class table_forum_threadhot extends discuz_table {
	public function __construct() {

		$this->_table = 'forum_threadhot';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_tid_by_cid($cid) {
		$tids = array();
		$cid = intval($cid);
		if($cid) {
			foreach(DB::fetch_all('SELECT * FROM %t WHERE cid=%d', array($this->_table, $cid)) as $value) {
				$tids[$value['tid']] = $value['tid'];
			}
		}
		return $tids;
	}

	public function insert_multiterm($dataarr) {
		$allkey = array('cid', 'fid', 'tid');
		$sql = array();
		foreach($dataarr as $key => $value) {
			if($value['cid'] && $value['fid'] && $value['tid']) {
				$cid = dintval($value['cid']);
				$fid = dintval($value['fid']);
				$tid = dintval($value['tid']);
				$sql[] = "($cid, $fid, $tid)";
			}
		}
		if($sql) {
			return DB::query('REPLACE INTO '.DB::table($this->_table)." (`cid`, `fid`, `tid`) VALUES ".implode(',', $sql), true);
		}
		return false;
	}
}

?>