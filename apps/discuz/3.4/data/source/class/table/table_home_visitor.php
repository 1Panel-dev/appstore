<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_home_visitor.php 31354 2012-08-16 03:03:08Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_home_visitor extends discuz_table
{
	public function __construct() {

		$this->_table = 'home_visitor';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_by_uid_vuid($uid, $vuid) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND vuid=%d', array($this->_table, $uid, $vuid));
	}
	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid));
	}
	public function fetch_all_by_vuid($uid, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE vuid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid));
	}
	public function update_by_uid_vuid($uid, $vuid, $data) {
		$uid = dintval($uid, true);
		$vuid = dintval($vuid, true);
		if($uid && !empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, DB::field('uid', $uid).' AND '.DB::field('vuid', $vuid));
		}
		return 0;
	}
	public function delete_by_uid_or_vuid($uids) {
		$uids = dintval($uids, true);
		if($uids) {
			return DB::delete($this->_table, DB::field('uid', $uids).' OR '.DB::field('vuid', $uids));
		}
		return 0;
	}
	public function delete_by_uid_vuid($uid, $vuid) {
		$uid = dintval($uid);
		$vuid = dintval($vuid);
		return DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('vuid', $vuid));
	}
	public function delete_by_dateline($dateline) {
		$dateline = dintval($dateline);
		return DB::delete($this->_table, DB::field('dateline', $dateline, '<'));
	}
	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table,$uid));
	}
	public function count_by_vuid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE vuid=%d', array($this->_table,$uid));
	}


}

?>