<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_collectioncomment.php 28611 2012-03-06 07:48:24Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_collectioncomment extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_collectioncomment';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function delete_by_ctid($ctid) {
		if(!$ctid) {
			return false;
		}
		return DB::delete($this->_table, DB::field('ctid', $ctid));
	}

	public function delete_by_cid_ctid($cids, $ctid = 0) {
		if(!$cids) {
			return false;
		}
		if($ctid != 0) {
			$ctidsql = ' AND ctid=\''.dintval($ctid).'\'';
		}
		return DB::query("DELETE FROM %t WHERE cid IN (%n) $ctidsql", array($this->_table, $cids));
	}

	public function delete_by_uid($uid) {
		if(!$uid) {
			return false;
		}
		return DB::query("DELETE FROM %t WHERE %i", array($this->_table, DB::field('uid', $uid)));
	}

	public function fetch_all_by_ctid($ctid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE ctid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $ctid), $this->_pk);
	}

	public function fetch_all_by_uid($uid) {
		if(!$uid) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('uid', $uid)), $this->_pk);
	}

	public function fetch_rate_by_ctid_uid($ctid, $uid) {
		return DB::result_first('SELECT rate FROM %t WHERE ctid=%d AND uid=%d AND rate!=0', array($this->_table, $ctid, $uid), $this->_pk);
	}

	public function fetch_all_for_search($cid, $ctid, $username, $uid, $useip, $rate, $message, $starttime, $endtime, $start = 0, $limit = 20) {
		$where = '1';

		$where .= $cid ? ' AND '.DB::field('cid', $cid) : '';
		$where .= $ctid ? ' AND '.DB::field('ctid', $ctid) : '';
		$where .= $username ? ' AND '.DB::field('username', '%'.stripsearchkey($username).'%', 'like') : '';
		$where .= $uid ? ' AND '.DB::field('uid', $uid) : '';
		$where .= $useip ? ' AND '.DB::field('useip', stripsearchkey($useip).'%', 'like') : '';
		$where .= $rate ? ' AND '.DB::field('rate', $rate, '>') : '';
		$where .= $message ? ' AND '.DB::field('message', '%'.stripsearchkey($message).'%', 'like') : '';
		$where .= $starttime != '' ? ' AND '.DB::field('dateline', $starttime, '>') : '';
		$where .= $endtime != '' ? ' AND '.DB::field('dateline', $endtime, '<') : '';

		if($start == -1) {
			return DB::result_first("SELECT count(*) FROM %t WHERE %i", array($this->_table, $where));
		}
		return DB::fetch_all("SELECT * FROM %t WHERE %i ORDER BY dateline DESC %i", array($this->_table, $where, DB::limit($start, $limit)));
	}
}

?>