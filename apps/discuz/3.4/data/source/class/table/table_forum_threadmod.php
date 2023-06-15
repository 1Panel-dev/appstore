<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_threadmod.php 27913 2012-02-16 09:07:00Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_threadmod extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_threadmod';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_by_tid($tid) {
		return DB::fetch_first('SELECT * FROM %t WHERE tid=%d ORDER BY dateline DESC LIMIT 1', array($this->_table, $tid));
	}
	public function fetch_by_tid_action_status($tid, $action, $status = 1) {
		return DB::fetch_first('SELECT * FROM %t WHERE tid=%d AND action=%s AND status=%d ORDER BY dateline DESC LIMIT 1', array($this->_table, $tid, $action, $status));
	}
	public function fetch_by_tid_magicid($tid, $magicid = 0) {
		return DB::fetch_first('SELECT * FROM %t WHERE tid=%d AND magicid=%d', array($this->_table, $tid, $magicid));
	}
	public function fetch_all_by_tid_magicid($tid, $magicid = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE tid=%d AND magicid=%d', array($this->_table, $tid, $magicid));
	}
	public function fetch_all_by_tid($tid, $action = '', $start = 0, $limit = 0) {
		$tid = dintval($tid, true);
		$parameter = array($this->_table, $tid);
		$wherearr = array();
		$wherearr[] = is_array($tid) && $tid ? 'tid IN(%n)' : 'tid=%d';
		if($action) {
			$parameter[] = $action;
			$wherearr[] = is_array($action) && $action ? 'action IN(%n)' : 'action=%s';
		}
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY dateline DESC ".DB::limit($start, $limit), $parameter);
	}
	public function fetch_all_by_expiration_status($expiration, $status=1) {
		return DB::fetch_all('SELECT * FROM %t WHERE expiration>0 AND expiration<%d AND status=%d', array($this->_table, $expiration, $status));
	}
	public function fetch_all_recyclebin_by_dateline($dateline, $start = 0, $limit = 0) {
		return DB::fetch_all("SELECT tm.tid FROM %t tm, %t t WHERE tm.action='DEL' AND tm.dateline<%d AND t.tid=tm.tid AND t.displayorder=-1".DB::limit($start, $limit), array($this->_table, 'forum_thread', $dateline));
	}
	public function count_by_tid_magicid($tid, $magicid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE tid=%d AND magicid=%d', array($this->_table, $tid, $magicid));
	}
	public function delete_by_dateline($dateline) {
		$dateline = dintval($dateline);
		return DB::delete($this->_table, DB::field('tid', 0, '>').' AND '.DB::field('dateline', $dateline, '<'));
	}
	public function update_by_tid_action($tids, $action, $data) {
		$tids = dintval($tids, true);
		if(!empty($data) && is_array($data) && $tids) {
			return DB::update($this->_table, $data, DB::field('tid', $tids).' AND '.DB::field('action', $action));
		}
		return 0;
	}
	public function delete_by_tid($tids) {
		$tids = dintval($tids, true);
		if($tids) {
			return DB::delete($this->_table, DB::field('tid', $tids));
		}
		return 0;
	}

}

?>