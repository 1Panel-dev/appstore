<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_access.php 27777 2012-02-14 07:07:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_access extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_access';
		$this->_pk    = '';

		parent::__construct();
	}


	public function fetch_all_by_fid_uid($fid = 0, $uid = 0, $count = 0, $start = 0, $limit = 0) {
		$uid = intval($uid);
		$sql = $uid ? ' uid='.$uid : '';
		$sql .= $fid ? ($sql ? ' AND ' : '').DB::field('fid', $fid) : '';
		if(empty($sql)) {
			return false;
		}
		if($count) {
			return DB::result_first('SELECT count(*) FROM %t WHERE '.$sql, array($this->_table));
		}
		if($limit) {
			$sql .= " LIMIT $start, $limit";
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.$sql, array($this->_table));
	}

	public function fetch_all_by_uid($uid) {
		$data = array();
		if($uid) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid), 'fid');
		}
		return $data;
	}

	public function count_by_uid($uid) {
		return $uid ? DB::result_first('SELECT count(*) FROM %t WHERE uid=%d', array($this->_table, $uid)) : 0;
	}

	public function delete_by_fid($fid, $uid = 0) {
		$uid = intval($uid);
		$uidsql = $uid ? ' uid='.$uid.' AND ' : '';
		DB::query("DELETE FROM %t WHERE $uidsql fid=%d", array($this->_table, $fid));
	}

	public function update_for_uid($uid, $fid, $data) {
		if(empty($uid) || empty($fid) || empty($data) || !is_array($data)) {
			return false;
		}
		DB::update($this->_table, $data, DB::field('uid', $uid).' AND '.DB::field('fid', $fid));
	}

	public function delete_by_uid($uid) {
		return $uid ? DB::delete($this->_table, DB::field('uid', $uid)) : false;
	}
}

?>