<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_follow_feed.php 28364 2012-02-28 07:31:23Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_follow_feed extends discuz_table
{
	private $_ids = array();
	private $_cids = array();
	private $_tids = array();
	private $_archiver_table = 'home_follow_feed_archiver';

	public function __construct() {

		$this->_table = 'home_follow_feed';
		$this->_pk    = 'feedid';

		parent::__construct();
	}

	public function fetch_all_by_uid($uids = 0, $archiver = false, $start = 0, $limit = 0) {

		$data = array();
		$parameter = array($archiver ? $this->_archiver_table : $this->_table);
		$wherearr = array();
		if(!empty($uids)) {
			$uids = dintval($uids, true);
			$wherearr[] = is_array($uids) && $uids ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uids;
		}
		$wheresql = !empty($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		$query = DB::query("SELECT * FROM %t $wheresql ORDER BY dateline DESC ".DB::limit($start, $limit), $parameter);
		while($row = DB::fetch($query)) {
			$data[$row['feedid']] = $row;
			$this->_tids[$row['tid']] = $row['tid'];
		}

		return $data;
	}

	public function fetch_all_by_dateline($dateline, $glue = '>=') {
		$glue = helper_util::check_glue($glue);
		return DB::fetch_all("SELECT * FROM %t WHERE dateline{$glue}%d ORDER BY dateline", array($this->_table, $dateline), $this->_pk);
	}

	public function fetch_by_feedid($feedid, $archiver = false) {
		return DB::fetch_first("SELECT * FROM %t WHERE feedid=%d", array($archiver ? $this->_archiver_table : $this->_table, $feedid));
	}

	public function count_by_uid_tid($uid, $tid, $archiver = false) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d AND tid=%d', array($archiver ? $this->_archiver_table : $this->_table, $uid, $tid));
	}

	public function count_by_uid_dateline($uids = array(), $dateline = 0, $archiver = 0) {
		$count = 0;
		$parameter = array($archiver ? $this->_archiver_table : $this->_table);
		$wherearr = array();
		if(!empty($uids)) {
			$uids = dintval($uids, true);
			$wherearr[] = is_array($uids) && $uids ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uids;
		}
		if($dateline) {
			$wherearr[] = "dateline>%d";
			$parameter[] = $dateline;
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		$count = DB::result_first("SELECT COUNT(*) FROM %t $wheresql", $parameter);
		return $count;
	}

	public function insert_archiver($data) {
		if(!empty($data) && is_array($data)) {
			return DB::insert($this->_archiver_table, $data, false, true);
		}
		return 0;
	}

	public function delete_by_feedid($feedid, $archiver = false) {
		$feedid = dintval($feedid, true);
		if($feedid) {
			return DB::delete($archiver ? $this->_archiver_table : $this->_table, DB::field('feedid', $feedid));
		}
		return 0;
	}

	public function delete_by_uid($uids) {
		$uids = dintval($uids, true);
		$delnum = 0;
		if($uids) {
			$delnum = DB::delete($this->_table, DB::field('uid', $uids));
			$delnum += DB::delete($this->_archiver_table, DB::field('uid', $uids));
		}
		return $delnum;
	}

	public function get_ids() {
		return $this->_ids;
	}

	public function get_tids() {
		return $this->_tids;
	}

	public function get_cids() {
		return $this->_cids;
	}

}

?>