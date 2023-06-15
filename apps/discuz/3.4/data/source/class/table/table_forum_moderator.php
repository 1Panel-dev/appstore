<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_moderator.php 28576 2012-03-05 06:46:08Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_moderator extends discuz_table
{
	public function __construct() {

		$this->_table = 'forum_moderator';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_all_by_fid($fid, $order = true) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d'.($order ? ' ORDER BY inherited, displayorder' : ''), array($this->_table, $fid), 'uid');
	}

	public function fetch_all_by_fid_inherited($fid, $inherited = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d AND inherited=%d', array($this->_table, $fid, $inherited), 'uid');
	}

	public function fetch_all_by_uid($uid) {
		if(!$uid) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('uid', $uid)), 'fid');
	}

	public function fetch_all_by_uid_forum($uid) {
		return DB::fetch_all('SELECT m.fid, f.name, f.recyclebin
			FROM %t m, %t f
			WHERE m.uid=%d AND f.fid=m.fid AND f.status=\'1\' AND f.type<>\'group\'', array($this->_table, 'forum_forum', $uid));
	}

	public function fetch_uid_by_fid_uid($fid, $uid) {
		return DB::result_first('SELECT uid FROM %t WHERE fid=%d AND uid=%d', array($this->_table, $fid, $uid));
	}

	public function fetch_uid_by_tid($tid, $uid, $archiveid) {
		$archiveid = dintval($archiveid);
		$threadtable = $archiveid ? "forum_thread_{$archiveid}" : 'forum_thread';
		return DB::result_first('SELECT uid FROM %t m INNER JOIN %t t ON t.tid=%d AND t.fid=m.fid WHERE m.uid=%d', array($this->_table, $threadtable, $tid, $uid));
	}

	public function count_by_uid($uid) {
		if(!$uid) {
			return null;
		}
		return DB::result_first('SELECT count(*) FROM %t WHERE %i', array($this->_table, DB::field('uid', $uid)));
	}

	public function fetch_all_no_inherited_by_fid($fid) {
		return DB::fetch_all('SELECT * FROM %t WHERE fid=%d AND inherited=0 ORDER BY displayorder', array($this->_table, $fid), 'uid');
	}

	public function fetch_all_no_inherited() {
		return DB::fetch_all('SELECT * FROM %t WHERE inherited=0 ORDER BY displayorder', array($this->_table));
	}

	public function update_by_fid_uid($fid, $uid, $data) {
		if(!$fid || !$uid || !$data || !is_array($data)) {
			return null;
		}
		return DB::update($this->_table, $data, array('fid' => $fid, 'uid' => $uid));
	}
	public function delete_by_uid($uid) {
		return $uid ? DB::delete($this->_table, DB::field('uid', $uid)) : false;
	}

	public function delete_by_fid($fid) {
		return $fid ? DB::delete($this->_table, DB::field('fid', $fid)) : false;
	}

	public function delete_by_fid_inherited($fid, $inherited) {
		return $fid ? DB::delete($this->_table, DB::field('fid', $fid).' AND '.DB::field('inherited', $inherited)) : false;
	}

	public function delete_by_uid_fid_inherited($uid, $fid, $fidarray) {
		if(!$fid || !$uid) {
			return null;
		}
		$fid = dintval($fid);
		$uid = dintval($uid);
		$fidarray = array_map('addslashes', $fidarray);
		return DB::delete($this->_table, "uid='$uid' AND ((fid='$fid' AND inherited='0') OR (fid IN (".dimplode($fidarray).") AND inherited='1'))");
	}

	public function delete_by_uid_fid($uid, $fid) {
		if(!$fid || !$uid) {
			return null;
		}
		return DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('fid', $fid).' AND '.DB::field('inherited', 1));
	}
}

?>